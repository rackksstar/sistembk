"""
Chatbot Konseling Siswa — FastAPI lokal (tanpa LLM eksternal).

Sumber jawaban:
1. Jawaban langsung terkurasi (BK + PCR/PMB) agar akurat
2. Retrieval FTS dari PDF BK + knowledge/*.md
3. Riwayat percakapan singkat (follow-up seperti "berapa biayanya?")

Siap VPS/cloud: path absolut, token API opsional, /health, CORS tidak dibuka publik.
"""

from __future__ import annotations

import os
import re
import sqlite3
from contextlib import contextmanager
from pathlib import Path

from dotenv import load_dotenv
from fastapi import FastAPI, Header, HTTPException, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field

BASE_DIR = Path(__file__).resolve().parent
load_dotenv(BASE_DIR / ".env")

CHATBOT_PROVIDER = os.environ.get("CHATBOT_PROVIDER", "local").strip().lower()
CHATBOT_API_TOKEN = os.environ.get("CHATBOT_API_TOKEN", "").strip()
BOOK_INDEX_DB = os.environ.get("BOOK_INDEX_DB", str(BASE_DIR / "book_index.sqlite3"))
MEMORY_DB = os.environ.get("MEMORY_DB", str(BASE_DIR / "chat_memory.sqlite3"))
TOP_K = int(os.environ.get("CHATBOT_TOP_K", "6"))
MIN_CONTENT_PAGE = int(os.environ.get("MIN_CONTENT_PAGE", "8"))
MAX_HISTORY_TURNS = int(os.environ.get("MAX_HISTORY_TURNS", "10"))
MAX_MESSAGE_CHARS = int(os.environ.get("MAX_MESSAGE_CHARS", "2000"))

app = FastAPI(title="Chatbot Konseling Siswa", version="2.0.0")


class ChatRequest(BaseModel):
    studentId: str = Field(min_length=1, max_length=64)
    message: str = Field(min_length=1, max_length=MAX_MESSAGE_CHARS)
    studentName: str | None = Field(default=None, max_length=120)


class ChatResponse(BaseModel):
    reply: str
    meta: dict | None = None


WEAK_QUERY_TERMS = {
    "siapa", "apa", "bagaimana", "kenapa", "mengapa", "kok", "loh", "yah",
    "dong", "nih", "deh", "sih", "aja", "saja", "kah",
}


@app.middleware("http")
async def verify_api_token(request: Request, call_next):
    """Lindungi /chat bila CHATBOT_API_TOKEN di-set (wajib di VPS/cloud)."""
    open_paths = {"/health", "/docs", "/openapi.json", "/redoc"}
    if request.url.path in open_paths or request.method == "OPTIONS":
        return await call_next(request)

    if not CHATBOT_API_TOKEN:
        return await call_next(request)

    provided = (request.headers.get("X-Chatbot-Token") or "").strip()
    if provided != CHATBOT_API_TOKEN:
        return JSONResponse(status_code=401, content={"detail": "Token chatbot tidak valid."})

    return await call_next(request)


@contextmanager
def get_memory_db():
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
    conn.execute(
        "CREATE INDEX IF NOT EXISTS idx_chat_history_student ON chat_history(student_id, id)"
    )
    try:
        yield conn
        conn.commit()
    finally:
        conn.close()


def load_history(student_id: str) -> list[tuple[str, str]]:
    with get_memory_db() as conn:
        rows = conn.execute(
            """
            SELECT role, content FROM chat_history
            WHERE student_id = ?
            ORDER BY id DESC
            LIMIT ?
            """,
            (student_id, MAX_HISTORY_TURNS * 2),
        ).fetchall()
    return list(reversed(rows))


def save_message(student_id: str, role: str, content: str) -> None:
    with get_memory_db() as conn:
        conn.execute(
            "INSERT INTO chat_history (student_id, role, content) VALUES (?, ?, ?)",
            (student_id, role, content),
        )


def normalize_text(text: str) -> str:
    text = re.sub(r"\s+", " ", text)
    text = re.sub(r"\bda n\b", "dan", text, flags=re.IGNORECASE)
    text = re.sub(r"\btiti k\b", "titik", text, flags=re.IGNORECASE)
    return text.strip()


def split_sentences(text: str) -> list[str]:
    text = normalize_text(text)
    return [
        sentence.strip()
        for sentence in re.split(r"(?<=[.!?])\s+", text)
        if len(sentence.strip()) > 25
    ]


def query_terms(query: str) -> list[str]:
    stopwords = {
        "ada", "aku", "apa", "atau", "bagaimana", "bisa", "buat", "cara",
        "dan", "dari", "dengan", "di", "ini", "itu", "jadi", "kalau", "ke",
        "mau", "saya", "sebagai", "tentang", "untuk", "yang", "apakah",
        "berapa", "dimana", "mana", "tolong", "mohon", "dong", "ya", "kah",
        "the", "and", "for", "dengan", "oleh",
    }

    terms = [
        term for term in re.findall(r"[A-Za-z0-9_]+", query.lower())
        if len(term) > 2 and term not in stopwords
    ]

    expansions = {
        "pcr": ["politeknik", "caltex", "riau", "pmb"],
        "pmb": ["pendaftaran", "mahasiswa", "baru", "pcr"],
        "kuliah": ["prodi", "jurusan", "pendaftaran", "pcr"],
        "jurusan": ["prodi", "program", "studi", "pcr"],
        "prodi": ["program", "studi", "jurusan", "pcr"],
        "biaya": ["spp", "spi", "pendaftaran", "pmb"],
        "beasiswa": ["kipk", "kip", "yayasan", "pmb"],
        "psud": ["rapor", "nilai", "gratis", "pmb"],
        "umpcr": ["ujian", "cbt", "pmb"],
        "mandiri": ["ujian", "cbt", "pmb"],
        "minat": ["bakat", "riasec", "asesmen"],
        "riasec": ["minat", "bakat", "holland"],
        "konsultasi": ["prodi", "guru", "bk"],
    }

    expanded: list[str] = list(terms)
    for term in terms:
        expanded.extend(expansions.get(term, []))

    return list(dict.fromkeys(expanded))


