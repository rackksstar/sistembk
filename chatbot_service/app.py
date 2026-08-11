import os
import re
import sqlite3
from contextlib import contextmanager

from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

load_dotenv()

CHATBOT_PROVIDER = os.environ.get("CHATBOT_PROVIDER", "local")
BOOK_INDEX_DB = os.environ.get("BOOK_INDEX_DB", "./book_index.sqlite3")
TOP_K = int(os.environ.get("CHATBOT_TOP_K", "4"))
MIN_CONTENT_PAGE = int(os.environ.get("MIN_CONTENT_PAGE", "8"))
MEMORY_DB = os.environ.get("MEMORY_DB", "./chat_memory.sqlite3")
MAX_HISTORY_TURNS = int(os.environ.get("MAX_HISTORY_TURNS", "10"))

app = FastAPI(title="Chatbot Konseling Siswa")


class ChatRequest(BaseModel):
    studentId: str
    message: str


class ChatResponse(BaseModel):
    reply: str


@contextmanager
def get_db():
    conn = sqlite3.connect(MEMORY_DB)
    conn.execute(
        """
        CREATE TABLE IF NOT EXISTS chat_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id TEXT NOT NULL,
            role TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
        """
    )
    try:
        yield conn
    finally:
        conn.commit()
        conn.close()


def load_history(student_id: str):
    with get_db() as conn:
        rows = conn.execute(
            "SELECT role, content FROM chat_history WHERE student_id = ? ORDER BY id DESC LIMIT ?",
            (student_id, MAX_HISTORY_TURNS * 2),
        ).fetchall()

    return list(reversed(rows))


def save_message(student_id: str, role: str, content: str):
    with get_db() as conn:
        conn.execute(
            "INSERT INTO chat_history (student_id, role, content) VALUES (?, ?, ?)",
            (student_id, role, content),
        )


def normalize_text(text: str) -> str:
    text = re.sub(r"\s+", " ", text)
    text = re.sub(r"\bda n\b", "dan", text, flags=re.IGNORECASE)
    text = re.sub(r"\btiti k\b", "titik", text, flags=re.IGNORECASE)
    return text.strip()


def split_sentences(text: str):
    text = normalize_text(text)
    return [
        sentence.strip()
        for sentence in re.split(r"(?<=[.!?])\s+", text)
        if len(sentence.strip()) > 30
    ]


def query_terms(query: str):
    stopwords = {
        "ada", "aku", "apa", "atau", "bagaimana", "bisa", "buat", "cara",
        "dan", "dari", "dengan", "di", "ini", "itu", "jadi", "kalau", "ke",
        "mau", "saya", "sebagai", "tentang", "untuk", "yang",
    }

    terms = [
        term for term in re.findall(r"[A-Za-z0-9_]+", query.lower())
        if len(term) > 2 and term not in stopwords
    ]

    return list(dict.fromkeys(terms))


def build_fts_query(query: str) -> str:
    return " OR ".join(query_terms(query)[:8])


def retrieve_context(query: str, k: int = TOP_K):
    if not os.path.exists(BOOK_INDEX_DB):
        raise HTTPException(
            status_code=503,
            detail="Index buku chatbot belum tersedia. Jalankan ingest_buku.py terlebih dahulu.",
        )

    fts_query = build_fts_query(query)

    with sqlite3.connect(BOOK_INDEX_DB) as conn:
        conn.row_factory = sqlite3.Row

        if fts_query:
            rows = conn.execute(
                """
                SELECT chunks.page, chunks.source, chunks.content
                FROM chunks_fts
                JOIN chunks ON chunks_fts.rowid = chunks.id
                WHERE chunks_fts MATCH ? AND chunks.page >= ?
                ORDER BY bm25(chunks_fts)
                LIMIT ?
                """,
                (fts_query, MIN_CONTENT_PAGE, k),
            ).fetchall()
        else:
            rows = []

        if not rows:
            rows = conn.execute(
                "SELECT page, source, content FROM chunks WHERE page >= ? ORDER BY id LIMIT ?",
                (MIN_CONTENT_PAGE, k),
            ).fetchall()

    context_parts = []
    for row in rows:
        context_parts.append({
            "source": row["source"],
            "page": row["page"],
            "content": normalize_text(row["content"]),
        })

    return context_parts


