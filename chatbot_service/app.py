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


class ChatResponse(BaseModel):
    reply: str
    meta: dict | None = None


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
        "umpcr", "psud", "beasiswa", "spp", "spi", "kipk", "rapor",
        "pendaftaran", "kuliah di", "kampus pcr", "pekanbaru",
        "teknik informatika", "sistem informasi", "akuntansi perpajakan",
        "mekatronika", "animasi", "bisnis digital", "humas",
        "rekognisi pembelajaran", "rpl kuliah",
    ]
    return any(key in lowered for key in keywords)


def history_suggests_pcr(history: list[tuple[str, str]]) -> bool:
    for role, content in reversed(history[-8:]):
        text = (content or "").lower()
        if "pmb.pcr.ac.id" in text or "politeknik caltex" in text:
            return True
        if role == "user" and is_pcr_message(text):
            return True
        if role == "assistant" and ("pcr" in text or "pmb" in text):
            return True
    return False


def is_followup_message(message: str) -> bool:
    lowered = message.lower().strip()
    if len(lowered.split()) <= 8:
        cues = [
            "itu", "yang tadi", "lanjut", "lebih detail", "jelaskan", "berapa",
            "contoh", "terus", "lalu", "yang kedua", "yang pertama", "oke",
            "iya", "ya", "gimana", "bagaimana", "detail", "syarat", "jadwal",
        ]
        return any(cue in lowered for cue in cues)
    return False


def prefer_pcr_context(message: str, history: list[tuple[str, str]]) -> bool:
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


def guidance_closing() -> str:
    return (
        "Kalau kamu ingin cerita lebih lanjut atau butuh arahan yang lebih sesuai dengan keadaanmu, "
        "kamu bisa lanjut bimbingan konseling dengan Guru BK di sekolah. Tidak apa-apa minta bantuan, "
        "justru itu langkah yang baik."
    )


