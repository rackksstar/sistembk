import os
import re
import sqlite3
from pathlib import Path

from dotenv import load_dotenv
from pypdf import PdfReader
from tqdm import tqdm

load_dotenv()

PDF_PATH = os.environ.get("BOOK_PDF_PATH", "Buku_Bimbingan_Konseling_Masdudi.pdf")
KNOWLEDGE_DIR = os.environ.get("KNOWLEDGE_DIR", "./knowledge")
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


def chunk_markdown_sections(text: str, source: str):
    """Split markdown by headings so PCR FAQs retrieve cleanly."""
    parts = re.split(r"(?m)^#{1,3}\s+", text)
    headings = re.findall(r"(?m)^#{1,3}\s+(.+)$", text)
    chunks = []
    section_no = 1

    # Intro before first heading
    if parts and parts[0].strip():
        for content in chunk_text(clean_text(parts[0]), CHUNK_SIZE, CHUNK_OVERLAP):
            chunks.append({
                "content": content,
                "page": section_no,
                "source": source,
            })
        section_no += 1

    for heading, body in zip(headings, parts[1:]):
        body_clean = clean_text(f"{heading.strip()}. {body}")
        if not body_clean:
            continue
        for content in chunk_text(body_clean, CHUNK_SIZE, CHUNK_OVERLAP):
            chunks.append({
                "content": content,
                "page": section_no,
                "source": source,
            })
        section_no += 1

    return chunks


def load_pdf_chunks(pdf_path: str):
    if not os.path.exists(pdf_path):
        print(f"Lewati PDF (tidak ditemukan): {pdf_path}")
        return []

    print(f"Membaca PDF: {pdf_path}")
    reader = PdfReader(pdf_path)
    print(f"Total halaman: {len(reader.pages)}")

    all_chunks = []
    for index, page in enumerate(reader.pages):
        text = clean_text(extract_page_text(page, index + 1))
        if not text:
            continue
        for content in chunk_text(text, CHUNK_SIZE, CHUNK_OVERLAP):
            all_chunks.append({
                "content": content,
                "page": index + 1,
                "source": "Buku Bimbingan Konseling",
            })

    return all_chunks


def load_knowledge_chunks(knowledge_dir: str):
    path = Path(knowledge_dir)
    if not path.exists():
        print(f"Lewati knowledge dir (tidak ditemukan): {knowledge_dir}")
        return []

    chunks = []
    for file_path in sorted(path.glob("*.md")):
        print(f"Membaca knowledge: {file_path.name}")
        text = file_path.read_text(encoding="utf-8")
        source = f"PCR Resmi ({file_path.stem})"
        chunks.extend(chunk_markdown_sections(text, source))

    for file_path in sorted(path.glob("*.txt")):
        print(f"Membaca knowledge: {file_path.name}")
        text = clean_text(file_path.read_text(encoding="utf-8"))
        source = f"PCR Resmi ({file_path.stem})"
        for idx, content in enumerate(chunk_text(text, CHUNK_SIZE, CHUNK_OVERLAP), start=1):
            chunks.append({"content": content, "page": idx, "source": source})

    return chunks


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
        conn.execute(
            "CREATE VIRTUAL TABLE chunks_fts USING fts5(content, source, tokenize='porter')"
        )

        for index, chunk in enumerate(chunks, start=1):
            conn.execute(
                "INSERT INTO chunks (id, content, page, source) VALUES (?, ?, ?, ?)",
                (index, chunk["content"], chunk["page"], chunk["source"]),
            )
            conn.execute(
                "INSERT INTO chunks_fts (rowid, content, source) VALUES (?, ?, ?)",
                (index, chunk["content"], chunk["source"]),
            )

    print(f"Selesai. Index tersimpan di: {BOOK_INDEX_DB} ({len(chunks)} chunk)")


def main():
    all_chunks = []
    all_chunks.extend(load_pdf_chunks(PDF_PATH))
    all_chunks.extend(load_knowledge_chunks(KNOWLEDGE_DIR))

    if not all_chunks:
        raise RuntimeError("Tidak ada chunk untuk diindeks. Pastikan PDF atau knowledge/*.md tersedia.")

    print(f"Total chunk: {len(all_chunks)}")
    write_sqlite_index(list(tqdm(all_chunks, desc="Menyimpan index lokal")))


if __name__ == "__main__":
    main()
