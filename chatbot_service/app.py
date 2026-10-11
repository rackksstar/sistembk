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
TOP_K = int(os.environ.get("CHATBOT_TOP_K", "6"))
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
        if len(sentence.strip()) > 25
    ]


def query_terms(query: str):
    stopwords = {
        "ada", "aku", "apa", "atau", "bagaimana", "bisa", "buat", "cara",
        "dan", "dari", "dengan", "di", "ini", "itu", "jadi", "kalau", "ke",
        "mau", "saya", "sebagai", "tentang", "untuk", "yang", "apakah",
        "berapa", "dimana", "mana", "tolong", "mohon",
    }

    terms = [
        term for term in re.findall(r"[A-Za-z0-9_]+", query.lower())
        if len(term) > 2 and term not in stopwords
    ]

    # Synonyms / expansions for better PCR retrieval
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
    }

    expanded = list(terms)
    for term in terms:
        expanded.extend(expansions.get(term, []))

    return list(dict.fromkeys(expanded))


def build_fts_query(query: str) -> str:
    return " OR ".join(query_terms(query)[:12])


def is_pcr_message(message: str) -> bool:
    lowered = message.lower()
    keywords = [
        "pcr", "caltex", "pmb", "politeknik", "prodi", "program studi",
        "umpcr", "psud", "beasiswa", "spp", "spi", "kipk", "rapor",
        "pendaftaran", "kuliah di", "kampus pcr", "pekanbaru",
        "teknik informatika", "sistem informasi", "akuntansi perpajakan",
        "mekatronika", "animasi", "bisnis digital", "humas",
    ]
    return any(key in lowered for key in keywords)


def is_pcr_source(source: str) -> bool:
    return "pcr" in (source or "").lower() or "pmb" in (source or "").lower()


def retrieve_context(query: str, k: int = TOP_K, prefer_pcr: bool = False):
    if not os.path.exists(BOOK_INDEX_DB):
        raise HTTPException(
            status_code=503,
            detail="Index chatbot belum tersedia. Jalankan ingest_buku.py terlebih dahulu.",
        )

    fts_query = build_fts_query(query)

    with sqlite3.connect(BOOK_INDEX_DB) as conn:
        conn.row_factory = sqlite3.Row

        rows = []
        if fts_query:
            # PCR knowledge uses page as section number; book uses MIN_CONTENT_PAGE filter.
            if prefer_pcr:
                rows = conn.execute(
                    """
                    SELECT chunks.page, chunks.source, chunks.content
                    FROM chunks_fts
                    JOIN chunks ON chunks_fts.rowid = chunks.id
                    WHERE chunks_fts MATCH ?
                      AND (chunks.source LIKE '%PCR%' OR chunks.source LIKE '%pmb%' OR chunks.page >= ?)
                    ORDER BY
                      CASE WHEN chunks.source LIKE '%PCR%' THEN 0 ELSE 1 END,
                      bm25(chunks_fts)
                    LIMIT ?
                    """,
                    (fts_query, MIN_CONTENT_PAGE, k),
                ).fetchall()
            else:
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

                # Also pull PCR hits even if page filter skipped them
                pcr_rows = conn.execute(
                    """
                    SELECT chunks.page, chunks.source, chunks.content
                    FROM chunks_fts
                    JOIN chunks ON chunks_fts.rowid = chunks.id
                    WHERE chunks_fts MATCH ?
                      AND (chunks.source LIKE '%PCR%' OR chunks.source LIKE '%pmb%')
                    ORDER BY bm25(chunks_fts)
                    LIMIT ?
                    """,
                    (fts_query, max(2, k // 2)),
                ).fetchall()
                rows = list({(r["source"], r["page"], r["content"]): r for r in list(rows) + list(pcr_rows)}.values())

        if not rows:
            if prefer_pcr:
                rows = conn.execute(
                    """
                    SELECT page, source, content FROM chunks
                    WHERE source LIKE '%PCR%' OR source LIKE '%pmb%'
                    ORDER BY id LIMIT ?
                    """,
                    (k,),
                ).fetchall()
            else:
                rows = conn.execute(
                    "SELECT page, source, content FROM chunks WHERE page >= ? ORDER BY id LIMIT ?",
                    (MIN_CONTENT_PAGE, k),
                ).fetchall()

    context_parts = []
    for row in rows[:k]:
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


def direct_pcr_answer(message: str):
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
            "4. **Beasiswa** — ada Yayasan PCR, Pemda, KIPK, dan alumni (syarat detail di formulir PMB).\n\n"
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


def direct_answer(message: str):
    if is_pcr_message(message):
        pcr = direct_pcr_answer(message)
        if pcr:
            return pcr

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


def build_local_reply(message: str, contexts):
    answer = direct_answer(message)
    if answer:
        return answer

    selected = pick_sentences(message, contexts)
    prefer_pcr = is_pcr_message(message)

    if not selected:
        if prefer_pcr:
            return with_pcr_closing(
                "Aku belum menemukan detail yang cukup spesifik dari knowledge resmi PCR untuk pertanyaan itu. "
                "Coba sebutkan lebih jelas: jalur masuk, biaya, prodi, beasiswa, atau kontak PMB. "
                "Sementara itu kamu bisa cek https://pmb.pcr.ac.id atau hubungi Tim PMB."
            )
        return with_guidance_closing(
            "Aku belum cukup paham maksud pertanyaanmu. Coba tulis lebih spesifik ya, "
            "misalnya tentang belajar, pertemanan, karier, keluarga, PCR/PMB, atau cara menghadapi masalah tertentu."
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
def chat(req: ChatRequest):
    message = req.message.strip()

    if not message:
        raise HTTPException(status_code=400, detail="Pesan tidak boleh kosong.")

    if CHATBOT_PROVIDER != "local":
        raise HTTPException(status_code=503, detail="Provider chatbot tidak tersedia. Gunakan CHATBOT_PROVIDER=local.")

    contexts = retrieve_context(message, prefer_pcr=is_pcr_message(message))
    reply_text = build_local_reply(message, contexts)

    save_message(req.studentId, "user", message)
    save_message(req.studentId, "assistant", reply_text)

    return ChatResponse(reply=reply_text)


@app.get("/health")
def health():
    indexed = os.path.exists(BOOK_INDEX_DB)
    count = 0
    if indexed:
        with sqlite3.connect(BOOK_INDEX_DB) as conn:
            count = conn.execute("SELECT COUNT(*) FROM chunks").fetchone()[0]
    return {"status": "ok", "index_ready": indexed, "chunks": count}