def escape_fts_term(term: str) -> str:
    cleaned = re.sub(r'[^\w]+', '', term, flags=re.UNICODE)
    if not cleaned:
        return ""
    return f'"{cleaned}"'


def build_fts_query(query: str) -> str:
    terms = [escape_fts_term(t) for t in query_terms(query)[:12]]
    terms = [t for t in terms if t]
    return " OR ".join(terms)


def is_pcr_message(message: str) -> bool:
    lowered = message.lower()
    keywords = [
        "pcr", "caltex", "pmb", "politeknik", "prodi", "program studi",
        "umpcr", "psud", "beasiswa", "spp", "spi", "kipk",
        "pendaftaran pmb", "pendaftaran pcr", "kuliah di", "kampus pcr",
        "pekanbaru", "teknik informatika", "sistem informasi",
        "akuntansi perpajakan", "mekatronika", "animasi", "bisnis digital",
        "humas", "rekognisi pembelajaran", "rpl kuliah", "ujian mandiri",
        "nilai rapor", "rata-rata rapor",
    ]
    if any(key in lowered for key in keywords):
        return True
    # "pendaftaran" / "jurusan" saja masih sering soal PMB di konteks sekolah ini
    if re.search(r"\b(pendaftaran|jurusan)\b", lowered) and not is_emotional_message(message):
        return True
    return False


def history_suggests_pcr(history: list[tuple[str, str]]) -> bool:
    """Hanya dari pesan USER sebelumnya — jangan ikut greeting asisten yang sekadar menyebut PCR."""
    for role, content in reversed(history[-8:]):
        if role == "user" and is_pcr_message(content or ""):
            return True
    return False


def is_followup_message(message: str) -> bool:
    """Follow-up singkat saja (hindari 'ya' di dalam 'siapa'; jangan anggap pertanyaan panjang)."""
    lowered = message.lower().strip()
    words = re.findall(r"[a-z0-9]+", lowered)
    if not words or len(words) > 6:
        return False

    # Satu/dua kata afirmasi atau topik lanjutan
    short_only = {
        "itu", "lanjut", "berapa", "contoh", "terus", "lalu", "oke", "okay",
        "iya", "ya", "gimana", "detail", "syarat", "jadwal", "biaya", "prodi",
        "jurusan", "beasiswa", "kontak", "iya", "yoi", "lanjutkan",
    }
    if len(words) <= 3 and any(w in short_only for w in words):
        return True

    phrases = [
        "yang tadi", "lebih detail", "jelaskan lagi", "yang kedua", "yang pertama",
        "bagaimana itu", "yang itu", "soal itu", "tentang itu", "terus bagaimana",
    ]
    return any(p in lowered for p in phrases)


def is_personal_identity_message(message: str) -> bool:
    lowered = message.lower().strip()
    patterns = [
        r"\bsiapa\s+(saya|aku)\b",
        r"\b(saya|aku)\s+siapa\b",
        r"\bnama\s+(saya|aku)\b",
        r"\b(saya|aku)\s+ini\s+siapa\b",
        r"\bsiapa\s+(kamu|anda|kau)\b",
        r"\b(kamu|anda)\s+siapa\b",
        r"\bnama\s+(kamu|anda)\b",
    ]
    return any(re.search(p, lowered) for p in patterns)


def has_strong_query_terms(message: str) -> bool:
    terms = query_terms(message)
    strong = [t for t in terms if t not in WEAK_QUERY_TERMS]
    return len(strong) > 0


def prefer_pcr_context(message: str, history: list[tuple[str, str]]) -> bool:
    if is_personal_identity_message(message):
        return False
    if is_pcr_message(message):
        return True
    return is_followup_message(message) and history_suggests_pcr(history)


def is_pcr_source(source: str) -> bool:
    s = (source or "").lower()
    return "pcr" in s or "pmb" in s


def is_emotional_message(message: str) -> bool:
    patterns = [
        "cemas", "capek", "depresi", "gagal", "khawatir", "marah", "sedih",
        "sendiri", "stres", "stress", "takut", "tertekan", "lelah", "panik",
        "bunuh", "mengakhiri", "putus asa", "tidak kuat",
    ]
    lowered = message.lower()
    return any(pattern in lowered for pattern in patterns)


def is_crisis_message(message: str) -> bool:
    patterns = [
        "bunuh diri", "mengakhiri hidup", "ingin mati", "mau mati",
        "tidak ingin hidup", "self harm", "menyakiti diri",
    ]
    lowered = message.lower()
    return any(p in lowered for p in patterns)


