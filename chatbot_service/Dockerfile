FROM python:3.13-slim

WORKDIR /app

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    BOOK_INDEX_DB=/app/book_index.sqlite3 \
    MEMORY_DB=/app/chat_memory.sqlite3 \
    KNOWLEDGE_DIR=/app/knowledge \
    BOOK_PDF_PATH=/app/Buku_Bimbingan_Konseling_Masdudi.pdf \
    CHATBOT_PROVIDER=local

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app.py ingest_buku.py ./
COPY knowledge ./knowledge
COPY Buku_Bimbingan_Konseling_Masdudi.pdf ./

RUN python ingest_buku.py

EXPOSE 8000

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]