def pcr_closing() -> str:
    return (
        "Sumber resmi: https://pmb.pcr.ac.id dan https://pcr.ac.id.\n"
        "Kontak PMB: 081170750001 / 081170750002 / 081170750003 · email pmb@pcr.ac.id.\n"
        "Untuk tanggal/biaya paling baru, cek langsung situs resmi karena bisa berubah. "
        "Kalau masih bingung pilih prodi, diskusikan juga dengan Guru BK atau menu Konsultasi Prodi di sistem."
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

        if not rows:
            if prefer_pcr:
                rows = conn.execute(
                    """
                    SELECT page, source, content FROM chunks
                    WHERE lower(source) LIKE '%pcr%' OR lower(source) LIKE '%pmb%'
                    ORDER BY id LIMIT ?
                    """,
                    (k,),
                ).fetchall()
            else:
                rows = conn.execute(
                    "SELECT page, source, content FROM chunks WHERE page >= ? ORDER BY id LIMIT ?",
                    (MIN_CONTENT_PAGE, k),
                ).fetchall()

    return [
        {
            "source": row["source"],
            "page": row["page"],
            "content": normalize_text(row["content"]),
        }
        for row in rows[:k]
    ]


def pick_sentences(query: str, contexts: list[dict]):
    terms = set(query_terms(query))
    ranked = []

    for context in contexts:
        for sentence in split_sentences(context["content"]):
            if len(sentence) > 320:
                continue
            sentence_terms = set(query_terms(sentence))
            score = len(terms & sentence_terms)
            if is_pcr_source(context["source"]):
                score += 1
            if score > 0:
                ranked.append((score, context["source"], context["page"], sentence))

    ranked.sort(key=lambda item: item[0], reverse=True)
    selected = []
    seen = set()

    for _, source, page, sentence in ranked:
        key = sentence.lower()
        if key in seen:
            continue
        seen.add(key)
        selected.append((source, page, sentence))
        if len(selected) >= 5:
            break

    if selected:
        return selected

    fallback = []
    for context in contexts[:2]:
        sentences = split_sentences(context["content"])
        if sentences:
            fallback.append((context["source"], context["page"], sentences[0]))
    return fallback


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

    if any(k in lowered for k in ["jalur", "psud", "umpcr", "mandiri", "cara daftar", "pendaftaran"]):
        return with_pcr_closing(
            "PCR punya beberapa jalur masuk utama:\n\n"
            "1. **PSUD** — pakai nilai rapor semester I–IV, rata-rata minimal **8,0/80**, **gratis**, tanpa ujian. "
            "IPA bisa pilih semua prodi; IPS hanya **Akuntansi Perpajakan** dan **Sistem Informasi**.\n"
            "2. **UMPCR** — ujian bersama (gelombang I/II/III), CBT, ada biaya formulir, pilih prodi bebas asal lulus passing grade.\n"
            "3. **Ujian Mandiri** — CBT hari kerja (Senin–Jumat), hasil bisa dilihat hari yang sama.\n"
            "4. **Beasiswa** — Yayasan PCR, Pemda, KIPK, dan alumni (syarat detail di formulir PMB).\n\n"
            "Alur daftar singkat: buat akun email → verifikasi → pilih formulir jalur aktif → bayar (jika berbayar) → "
            "lengkapi data/berkas → seleksi → pengumuman → daftar ulang jika lulus.\n\n"
            "Detail: https://pmb.pcr.ac.id/panduan/informasi · jadwal: https://pmb.pcr.ac.id/panduan/jadwal"
        )

    if any(k in lowered for k in ["biaya", "spp", "spi", "uang kuliah", "bayar"]):
        return with_pcr_closing(
            "Ringkasan biaya resmi PCR (cek ulang di situs karena bisa berubah):\n\n"
            "- Orientasi & seragam: **Rp 2.000.000** (sekali saat registrasi ulang)\n"
            "- SPP per semester: **Rp 7.100.000** atau **Rp 7.500.000**\n"
            "  (Rp 7.500.000 untuk Teknik Informatika, Sistem Informasi, Teknik Mesin, dan Teknik Mekatronika)\n"
            "- Pengembangan institusi (SPI): disebut mulai sekitar **Rp 12.000.000** (ada skema lain di situs)\n\n"
            "Pembayaran bisa dicicil/bertahap. Detail: https://pmb.pcr.ac.id/panduan/biaya"
        )

    if any(k in lowered for k in ["jadwal", "tanggal daftar", "kapan daftar", "deadline"]):
        return with_pcr_closing(
            "Jadwal PMB PCR berubah tiap tahun akademik, jadi aku **tidak mengunci tanggal** di sini "
            "agar tidak salah informasi.\n\n"
            "Cek tanggal resmi di: https://pmb.pcr.ac.id/panduan/jadwal"
        )

    if any(k in lowered for k in ["prodi", "jurusan", "program studi", "teknik informatika", "sistem informasi", "animasi"]):
        return with_pcr_closing(
            "Program studi PCR (Sarjana Terapan / D4) dikelompokkan seperti ini:\n\n"
            "**Teknologi Informasi:** Teknik Informatika, Sistem Informasi, Teknologi Rekayasa Komputer, Animasi "
            "(plus Magister Terapan Teknik Komputer).\n"
            "**Teknologi Industri:** Teknik Elektronika, Teknik Listrik, Teknik Mesin, TRJT, TRSE, Mekatronika, "
            "Kecerdasan Buatan dan Robotika.\n"
            "**Bisnis & Komunikasi:** Akuntansi Perpajakan, Hubungan Masyarakat dan Komunikasi Digital, Bisnis Digital.\n\n"
            "Kalau kamu IPS dan mau jalur PSUD, pilihan terbatas ke Akuntansi Perpajakan & Sistem Informasi; "
            "prodi lain biasanya lewat UMPCR/Mandiri.\n"
            "Daftar lengkap: https://pmb.pcr.ac.id/prodi"
        )

    if "beasiswa" in lowered or "kip" in lowered:
        return with_pcr_closing(
            "PCR punya beberapa beasiswa, antara lain:\n"
            "- Beasiswa Prestasi Yayasan PCR\n"
            "- Beasiswa Pemerintah Daerah\n"
            "- Beasiswa KIPK (SPP + biaya hidup)\n"
            "- Beasiswa Ikatan Keluarga Alumni PCR\n\n"
            "Untuk KIP/DTKS/PKH biasanya wajib diunggah salah satunya. Syarat lengkap ada di "
            "https://pmb.pcr.ac.id/formulir dan info jalur di https://pmb.pcr.ac.id/panduan/informasi"
        )

    if any(k in lowered for k in ["kontak", "whatsapp", "hubungi", "alamat", "email pmb"]):
        return with_pcr_closing(
            "Kontak resmi PMB PCR:\n"
            "- WA/Telepon: 081170750001, 081170750002, 081170750003\n"
            "- Bantuan data/berkas (FAQ): 082375369696\n"
            "- Email: pmb@pcr.ac.id / pcr@pcr.ac.id\n"
            "- Alamat: Jl. Umbansari No.1, Rumbai, Pekanbaru, Riau 28265\n"
            "- Situs: https://pmb.pcr.ac.id · https://pcr.ac.id"
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


def direct_answer(message: str, prefer_pcr: bool = False) -> str | None:
    if is_sistem_bk_message(message):
        sistem = sistem_bk_answer(message)
        if sistem:
            return sistem

    if prefer_pcr or is_pcr_message(message):
        # Follow-up biaya/jadwal tanpa kata "PCR" tetap dijawab akurat.
        if prefer_pcr and not is_pcr_message(message):
            lowered = message.lower()
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

        pcr = direct_pcr_answer(message)
        if pcr:
            return pcr

    return direct_bk_answer(message)


def build_local_reply(message: str, contexts: list[dict], prefer_pcr: bool) -> str:
    answer = direct_answer(message, prefer_pcr=prefer_pcr)
    if answer:
        return answer

    selected = pick_sentences(message, contexts)

    if not selected:
        if prefer_pcr:
            return with_pcr_closing(
                "Aku belum menemukan detail yang cukup spesifik dari knowledge resmi PCR untuk pertanyaan itu. "
                "Coba sebutkan lebih jelas: jalur masuk, biaya, prodi, beasiswa, atau kontak PMB. "
                "Sementara itu kamu bisa cek https://pmb.pcr.ac.id atau hubungi Tim PMB."
            )
        return with_guidance_closing(
            "Aku belum cukup paham maksud pertanyaanmu. Coba tulis lebih spesifik ya, "
            "misalnya tentang belajar, pertemanan, karier, PCR/PMB, Minat Bakat, atau cara menghadapi masalah tertentu."
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
        opening = "Intinya, ini bisa dipahami dengan sederhana seperti ini."
        closing = guidance_closing()

    points = []
    for index, (source, page, sentence) in enumerate(selected, start=1):
        cleaned = re.sub(r"\s+-\s+", " ", sentence)
        cleaned = re.sub(r"\s+", " ", cleaned).strip()
        label = "bagian" if is_pcr_source(source) else "hal."
        points.append(f"{index}. {cleaned} ({source}, {label} {page})")

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

    history = load_history(req.studentId)
    want_pcr = prefer_pcr_context(message, history)
    contexts = retrieve_context(message, prefer_pcr=want_pcr)
    reply_text = build_local_reply(message, contexts, prefer_pcr=want_pcr)

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