def pick_sentences(query: str, contexts):
    terms = set(query_terms(query))
    ranked = []

    for context in contexts:
        for sentence in split_sentences(context["content"]):
            if len(sentence) > 260:
                continue

            if sentence[-1] not in ".!?":
                continue

            sentence_terms = set(query_terms(sentence))
            score = len(terms & sentence_terms)

            if score > 0:
                ranked.append((score, context["page"], sentence))

    ranked.sort(key=lambda item: item[0], reverse=True)
    selected = []
    seen = set()

    for _, page, sentence in ranked:
        key = sentence.lower()

        if key in seen:
            continue

        seen.add(key)
        selected.append((page, sentence))

        if len(selected) >= 4:
            break

    if selected:
        return selected

    fallback = []
    for context in contexts[:2]:
        sentences = split_sentences(context["content"])
        if sentences:
            fallback.append((context["page"], sentences[0]))

    return fallback


def is_emotional_message(message: str) -> bool:
    patterns = [
        "cemas", "capek", "depresi", "gagal", "khawatir", "marah", "sedih",
        "sendiri", "stres", "stress", "takut", "tertekan", "lelah", "panik",
    ]
    lowered = message.lower()

    return any(pattern in lowered for pattern in patterns)


def guidance_closing() -> str:
    return (
        "Kalau kamu ingin cerita lebih lanjut atau butuh arahan yang lebih sesuai dengan keadaanmu, "
        "kamu bisa lanjut bimbingan konseling dengan Guru BK di sekolah. Tidak apa-apa minta bantuan, "
        "justru itu langkah yang baik."
    )


def with_guidance_closing(answer: str) -> str:
    return f"{answer}\n\n{guidance_closing()}"


