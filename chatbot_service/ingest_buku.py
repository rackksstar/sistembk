import os
import re
import sqlite3
import time

from dotenv import load_dotenv
from pypdf import PdfReader
from tqdm import tqdm

load_dotenv()

PDF_PATH = os.environ.get("BOOK_PDF_PATH", "Buku_Bimbingan_Konseling.pdf")
BOOK_INDEX_DB = os.environ.get("BOOK_INDEX_DB", "./book_index.sqlite3")
CHUNK_SIZE = int(os.environ.get("CHUNK_SIZE", "800"))
CHUNK_OVERLAP = int(os.environ.get("CHUNK_OVERLAP", "150"))


def clean_text(text: str) -> str:
    text = re.sub(r"\n{2,}", "\n", text)
    text = re.sub(r"[ \t]{2,}", " ", text)
    text = re.sub(r"^\s*\d+\s*$", "", text, flags=re.MULTILINE)
    return text.strip()


def chunk_text(text: str, chunk_size: int, overlap: int):
    chunks = []
    start = 0

    while start < len(text):
        end = start + chunk_size
        piece = text[start:end].strip()

        if piece:
            chunks.append(piece)

        start += chunk_size - overlap

    return chunks


def extract_page_text(page, page_number: int) -> str:
    try:
        return page.extract_text() or ""
    except Exception as exc:
        print(f"Melewati halaman {page_number}: gagal ekstrak teks ({exc})")
        return ""


def write_sqlite_index(chunks):
    if os.path.exists(BOOK_INDEX_DB):
        os.remove(BOOK_INDEX_DB)

    with sqlite3.connect(BOOK_INDEX_DB) as conn:
        conn.execute(
            """
            CREATE TABLE chunks (
                id INTEGER PRIMARY KEY,
                content TEXT NOT NULL,
                page INTEGER NOT NULL,
                source TEXT NOT NULL
            )
            """
        )
        conn.execute("CREATE VIRTUAL TABLE chunks_fts USING fts5(content)")

        for index, chunk in enumerate(chunks, start=1):
            conn.execute(
                "INSERT INTO chunks (id, content, page, source) VALUES (?, ?, ?, ?)",
                (index, chunk["content"], chunk["page"], "Buku Bimbingan Konseling"),
            )
            conn.execute(
                "INSERT INTO chunks_fts (rowid, content) VALUES (?, ?)",
                (index, chunk["content"]),
            )

    print(f"Selesai. Index buku tersimpan di: {BOOK_INDEX_DB}")


def main():
    if not os.path.exists(PDF_PATH):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {PDF_PATH}")

    print(f"Membaca PDF: {PDF_PATH}")
    reader = PdfReader(PDF_PATH)
    print(f"Total halaman: {len(reader.pages)}")

    all_chunks = []
    for index, page in enumerate(reader.pages):
        text = clean_text(extract_page_text(page, index + 1))

        if not text:
            continue

        for content in chunk_text(text, CHUNK_SIZE, CHUNK_OVERLAP):
            all_chunks.append({"content": content, "page": index + 1})

    print(f"Total chunk: {len(all_chunks)}")
    write_sqlite_index(tqdm(all_chunks, desc="Menyimpan index lokal"))


if __name__ == "__main__":
    main()
