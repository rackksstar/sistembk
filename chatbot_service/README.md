# Chatbot Konseling Siswa (+ Knowledge PCR)

Laravel mengirim pesan siswa ke FastAPI lokal. FastAPI menjawab dari:

1. **Buku Bimbingan Konseling** (PDF)
2. **Knowledge resmi PCR/PMB** (`knowledge/*.md`) dari [pmb.pcr.ac.id](https://pmb.pcr.ac.id) dan [pcr.ac.id](https://pcr.ac.id)

Tanpa layanan AI eksternal.

## Setup

```bash
cd chatbot_service
# Disarankan Python 3.11–3.13 (bukan 3.14+)
python3.13 -m venv venv
source venv/bin/activate   # Windows: venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env       # Windows: copy .env.example .env
```

Pastikan PDF buku ada (default: `Buku_Bimbingan_Konseling_Masdudi.pdf`) dan file knowledge di `knowledge/`.

## Ingest / Reindex (wajib setelah update knowledge)

```bash
python ingest_buku.py
```

Ini membuat ulang `book_index.sqlite3` dari PDF + seluruh `knowledge/*.md`.

## Jalankan Service

```bash
uvicorn app:app --host 127.0.0.1 --port 8000
```

Laravel memakai:

```env
CHATBOT_SERVICE_URL=http://127.0.0.1:8000
```

## Tes cepat

```bash
curl http://127.0.0.1:8000/health

curl -X POST http://127.0.0.1:8000/chat \
  -H "Content-Type: application/json" \
  -d '{"studentId":"test-1","message":"Jalur masuk PCR apa saja?"}'

curl -X POST http://127.0.0.1:8000/chat \
  -H "Content-Type: application/json" \
  -d '{"studentId":"test-1","message":"Berapa biaya kuliah PCR?"}'
```

## Update knowledge PCR

1. Edit / tambah file di `knowledge/` (sumber resmi saja).
2. Jalankan ulang `python ingest_buku.py`.
3. Restart `uvicorn` jika sudah berjalan.

Jangan mengarang jadwal/biaya di luar sumber resmi. Chatbot sudah diarahkan untuk selalu menyertakan tautan PMB.