def direct_answer(message: str):
    lowered = message.lower()

    if is_emotional_message(message) and "belajar" in lowered:
        return with_guidance_closing(
            "Aku paham, takut gagal saat belajar itu berat banget rasanya. Tapi coba ingat, gagal di satu tugas atau satu pelajaran "
            "bukan berarti kamu gagal sebagai siswa.\n\n"
            "Mulai dari langkah kecil dulu: pilih satu materi yang paling bikin bingung, belajar 20 sampai 30 menit, lalu istirahat sebentar. "
            "Kalau masih sulit, catat bagian yang belum paham dan tanyakan ke guru, teman, atau Guru BK.\n\n"
            "Yang penting bukan langsung sempurna, tapi kamu pelan-pelan tahu bagian mana yang perlu dibantu."
        )

    if "bimbingan" in lowered and "konseling" in lowered:
        return with_guidance_closing(
            "Intinya, bimbingan konseling itu tempat kamu dibantu untuk memahami diri, menghadapi masalah, "
            "dan membuat pilihan yang lebih baik.\n\n"
            "Bimbingan biasanya lebih ke mencegah masalah sebelum makin besar, misalnya dibantu mengatur belajar, "
            "mengenali minat, atau memilih langkah setelah lulus. Konseling lebih fokus saat kamu sedang punya masalah "
            "dan butuh diajak ngobrol untuk mencari jalan keluarnya.\n\n"
            "Jadi BK bukan tempat untuk menghukum siswa. BK itu tempat untuk dibantu, didengar, dan diarahkan supaya kamu "
            "bisa mengambil keputusan dengan lebih tenang."
        )

    if "konseling" in lowered:
        return with_guidance_closing(
            "Konseling itu proses ngobrol yang lebih pribadi untuk membantu kamu memahami masalah dan mencari jalan keluar. "
            "Di dalam konseling, kamu bisa cerita dengan lebih aman, lalu dibantu melihat apa yang sebenarnya terjadi, "
            "apa pilihanmu, dan langkah kecil apa yang bisa kamu lakukan.\n\n"
            "Konseling bukan berarti kamu lemah. Justru itu tanda kamu mau berusaha memahami diri sendiri dan memperbaiki keadaan."
        )

    if "bimbingan" in lowered:
        return with_guidance_closing(
            "Bimbingan itu bantuan supaya kamu bisa berkembang dan mengambil keputusan yang lebih baik. "
            "Contohnya bantuan dalam belajar, pergaulan, pilihan karier, kebiasaan sehari-hari, atau cara mengenali kemampuan diri.\n\n"
            "Sederhananya, bimbingan membantu kamu tidak berjalan sendirian saat sedang bingung menentukan arah."
        )

    if "belajar" in lowered:
        return with_guidance_closing(
            "Kalau masalahnya tentang belajar, coba mulai dari hal kecil dulu. Tentukan pelajaran mana yang paling bikin kamu bingung, "
            "buat jadwal pendek 20 sampai 30 menit, lalu kerjakan bagian yang paling mudah dulu supaya kamu punya dorongan untuk lanjut.\n\n"
            "Belajar yang baik bukan harus lama terus, tapi konsisten dan tahu bagian mana yang perlu diperbaiki."
        )

    if "karier" in lowered or "cita" in lowered or "jurusan" in lowered:
        return with_guidance_closing(
            "Kalau kamu sedang mikir soal karier atau jurusan, mulai dari mengenali minat, kemampuan, dan hal yang kamu sukai. "
            "Setelah itu cocokkan dengan peluang yang ada, misalnya jurusan kuliah, dunia kerja, atau keterampilan yang perlu kamu latih.\n\n"
            "Tidak harus langsung yakin seratus persen. Yang penting kamu mulai mengumpulkan informasi dan mencoba mengenal dirimu sendiri."
        )

    return None


def build_local_reply(message: str, contexts):
    answer = direct_answer(message)
    if answer:
        return answer

    selected = pick_sentences(message, contexts)

    if not selected:
        return with_guidance_closing(
            "Aku belum cukup paham maksud pertanyaanmu. Coba tulis lebih spesifik ya, "
            "misalnya tentang belajar, pertemanan, karier, keluarga, atau cara menghadapi masalah tertentu."
        )

    if is_emotional_message(message):
        opening = (
            "Aku paham, rasanya pasti tidak enak kalau lagi ada di posisi itu. "
            "Pelan-pelan ya, kamu tidak harus menyelesaikan semuanya sekaligus."
        )
    else:
        opening = "Intinya, ini bisa dipahami dengan sederhana seperti ini."

    points = []
    for index, (page, sentence) in enumerate(selected, start=1):
        cleaned = sentence
        cleaned = re.sub(r"\s+-\s+", " ", cleaned)
        cleaned = re.sub(r"\s+", " ", cleaned).strip()
        points.append(f"{index}. {cleaned}")

    return "\n\n".join([opening, "\n".join(points), guidance_closing()])


@app.post("/chat", response_model=ChatResponse)
def chat(req: ChatRequest):
    message = req.message.strip()

    if not message:
        raise HTTPException(status_code=400, detail="Pesan tidak boleh kosong.")

    if CHATBOT_PROVIDER != "local":
        raise HTTPException(status_code=503, detail="Provider chatbot tidak tersedia. Gunakan CHATBOT_PROVIDER=local.")

    contexts = retrieve_context(message)
    reply_text = build_local_reply(message, contexts)

    save_message(req.studentId, "user", message)
    save_message(req.studentId, "assistant", reply_text)

    return ChatResponse(reply=reply_text)


@app.get("/health")
def health():
    return {"status": "ok"}