def is_out_of_scope(message: str) -> bool:
    lowered = message.lower()
    blocked = [
        "jawaban ujian", "kunci jawaban", "contekan", "hack", "password admin",
        "nuklir", "racun", "cara bunuh orang", "judi slot", "porn",
        "obat terlarang", "sabu", "ganja beli",
    ]
    return any(p in lowered for p in blocked)


def is_sistem_bk_message(message: str) -> bool:
    lowered = message.lower()
    keys = [
        "minat bakat", "riasec", "konsultasi prodi", "tryout", "angket",
        "sistem bk", "guru bk", "rapor bk", "chatbot", "hasil minat",
    ]
    return any(k in lowered for k in keys)


def is_greeting_message(message: str) -> bool:
    text = re.sub(r"[^\w\s]", " ", message.lower()).strip()
    text = re.sub(r"\s+", " ", text)
    if not text:
        return False

    greetings = {
        "halo", "halo halo", "hai", "hay", "hi", "hello", "helo",
        "hallo", "hei", "hey", "yo", "pagi", "siang", "sore", "malam",
        "selamat pagi", "selamat siang", "selamat sore", "selamat malam",
        "assalamualaikum", "assalamu alaikum", "assalamu'alaikum",
        "waalaikumsalam", "waalaikumussalam", "salam", "permisi",
        "halo kak", "hai kak", "halo min", "halo bot",
    }
    if text in greetings:
        return True

    # Sapaan singkat + nama panggilan
    if len(text.split()) <= 4 and any(
        text.startswith(g) for g in ("halo", "hai", "hi ", "hello", "assalam", "selamat")
    ):
        return True

    return False


def is_thanks_or_ack(message: str) -> bool:
    text = re.sub(r"[^\w\s]", " ", message.lower()).strip()
    text = re.sub(r"\s+", " ", text)
    acks = {
        "ok", "oke", "okay", "baik", "sip", "siap", "thanks", "thank you",
        "makasih", "terima kasih", "trims", "trimakasih", "suwun", "thanks ya",
        "makasih ya", "oke thanks", "baiklah",
    }
    return text in acks or (len(text.split()) <= 4 and any(
        w in text for w in ("makasih", "terima kasih", "thanks")
    ))


def greeting_reply() -> str:
    return (
        "Halo! Senang bisa membantu.\n\n"
        "Cerita saja soal belajar, pertemanan, atau rencana karier — aku dengerin. "
        "Boleh juga tanya info resmi **PCR/PMB** (jalur masuk, prodi, biaya, beasiswa), "
        "atau cara pakai **Minat Bakat Kuliah** / **Konsultasi Prodi**.\n\n"
        "Mau mulai dari mana?"
    )


def thanks_reply() -> str:
    return (
        "Sama-sama. Kalau masih ada yang ingin ditanyakan soal sekolah, konseling, "
        "atau PCR/PMB, tulis saja ya."
    )


def identity_reply(message: str, student_name: str | None = None) -> str:
    lowered = message.lower()
    asking_bot = bool(re.search(r"\b(kamu|anda)\b", lowered)) and "siapa" in lowered

    if asking_bot:
        return (
            "Aku asisten **Chatbot Konseling** di Sistem BK. "
            "Aku membantu ngobrol soal belajar, konseling sekolah, minat/karier, "
            "dan info resmi PCR/PMB. Aku bukan Guru BK manusia — untuk urusan penting "
            "atau pribadi yang rumit, lanjut ke Guru BK ya."
        )

    name = (student_name or "").strip()
    if name:
        first = name.split()[0]
        return (
            f"Di sistem ini kamu login sebagai **{name}**.\n\n"
            f"Kalau yang kamu maksud lebih dalam — misalnya mengenal minat, bakat, atau arah kuliah — "
            f"kita bisa mulai dari situ, {first}. Mau coba tanya soal Minat Bakat Kuliah, "
            f"pilihan prodi PCR, atau cerita dulu apa yang sedang kamu pikirkan?"
        )

    return (
        "Aku mengenalmu sebagai siswa yang sedang login di Sistem BK. "
        "Untuk mengenal minat dan arah kuliah lebih dalam, kamu bisa kerjakan "
        "**Minat Bakat Kuliah**, atau cerita saja apa yang ingin kamu bahas sekarang."
    )


def guidance_closing() -> str:
    return (
        "Kalau kamu ingin cerita lebih lanjut atau butuh arahan yang lebih sesuai dengan keadaanmu, "
        "kamu bisa lanjut bimbingan konseling dengan Guru BK di sekolah. Tidak apa-apa minta bantuan, "
        "justru itu langkah yang baik."
    )


def pcr_closing() -> str:
    return (
        "**Sumber resmi**\n"
        "• https://pmb.pcr.ac.id\n"
        "• https://pcr.ac.id\n\n"
        "**Kontak PMB**\n"
        "• WA/Telp: 081170750001 / 081170750002 / 081170750003\n"
        "• Email: pmb@pcr.ac.id\n\n"
        "Tanggal & biaya bisa berubah — cek situs resmi untuk info terbaru. "
        "Kalau masih bingung pilih prodi, tanya Guru BK atau buka menu **Konsultasi Prodi**."
    )


def with_guidance_closing(answer: str) -> str:
    return f"{answer}\n\n{guidance_closing()}"


def with_pcr_closing(answer: str) -> str:
    return f"{answer}\n\n{pcr_closing()}"


