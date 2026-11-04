import sqlite3
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from fastapi.testclient import TestClient

from chatbot_service import app as chatbot
from chatbot_service import ingest_buku


class ChatbotTest(unittest.TestCase):
    def setUp(self):
        self.temp_dir = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp_dir.cleanup)
        self.memory_patch = patch.object(chatbot, "MEMORY_DB", Path(self.temp_dir.name) / "memory.sqlite3")
        self.index_patch = patch.object(chatbot, "BOOK_INDEX_DB", Path(self.temp_dir.name) / "missing.sqlite3")
        self.memory_patch.start()
        self.index_patch.start()
        self.addCleanup(self.memory_patch.stop)
        self.addCleanup(self.index_patch.stop)
        self.client = TestClient(chatbot.app)

    def send(self, message):
        return self.client.post("/chat", json={"studentId": "siswa-1", "message": message})

    def test_common_question_works_without_book_index(self):
        response = self.send("Apa itu konseling?")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Konseling itu", response.json()["reply"])

    def test_unknown_question_does_not_quote_unrelated_book_page(self):
        response = self.send("Apa makanan favoritmu?")
        self.assertEqual(response.status_code, 200)
        self.assertIn("lebih spesifik", response.json()["reply"])

    def test_relevant_book_sentence_is_used_without_arbitrary_fallback(self):
        with sqlite3.connect(chatbot.BOOK_INDEX_DB) as connection:
            connection.execute("CREATE TABLE chunks (id INTEGER PRIMARY KEY, content TEXT, page INTEGER, source TEXT)")
            connection.execute("CREATE VIRTUAL TABLE chunks_fts USING fts5(content)")
            sentence = "Layanan orientasi membantu siswa mengenal sekolah baru dengan lebih percaya diri."
            connection.execute("INSERT INTO chunks VALUES (1, ?, 10, 'Buku BK')", (sentence,))
            connection.execute("INSERT INTO chunks_fts (rowid, content) VALUES (1, ?)", (sentence,))

        relevant = self.send("Apa manfaat orientasi bagi siswa?")
        self.assertEqual(relevant.status_code, 200)
        self.assertIn("Buku BK, hlm. 10", relevant.json()["reply"])

        unrelated = self.send("Apa makanan favoritmu?")
        self.assertIn("lebih spesifik", unrelated.json()["reply"])

    def test_crisis_message_is_prioritized(self):
        response = self.send("Aku ingin bunuh diri")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Guru BK", response.json()["reply"])

    def test_empty_message_rejected(self):
        response = self.send("   ")
        self.assertEqual(response.status_code, 400)

    def test_service_paths_are_relative_to_service_directory(self):
        self.assertEqual(chatbot.service_path("book_index.sqlite3"), chatbot.BASE_DIR / "book_index.sqlite3")

    def test_reingest_replaces_index_after_database_is_closed(self):
        target = Path(self.temp_dir.name) / "book.sqlite3"
        with patch.object(ingest_buku, "BOOK_INDEX_DB", target):
            ingest_buku.write_sqlite_index([{"content": "Versi pertama", "page": 10, "source": "Buku BK"}])
            ingest_buku.write_sqlite_index([{"content": "Versi kedua", "page": 11, "source": "Buku BK"}])

        with sqlite3.connect(target) as connection:
            self.assertEqual(connection.execute("SELECT content FROM chunks").fetchall(), [("Versi kedua",)])


if __name__ == "__main__":
    unittest.main()
