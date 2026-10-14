# Chatbot Konseling Siswa (+ Knowledge PCR + Sistem BK)

Laravel mengirim pesan siswa ke FastAPI. Service menjawab **tanpa LLM eksternal** dari:

1. Jawaban terkurasi (BK + PCR/PMB + panduan menu Sistem BK)
2. Retrieval FTS: PDF buku BK + `knowledge/*.md`
3. Riwayat singkat per siswa (follow-up seperti “berapa biayanya?” setelah bicara PCR)

## Setup lokal

```bash
cd chatbot_service
# Python 3.11–3.13 (hindari 3.14+ bila pydantic belum kompatibel)
python3.13 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
```

Pastikan PDF buku ada dan folder `knowledge/` berisi file `.md`.

### Ingest (wajib setelah update knowledge)

```bash
python ingest_buku.py
```

Membuat ulang `book_index.sqlite3` (file ini **tidak** di-commit; harus di-build di setiap server).

### Jalankan

```bash
uvicorn app:app --host 127.0.0.1 --port 8001
```

Laravel `.env`:

```env
CHATBOT_SERVICE_URL=http://127.0.0.1:8001
CHATBOT_API_TOKEN=   # opsional di local; WAJIB di VPS/cloud
CHATBOT_TIMEOUT=30
```

Jika `CHATBOT_API_TOKEN` diisi di service, isi **nilai yang sama** di Laravel.

## Tes cepat

```bash
./scripts/smoke_test.sh
# atau:
curl http://127.0.0.1:8001/health
curl -X POST http://127.0.0.1:8001/chat \
  -H "Content-Type: application/json" \
  -H "X-Chatbot-Token: $CHATBOT_API_TOKEN" \
  -d '{"studentId":"test-1","message":"Jalur masuk PCR apa saja?"}'
```

## Deploy VPS / cloud (disarankan)

1. Clone repo, buat venv, `pip install -r requirements.txt`
2. Salin `.env.example` → `.env`, set `CHATBOT_API_TOKEN` (random panjang)
3. `python ingest_buku.py`
4. Bind **hanya localhost** (Nginx/Laravel di host yang sama) atau jaringan private:

```bash
uvicorn app:app --host 127.0.0.1 --port 8001
```

5. Laravel produksi:

```env
CHATBOT_SERVICE_URL=http://127.0.0.1:8001
CHATBOT_API_TOKEN=sama_dengan_service
```

6. Aktifkan systemd (contoh unit di `deploy/chatbot.service`)

**Jangan** expose port 8001 ke publik tanpa token + firewall.

> Laravel core memakai **port 8000**. Chatbot wajib di **8001** (atau port lain yang tidak bentrok), tanpa Docker.

## Aturan jawaban (agar tidak salah)

- PCR/PMB: hanya dari knowledge resmi + jawaban terkurasi; jadwal selalu diarahkan ke situs.
- Follow-up singkat memakai riwayat (mis. “berapa biayanya?” setelah topik PCR).
- Topik di luar BK/PCR ditolak dengan sopan.
- Krisis (ide bunuh diri dll.) → arahkan ke Guru BK + hotline 119 ext 8.
- Jangan mengarang passing grade / kuota / tanggal yang tidak ada di knowledge.

## Update knowledge

1. Edit/tambah `knowledge/*.md` (sumber resmi saja)
2. `python ingest_buku.py`
3. Restart uvicorn / systemd