def retrieve_context(query: str, k: int = TOP_K, prefer_pcr: bool = False) -> list[dict]:
    if not os.path.exists(BOOK_INDEX_DB):
        raise HTTPException(
            status_code=503,
            detail="Index chatbot belum tersedia. Jalankan: python ingest_buku.py",
        )

    fts_query = build_fts_query(query)

    with sqlite3.connect(BOOK_INDEX_DB) as conn:
        conn.row_factory = sqlite3.Row
        rows: list = []

        def run_fts(sql: str, params: tuple):
            try:
                return conn.execute(sql, params).fetchall()
            except sqlite3.OperationalError:
                return []

        if fts_query:
            if prefer_pcr:
                rows = run_fts(
                    """
                    SELECT chunks.page, chunks.source, chunks.content
                    FROM chunks_fts
                    JOIN chunks ON chunks_fts.rowid = chunks.id
                    WHERE chunks_fts MATCH ?
                      AND (chunks.source LIKE '%PCR%' OR chunks.source LIKE '%pmb%' OR chunks.source LIKE '%pcr%')
                    ORDER BY bm25(chunks_fts)
                    LIMIT ?
                    """,
                    (fts_query, k),
                )
                if len(rows) < k:
                    more = run_fts(
                        """
                        SELECT chunks.page, chunks.source, chunks.content
                        FROM chunks_fts
                        JOIN chunks ON chunks_fts.rowid = chunks.id
                        WHERE chunks_fts MATCH ?
                        ORDER BY
                          CASE WHEN lower(chunks.source) LIKE '%pcr%' OR lower(chunks.source) LIKE '%pmb%' THEN 0 ELSE 1 END,
                          bm25(chunks_fts)
                        LIMIT ?
                        """,
                        (fts_query, k),
                    )
                    rows = list({(r["source"], r["page"], r["content"]): r for r in list(rows) + list(more)}.values())
            else:
                rows = run_fts(
                    """
                    SELECT chunks.page, chunks.source, chunks.content
                    FROM chunks_fts
                    JOIN chunks ON chunks_fts.rowid = chunks.id
                    WHERE chunks_fts MATCH ? AND chunks.page >= ?
                    ORDER BY bm25(chunks_fts)
                    LIMIT ?
                    """,
                    (fts_query, MIN_CONTENT_PAGE, k),
                )
                pcr_rows = run_fts(
                    """
                    SELECT chunks.page, chunks.source, chunks.content
                    FROM chunks_fts
                    JOIN chunks ON chunks_fts.rowid = chunks.id
                    WHERE chunks_fts MATCH ?
                      AND (lower(chunks.source) LIKE '%pcr%' OR lower(chunks.source) LIKE '%pmb%')
                    ORDER BY bm25(chunks_fts)
                    LIMIT ?
                    """,
                    (fts_query, max(2, k // 2)),
                )
                rows = list({(r["source"], r["page"], r["content"]): r for r in list(rows) + list(pcr_rows)}.values())

        # Jangan fallback ke halaman awal buku (sering daftar isi) bila FTS kosong.
        # Itu penyebab bug jawaban aneh untuk pesan seperti "halo".
        if not rows and prefer_pcr and fts_query:
            rows = conn.execute(
                """
                SELECT page, source, content FROM chunks
                WHERE lower(source) LIKE '%pcr%' OR lower(source) LIKE '%pmb%'
                ORDER BY id LIMIT ?
                """,
                (k,),
            ).fetchall()

    return [
        {
            "source": row["source"],
            "page": row["page"],
            "content": normalize_text(row["content"]),
        }
        for row in rows[:k]
    ]


def is_junk_sentence(sentence: str) -> bool:
    """Filter potongan daftar isi / heading bab / kutipan tak relevan."""
    lowered = sentence.lower()
    if re.search(r"\bbab\s+\d+\b", lowered):
        return True
    if lowered.count(" -") >= 3 or lowered.count("–") >= 3:
        return True
    page_hits = len(re.findall(r"\b\d{2,3}\b", sentence))
    if page_hits >= 4 and ("bab" in lowered or "konsep" in lowered or "bimbingan" in lowered):
        return True
    letter_sections = len(re.findall(r"\b[A-E]\.[A-Z]", sentence))
    if letter_sections >= 2:
        return True
    # Kutipan agama / meta-buku yang sering salah cocok dengan kata generik ("siapa", "makna").
    if re.search(
        r"\b(al[-\s]?qur['’]?an|tuhanmu|sesungguhnya tuhan|buku bimbingan|"
        r"hal\.\s*\d+|makna hikmah|serulah kepada)\b",
        lowered,
    ):
        return True
    return False


def pick_sentences(query: str, contexts: list[dict], prefer_pcr: bool = False):
    terms = set(query_terms(query))
    strong_terms = {t for t in terms if t not in WEAK_QUERY_TERMS}
    if not strong_terms and not prefer_pcr:
        return []

    ranked = []
    min_score = 2 if not prefer_pcr else 1

    for context in contexts:
        source = context.get("source") or ""
        # Mode PCR: hanya potongan dari knowledge resmi PCR/PMB.
        if prefer_pcr and not is_pcr_source(source):
            continue

        for sentence in split_sentences(context["content"]):
            if len(sentence) > 280 or is_junk_sentence(sentence):
                continue
            sentence_terms = set(query_terms(sentence))
            overlap = strong_terms & sentence_terms if strong_terms else terms & sentence_terms
            score = len(overlap)
            if is_pcr_source(source) and prefer_pcr:
                score += 1
            if score >= min_score:
                ranked.append((score, source, context["page"], sentence))

    ranked.sort(key=lambda item: item[0], reverse=True)
    selected = []
    seen = set()

    for _, source, page, sentence in ranked:
        key = sentence.lower()
        if key in seen:
            continue
        seen.add(key)
        selected.append((source, page, sentence))
        if len(selected) >= 4:
            break

    return selected


def crisis_reply() -> str:
    return (
        "Aku dengar kamu sedang sangat tertekan. Kamu tidak sendirian, dan perasaan ini penting untuk ditangani "
        "bersama orang yang tepat.\n\n"
        "Segera hubungi Guru BK di sekolah, orang tua/wali yang kamu percaya, atau layanan darurat setempat. "
        "Di Indonesia kamu juga bisa menghubungi hotline kesehatan jiwa **119 ext 8** (Kemenkes).\n\n"
        "Aku di sini untuk mendampingi secara umum, tetapi untuk situasi darurat, bantuan manusia langsung lebih penting."
    )


def out_of_scope_reply() -> str:
    return with_guidance_closing(
        "Aku fokus membantu topik **konseling sekolah**, belajar, minat/karier, serta info resmi **PCR/PMB**. "
        "Untuk permintaan di luar itu, aku tidak bisa membantu. Coba tanyakan hal seputar sekolah, "
        "Minat Bakat, Konsultasi Prodi, atau jalur masuk PCR ya."
    )


def sistem_bk_answer(message: str) -> str | None:
    lowered = message.lower()

    if "minat bakat" in lowered or "riasec" in lowered:
        return with_guidance_closing(
            "Di Sistem BK, asesmen **Minat Bakat Kuliah** ada di menu **Lanjut Kuliah (Key)**.\n\n"
            "Alurnya: jawab soal RIASEC → dapat **Kode Minat** (3 huruf) → lihat rekomendasi "
            "**prodi PCR** (SMA) atau **bidang karier** (SMK). Hasil bisa diunduh PDF. "
            "Kalau sudah ada rekomendasi, kamu bisa lanjut **Konsultasi Prodi** ke Guru BK."
        )

    if "konsultasi prodi" in lowered:
        return with_guidance_closing(
            "Menu **Konsultasi Prodi** dipakai untuk diskusi pilihan prodi PCR dengan Guru BK. "
            "Pilih prodi yang sudah diverifikasi Admin, tulis topik/pertanyaan, lalu kirim. "
            "Statusnya bisa dipantau di riwayat (menunggu, disetujui, dijadwalkan, selesai)."
        )

    if "tryout" in lowered:
        return with_guidance_closing(
            "Tryout dikerjakan dari menu **Tryout** jika Guru BK sudah membuat sesi aktif untuk kelasmu. "
            "Skor baru muncul setelah Guru **memublikasikan hasil**."
        )

    return None


def direct_pcr_answer(message: str) -> str | None:
    lowered = message.lower()

    if "ips" in lowered and ("psud" in lowered or "prodi" in lowered or "jurusan" in lowered):
        return with_pcr_closing(
            "Menurut FAQ resmi PMB PCR, pada **jalur PSUD** siswa jurusan **IPS** hanya bisa mendaftar ke:\n"
            "1. **Akuntansi Perpajakan**\n"
            "2. **Sistem Informasi**\n\n"
            "Kalau ingin prodi lain, setelah PSUD disarankan ikut **UMPCR** atau **Ujian Mandiri** "
            "(boleh pilih prodi bebas asal memenuhi passing grade).\n"
            "Sumber: https://pmb.pcr.ac.id/faq"
        )

    if "ipa" in lowered and "psud" in lowered:
        return with_pcr_closing(
            "Menurut FAQ resmi PMB PCR, pada **jalur PSUD** siswa jurusan **IPA** bisa mendaftar ke "
            "**prodi manapun** yang tersedia di PCR.\n"
            "Syarat PSUD: rata-rata rapor semester I–IV minimal **8,0/80**, gratis, tanpa ujian.\n"
            "Sumber: https://pmb.pcr.ac.id/faq · https://pmb.pcr.ac.id/panduan/informasi"
        )

    if any(k in lowered for k in ["jalur", "psud", "umpcr", "ujian mandiri", "cara daftar", "pendaftaran"]):
        return with_pcr_closing(
            "**Jalur masuk PCR** ada 4 yang utama:\n\n"
            "**1. PSUD** (nilai rapor)\n"
            "• Rapor semester I–IV, rata-rata minimal **8,0 / 80**\n"
            "• **Gratis**, tanpa ujian\n"
            "• IPA: semua prodi\n"
            "• IPS: hanya **Akuntansi Perpajakan** & **Sistem Informasi**\n\n"
            "**2. UMPCR** (ujian bersama)\n"
            "• Gelombang I / II / III, ujian CBT\n"
            "• Ada biaya formulir\n"
            "• Prodi bebas, asal lulus passing grade\n\n"
            "**3. Ujian Mandiri**\n"
            "• CBT hari kerja (Senin–Jumat)\n"
            "• Hasil bisa dilihat di hari yang sama\n\n"
            "**4. Beasiswa**\n"
            "• Yayasan PCR, Pemda, KIPK, dan alumni\n"
            "• Syarat detail ada di formulir PMB\n\n"
            "**Alur daftar singkat**\n"
            "1. Buat akun email → verifikasi\n"
            "2. Pilih formulir jalur yang aktif\n"
            "3. Bayar (jika berbayar) → lengkapi data/berkas\n"
            "4. Seleksi → pengumuman → daftar ulang jika lulus\n\n"
            "**Link resmi**\n"
            "• Info jalur: https://pmb.pcr.ac.id/panduan/informasi\n"
            "• Jadwal: https://pmb.pcr.ac.id/panduan/jadwal"
        )

    if any(k in lowered for k in ["biaya", "spp", "spi", "uang kuliah", "bayar"]):
        return with_pcr_closing(
            "**Ringkasan biaya PCR** (bisa berubah — cek situs resmi):\n\n"
            "• Orientasi & seragam: **Rp 2.000.000** (sekali, registrasi ulang)\n"
            "• SPP / semester: **Rp 7.100.000** atau **Rp 7.500.000**\n"
            "  → Rp 7.500.000 untuk TI, SI, Teknik Mesin, Mekatronika\n"
            "• SPI (pengembangan institusi): mulai sekitar **Rp 12.000.000**\n\n"
            "Pembayaran bisa dicicil/bertahap.\n"
            "Detail: https://pmb.pcr.ac.id/panduan/biaya"
        )

    if any(k in lowered for k in ["jadwal", "tanggal daftar", "kapan daftar", "deadline"]):
        return with_pcr_closing(
            "Jadwal PMB PCR berubah tiap tahun akademik, jadi aku **tidak mengunci tanggal** di sini "
            "agar informasinya tidak salah.\n\n"
            "Cek tanggal resmi:\n"
            "https://pmb.pcr.ac.id/panduan/jadwal"
        )

    if any(k in lowered for k in ["prodi", "jurusan", "program studi", "teknik informatika", "sistem informasi", "animasi"]):
        return with_pcr_closing(
            "**Program studi PCR** (Sarjana Terapan / D4):\n\n"
            "**Teknologi Informasi**\n"
            "• Teknik Informatika\n"
            "• Sistem Informasi\n"
            "• Teknologi Rekayasa Komputer\n"
            "• Animasi\n"
            "• (+ Magister Terapan Teknik Komputer)\n\n"
            "**Teknologi Industri**\n"
            "• Teknik Elektronika, Teknik Listrik, Teknik Mesin\n"
            "• TRJT, TRSE, Mekatronika\n"
            "• Kecerdasan Buatan dan Robotika\n\n"
            "**Bisnis & Komunikasi**\n"
            "• Akuntansi Perpajakan\n"
            "• Hubungan Masyarakat dan Komunikasi Digital\n"
            "• Bisnis Digital\n\n"
            "Catatan IPS + PSUD: hanya Akuntansi Perpajakan & Sistem Informasi; "
            "prodi lain biasanya lewat UMPCR / Ujian Mandiri.\n"
            "Daftar lengkap: https://pmb.pcr.ac.id/prodi"
        )

    if "beasiswa" in lowered or "kip" in lowered:
        return with_pcr_closing(
            "**Beasiswa PCR** yang umum ditawarkan:\n\n"
            "• Beasiswa Prestasi Yayasan PCR\n"
            "• Beasiswa Pemerintah Daerah\n"
            "• Beasiswa KIPK (SPP + biaya hidup)\n"
            "• Beasiswa Ikatan Keluarga Alumni PCR\n\n"
            "Untuk KIP / DTKS / PKH biasanya wajib unggah salah satunya.\n"
            "• Formulir: https://pmb.pcr.ac.id/formulir\n"
            "• Info jalur: https://pmb.pcr.ac.id/panduan/informasi"
        )

    if any(k in lowered for k in ["kontak", "whatsapp", "hubungi", "alamat", "email pmb"]):
        return (
            "**Kontak resmi PMB PCR**\n\n"
            "• WA/Telepon: 081170750001, 081170750002, 081170750003\n"
            "• Bantuan data/berkas (FAQ): 082375369696\n"
            "• Email: pmb@pcr.ac.id / pcr@pcr.ac.id\n"
            "• Alamat: Jl. Umbansari No.1, Rumbai, Pekanbaru, Riau 28265\n"
            "• Situs: https://pmb.pcr.ac.id · https://pcr.ac.id\n\n"
            "Tanggal & biaya bisa berubah — selalu cocokkan di situs resmi."
        )

    if "rpl" in lowered or "kuliah sambil" in lowered or "karyawan" in lowered:
        return with_pcr_closing(
            "Ya, di PCR ada program **RPL (Rekognisi Pembelajaran Lampau)** untuk yang ingin kuliah sambil bekerja. "
            "Cocok bagi karyawan/profesional yang punya pengalaman kerja atau pendidikan sebelumnya. "
            "Detail resmi ada di FAQ PMB: https://pmb.pcr.ac.id/faq"
        )

    if "pcr" in lowered or "caltex" in lowered or "politeknik" in lowered:
        return with_pcr_closing(
            "Politeknik Caltex Riau (PCR) adalah kampus vokasi di Pekanbaru dengan fokus praktik dan kesiapan kerja. "
            "Ada banyak prodi D4 di bidang TI, industri, serta bisnis & komunikasi, plus magister terapan komputer.\n\n"
            "Untuk masuk, cek jalur PSUD / UMPCR / Mandiri / Beasiswa di https://pmb.pcr.ac.id. "
            "Kalau kamu sedang menimbang prodi, cocokkan juga dengan hasil Minat Bakat Kuliah dan tanyakan ke Guru BK."
        )

    return None


def direct_bk_answer(message: str) -> str | None:
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
            "Untuk pilihan kuliah di Politeknik Caltex Riau, kamu juga bisa tanya soal prodi/jalur PMB — "
            "atau buka https://pmb.pcr.ac.id/prodi lalu diskusikan dengan Guru BK.\n\n"
            "Tidak harus langsung yakin seratus persen. Yang penting kamu mulai mengumpulkan informasi dan mencoba mengenal dirimu sendiri."
        )

    return None


def last_user_pcr_topic(history: list[tuple[str, str]]) -> str | None:
    """Ambil topik PCR terakhir dari pesan user untuk follow-up singkat (ya/lanjut)."""
    for role, content in reversed(history[-8:]):
        if role != "user":
            continue
        text = (content or "").lower()
        if not is_pcr_message(text):
            continue
        if any(k in text for k in ["biaya", "spp", "spi"]):
            return "biaya kuliah pcr"
        if "beasiswa" in text or "kip" in text:
            return "beasiswa pcr"
        if any(k in text for k in ["jadwal", "tanggal daftar", "kapan daftar"]):
            return "jadwal pmb pcr"
        if any(k in text for k in ["jalur", "psud", "umpcr", "pendaftaran", "daftar"]):
            return "jalur masuk pcr"
        if any(k in text for k in ["prodi", "jurusan", "program studi"]):
            return "prodi pcr"
        if any(k in text for k in ["kontak", "whatsapp", "hubungi"]):
            return "kontak pmb pcr"
        return "prodi pcr"
    return None


def direct_answer(
    message: str,
    prefer_pcr: bool = False,
    student_name: str | None = None,
    history: list[tuple[str, str]] | None = None,
) -> str | None:
    if is_greeting_message(message):
        return greeting_reply()

    if is_personal_identity_message(message):
        return identity_reply(message, student_name)

    if is_sistem_bk_message(message):
        sistem = sistem_bk_answer(message)
        if sistem:
            return sistem

    if prefer_pcr or is_pcr_message(message):
        # Follow-up biaya/jadwal/prodi tanpa kata "PCR" tetap dijawab akurat.
        if prefer_pcr and not is_pcr_message(message):
            lowered = message.lower()
            words = re.findall(r"[a-z0-9]+", lowered)
            if any(k in lowered for k in ["biaya", "spp", "spi", "bayar"]):
                return direct_pcr_answer("biaya kuliah pcr")
            if any(k in lowered for k in ["jadwal", "tanggal", "kapan"]):
                return direct_pcr_answer("jadwal pmb pcr")
            if any(k in lowered for k in ["prodi", "jurusan"]):
                return direct_pcr_answer("prodi pcr")
            if any(k in lowered for k in ["jalur", "daftar", "pendaftaran", "psud", "umpcr"]):
                return direct_pcr_answer("jalur masuk pcr")
            if "beasiswa" in lowered or "kip" in lowered:
                return direct_pcr_answer("beasiswa pcr")
            # "ya" / "lanjut" / "oke" → lanjutkan topik PCR terakhir
            if len(words) <= 3 and any(
                w in {"ya", "iya", "yoi", "oke", "okay", "lanjut", "lanjutkan", "terus", "gimana"}
                for w in words
            ):
                topic = last_user_pcr_topic(history or [])
                if topic:
                    return direct_pcr_answer(topic)
                return with_pcr_closing(
                    "Siap. Mau dilanjut yang mana soal PCR/PMB?\n"
                    "1. Jalur masuk\n2. Prodi\n3. Biaya\n4. Beasiswa\n5. Kontak PMB"
                )

        pcr = direct_pcr_answer(message)
        if pcr:
            return pcr

    if is_thanks_or_ack(message):
        return thanks_reply()

    return direct_bk_answer(message)


def build_local_reply(
    message: str,
    contexts: list[dict],
    prefer_pcr: bool,
    student_name: str | None = None,
    history: list[tuple[str, str]] | None = None,
) -> str:
    answer = direct_answer(
        message,
        prefer_pcr=prefer_pcr,
        student_name=student_name,
        history=history,
    )
    if answer:
        return answer

    # Jangan retrieval untuk pertanyaan generik tanpa kata kunci kuat.
    if not prefer_pcr and not has_strong_query_terms(message):
        return with_guidance_closing(
            "Biar aku bantu lebih tepat, coba tulis lebih spesifik ya — "
            "misalnya soal belajar, pertemanan, karier, Minat Bakat, atau info PCR/PMB "
            "(jalur masuk, prodi, biaya, beasiswa)."
        )

    selected = pick_sentences(message, contexts, prefer_pcr=prefer_pcr)

    if not selected:
        if prefer_pcr:
            return with_pcr_closing(
                "Aku belum menemukan detail yang cukup spesifik dari knowledge resmi PCR untuk pertanyaan itu. "
                "Coba sebutkan lebih jelas: jalur masuk, biaya, prodi, beasiswa, atau kontak PMB. "
                "Sementara itu kamu bisa cek https://pmb.pcr.ac.id atau hubungi Tim PMB."
            )
        return with_guidance_closing(
            "Aku belum cukup yakin dengan jawaban yang tepat untuk itu. "
            "Coba tulis lebih spesifik, misalnya tentang belajar, karier, "
            "PCR/PMB, atau Minat Bakat Kuliah."
        )

    if prefer_pcr:
        opening = "Berikut ringkasan berdasarkan informasi resmi PCR/PMB yang relevan:"
        closing = pcr_closing()
    elif is_emotional_message(message):
        opening = (
            "Aku paham, rasanya pasti tidak enak kalau lagi ada di posisi itu. "
            "Pelan-pelan ya, kamu tidak harus menyelesaikan semuanya sekaligus."
        )
        closing = guidance_closing()
    else:
        opening = "Ini poin yang paling relevan:"
        closing = guidance_closing()

    points = []
    for index, (source, page, sentence) in enumerate(selected, start=1):
        cleaned = re.sub(r"\s+-\s+", " ", sentence)
        cleaned = re.sub(r"\s+", " ", cleaned).strip()
        if is_pcr_source(source):
            points.append(f"{index}. {cleaned}")
        else:
            points.append(f"{index}. {cleaned}")

    return "\n\n".join([opening, "\n".join(points), closing])


@app.post("/chat", response_model=ChatResponse)
def chat(req: ChatRequest, x_chatbot_token: str | None = Header(default=None)):
    # Header sudah dicek middleware; parameter ini agar OpenAPI dokumentatif.
    _ = x_chatbot_token

    if CHATBOT_PROVIDER != "local":
        raise HTTPException(
            status_code=503,
            detail="Provider chatbot tidak tersedia. Set CHATBOT_PROVIDER=local.",
        )

    message = req.message.strip()
    if not message:
        raise HTTPException(status_code=400, detail="Pesan tidak boleh kosong.")

    if is_crisis_message(message):
        reply_text = crisis_reply()
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", reply_text)
        return ChatResponse(reply=reply_text, meta={"kind": "crisis"})

    if is_out_of_scope(message):
        reply_text = out_of_scope_reply()
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", reply_text)
        return ChatResponse(reply=reply_text, meta={"kind": "out_of_scope"})

    # Jawaban langsung tanpa retrieval (lebih akurat & cepat).
    if is_greeting_message(message):
        reply_text = greeting_reply()
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", reply_text)
        return ChatResponse(reply=reply_text, meta={"kind": "greeting", "contexts": 0})

    if is_personal_identity_message(message):
        reply_text = identity_reply(message, req.studentName)
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", reply_text)
        return ChatResponse(reply=reply_text, meta={"kind": "identity", "contexts": 0})

    history = load_history(req.studentId)
    want_pcr = prefer_pcr_context(message, history)

    # "makasih" murni — tapi "oke/ya" setelah topik PCR dilanjut, bukan ditutup.
    if is_thanks_or_ack(message) and not (
        want_pcr and is_followup_message(message)
    ):
        reply_text = thanks_reply()
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", reply_text)
        return ChatResponse(reply=reply_text, meta={"kind": "thanks", "contexts": 0})

    # Prioritas: jawaban terkurasi (akurat) sebelum retrieval buku/knowledge.
    curated = direct_answer(
        message,
        prefer_pcr=want_pcr,
        student_name=req.studentName,
        history=history,
    )
    if curated:
        save_message(req.studentId, "user", message)
        save_message(req.studentId, "assistant", curated)
        if is_sistem_bk_message(message):
            kind = "sistem_bk"
        elif want_pcr or is_pcr_message(message):
            kind = "pcr"
        else:
            kind = "bk"
        return ChatResponse(reply=curated, meta={"kind": kind, "contexts": 0})

    # Retrieval cadangan — jangan untuk query lemah tanpa konteks PCR.
    contexts: list[dict] = []
    if want_pcr or has_strong_query_terms(message):
        contexts = retrieve_context(message, prefer_pcr=want_pcr)

    reply_text = build_local_reply(
        message,
        contexts,
        prefer_pcr=want_pcr,
        student_name=req.studentName,
        history=history,
    )

    save_message(req.studentId, "user", message)
    save_message(req.studentId, "assistant", reply_text)

    return ChatResponse(
        reply=reply_text,
        meta={"kind": "pcr" if want_pcr else "bk", "contexts": len(contexts)},
    )


@app.get("/health")
def health():
    indexed = os.path.exists(BOOK_INDEX_DB)
    count = 0
    pcr_chunks = 0
    if indexed:
        try:
            with sqlite3.connect(BOOK_INDEX_DB) as conn:
                count = conn.execute("SELECT COUNT(*) FROM chunks").fetchone()[0]
                pcr_chunks = conn.execute(
                    """
                    SELECT COUNT(*) FROM chunks
                    WHERE lower(source) LIKE '%pcr%' OR lower(source) LIKE '%pmb%'
                    """
                ).fetchone()[0]
        except sqlite3.Error:
            indexed = False

    ready = indexed and count > 0
    return {
        "status": "ok" if ready else "degraded",
        "index_ready": ready,
        "chunks": count,
        "pcr_chunks": pcr_chunks,
        "provider": CHATBOT_PROVIDER,
        "token_required": bool(CHATBOT_API_TOKEN),
    }
