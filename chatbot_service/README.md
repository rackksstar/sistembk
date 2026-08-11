# Chatbot Konseling Siswa

Laravel mengirim pesan siswa ke FastAPI lokal, lalu FastAPI mengambil konteks dari index buku lokal dan menyusun balasan tanpa layanan AI eksternal.

## Setup

```bash
cd chatbot_service
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
```

Taruh PDF buku BK sesuai nilai `BOOK_PDF_PATH`.

## Ingest Buku

Jalankan sekali setelah PDF siap:

```bash
python ingest_buku.py
```

Perintah ini membuat `book_index.sqlite3` yang dipakai chatbot saat menjawab.

## Jalankan Service

```bash
uvicorn app:app --host 127.0.0.1 --port 8000
```

Laravel membaca URL service dari `CHATBOT_SERVICE_URL`. Default-nya:

```env
CHATBOT_SERVICE_URL=http://127.0.0.1:8000
```

Tes cepat:

```bash
curl -X POST http://127.0.0.1:8000/chat -H "Content-Type: application/json" -d "{\"studentId\":\"test-1\",\"message\":\"Apa itu konseling?\"}"
```
