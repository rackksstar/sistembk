-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sistembk
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sistembk`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sistembk` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sistembk`;

--
-- Table structure for table `assessment_responses`
--

DROP TABLE IF EXISTS `assessment_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `talent_interest` json NOT NULL,
  `sociometry` json NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessment_responses_student_id_foreign` (`student_id`),
  CONSTRAINT `assessment_responses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessment_responses`
--

LOCK TABLES `assessment_responses` WRITE;
/*!40000 ALTER TABLE `assessment_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-rakha23ti@mahasiswa.pcr.ac.id|127.0.0.1','i:1;',1779100771),('laravel-cache-rakha23ti@mahasiswa.pcr.ac.id|127.0.0.1:timer','i:1779100771;',1779100771);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_infos`
--

DROP TABLE IF EXISTS `career_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_infos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_infos`
--

LOCK TABLES `career_infos` WRITE;
/*!40000 ALTER TABLE `career_infos` DISABLE KEYS */;
INSERT INTO `career_infos` VALUES (1,'Software Engineer','Merancang, membangun, dan memelihara aplikasi digital. Cocok untuk siswa yang senang logika, problem solving, dan teknologi.','Teknologi',NULL,'2026-05-18 03:30:21','2026-05-18 03:30:21'),(2,'Konselor Pendidikan','Membantu siswa memahami potensi diri, pilihan studi, serta strategi belajar yang lebih sehat dan terarah.','Pendidikan',NULL,'2026-05-18 03:30:21','2026-05-18 03:30:21'),(3,'Desainer UI/UX','Menciptakan pengalaman aplikasi yang mudah digunakan, indah, dan sesuai kebutuhan pengguna.','Kreatif',NULL,'2026-05-18 03:30:21','2026-05-18 03:30:21');
/*!40000 ALTER TABLE `career_infos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classes_school_id_name_unique` (`school_id`,`name`),
  CONSTRAINT `classes_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,'XII IPA 1','XII','2026-05-18 03:30:17','2026-05-18 03:30:17'),(2,1,'XI IPS 2','XI','2026-05-18 03:30:17','2026-05-18 03:30:17');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultation_requests`
--

DROP TABLE IF EXISTS `consultation_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `counselor_id` bigint unsigned DEFAULT NULL,
  `subject` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `case_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_time` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consultation_date` date DEFAULT NULL,
  `consultation_time` time DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `result` text COLLATE utf8mb4_unicode_ci,
  `evaluation` text COLLATE utf8mb4_unicode_ci,
  `follow_up` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultation_requests_student_id_foreign` (`student_id`),
  KEY `consultation_requests_counselor_id_foreign` (`counselor_id`),
  KEY `consultation_requests_status_index` (`status`),
  KEY `consultation_requests_case_category_index` (`case_category`),
  CONSTRAINT `consultation_requests_counselor_id_foreign` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `consultation_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultation_requests`
--

LOCK TABLES `consultation_requests` WRITE;
/*!40000 ALTER TABLE `consultation_requests` DISABLE KEYS */;
INSERT INTO `consultation_requests` VALUES (1,4,3,'Persiapan ujian akhir','belajar','Senin pagi','2026-05-21','09:00:00','Saya ingin berdiskusi tentang manajemen waktu belajar sebelum ujian.','disetujui','2026-05-21 02:00:00','Sesi awal sudah dijadwalkan.','Siswa memahami hambatan utama berupa distraksi gawai dan belum memiliki jadwal belajar harian.','Siswa aktif menyusun prioritas kegiatan dan bersedia mencoba jadwal belajar selama satu minggu.','Pantau jurnal belajar siswa pada sesi berikutnya.','2026-05-18 03:30:19','2026-05-18 03:30:19'),(2,4,3,'Latihan komunikasi dengan teman sebaya','sosial','Kamis siang','2026-05-12','10:00:00','Siswa ingin lebih percaya diri saat kerja kelompok.','selesai','2026-05-12 03:00:00','Sesi selesai.','Siswa dapat menyebutkan contoh kalimat asertif untuk menyampaikan pendapat.','Siswa perlu latihan bertahap dalam kelompok kecil.','Libatkan siswa dalam bimbingan kelompok komunikasi asertif.','2026-05-18 03:30:19','2026-05-18 03:30:19'),(3,4,NULL,'Konsultasi pemilihan jurusan','karier','Rabu siang',NULL,NULL,'Butuh arahan untuk memilih jurusan yang sesuai minat.','pending',NULL,NULL,NULL,NULL,NULL,'2026-05-18 03:30:19','2026-05-18 03:30:19');
/*!40000 ALTER TABLE `consultation_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guidance_class_student`
--

DROP TABLE IF EXISTS `guidance_class_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guidance_class_student` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guidance_class_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guidance_class_student_guidance_class_id_student_id_unique` (`guidance_class_id`,`student_id`),
  KEY `guidance_class_student_student_id_foreign` (`student_id`),
  CONSTRAINT `guidance_class_student_guidance_class_id_foreign` FOREIGN KEY (`guidance_class_id`) REFERENCES `guidance_classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guidance_class_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guidance_class_student`
--

LOCK TABLES `guidance_class_student` WRITE;
/*!40000 ALTER TABLE `guidance_class_student` DISABLE KEYS */;
INSERT INTO `guidance_class_student` VALUES (1,1,1,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(2,1,2,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(3,1,3,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(4,1,4,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(5,1,5,'2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `guidance_class_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guidance_classes`
--

DROP TABLE IF EXISTS `guidance_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guidance_classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guidance_classes_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guidance_classes`
--

LOCK TABLES `guidance_classes` WRITE;
/*!40000 ALTER TABLE `guidance_classes` DISABLE KEYS */;
INSERT INTO `guidance_classes` VALUES (1,'Kelas Bimbingan Karier XII','BK-KARIER','Kelompok bimbingan untuk persiapan studi lanjut dan pilihan karier.','2026-05-18 03:30:19','2026-05-18 03:30:19');
/*!40000 ALTER TABLE `guidance_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guru_bks`
--

DROP TABLE IF EXISTS `guru_bks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru_bks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sekolah_id` bigint unsigned DEFAULT NULL,
  `nip` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bidang_studi` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guru_bks_user_id_unique` (`user_id`),
  UNIQUE KEY `guru_bks_sekolah_id_nip_unique` (`sekolah_id`,`nip`),
  CONSTRAINT `guru_bks_sekolah_id_foreign` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolahs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guru_bks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru_bks`
--

LOCK TABLES `guru_bks` WRITE;
/*!40000 ALTER TABLE `guru_bks` DISABLE KEYS */;
INSERT INTO `guru_bks` VALUES (1,3,1,'1987654321001','Guru BK','Bimbingan Konseling','2026-05-18 03:30:18','2026-05-18 03:30:18');
/*!40000 ALTER TABLE `guru_bks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instrument_answers`
--

DROP TABLE IF EXISTS `instrument_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instrument_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instrument_submission_id` bigint unsigned NOT NULL,
  `instrument_question_id` bigint unsigned NOT NULL,
  `answer_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instrument_answers_instrument_submission_id_foreign` (`instrument_submission_id`),
  KEY `instrument_answers_instrument_question_id_foreign` (`instrument_question_id`),
  CONSTRAINT `instrument_answers_instrument_question_id_foreign` FOREIGN KEY (`instrument_question_id`) REFERENCES `instrument_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `instrument_answers_instrument_submission_id_foreign` FOREIGN KEY (`instrument_submission_id`) REFERENCES `instrument_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instrument_answers`
--

LOCK TABLES `instrument_answers` WRITE;
/*!40000 ALTER TABLE `instrument_answers` DISABLE KEYS */;
INSERT INTO `instrument_answers` VALUES (1,1,1,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(2,1,2,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(3,1,3,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(4,2,7,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(5,2,8,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(6,2,9,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(7,3,7,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(8,3,8,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(9,3,9,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(10,4,7,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(11,4,8,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(12,4,9,'Sesuai',3,'2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `instrument_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instrument_questions`
--

DROP TABLE IF EXISTS `instrument_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instrument_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instrument_questions_created_by_foreign` (`created_by`),
  CONSTRAINT `instrument_questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instrument_questions`
--

LOCK TABLES `instrument_questions` WRITE;
/*!40000 ALTER TABLE `instrument_questions` DISABLE KEYS */;
INSERT INTO `instrument_questions` VALUES (1,'minat_bakat','Saya bersemangat saat mengerjakan aktivitas yang membutuhkan ide baru.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(2,'minat_bakat','Saya mudah menikmati pelajaran atau kegiatan yang menantang kemampuan berpikir.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(3,'minat_bakat','Saya memiliki aktivitas favorit yang ingin saya dalami sebagai rencana masa depan.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(4,'gaya_belajar','Saya lebih mudah memahami materi ketika melihat gambar, diagram, atau warna.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(5,'gaya_belajar','Saya lebih cepat mengingat materi setelah mendengar penjelasan atau diskusi.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(6,'gaya_belajar','Saya senang belajar dengan praktik langsung atau membuat contoh nyata.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(7,'kepribadian','Saya mampu menenangkan diri ketika menghadapi situasi yang menekan.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(8,'kepribadian','Saya nyaman bekerja sama dengan teman yang berbeda pendapat.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(9,'kepribadian','Saya berani menyampaikan kebutuhan saya dengan cara yang sopan.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(10,'sosiometri','Saya mudah memilih teman untuk bekerja sama dalam kelompok belajar.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(11,'sosiometri','Saya merasa diterima dalam pergaulan kelas.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(12,'sosiometri','Saya memiliki teman yang dapat dipercaya saat mengalami kesulitan.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(13,'angket_masalah','Saya sering merasa sulit berkonsentrasi saat belajar.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(14,'angket_masalah','Saya merasa cemas ketika menghadapi tugas atau ujian.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(15,'angket_masalah','Saya mengalami kesulitan berkomunikasi dengan teman di sekolah.','[{\"label\": \"Sangat Tidak Sesuai\", \"score\": 1}, {\"label\": \"Tidak Sesuai\", \"score\": 2}, {\"label\": \"Sesuai\", \"score\": 3}, {\"label\": \"Sangat Sesuai\", \"score\": 4}]',1,3,'2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `instrument_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instrument_submissions`
--

DROP TABLE IF EXISTS `instrument_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instrument_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_score` int unsigned NOT NULL DEFAULT '0',
  `result_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_description` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instrument_submissions_student_id_foreign` (`student_id`),
  CONSTRAINT `instrument_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instrument_submissions`
--

LOCK TABLES `instrument_submissions` WRITE;
/*!40000 ALTER TABLE `instrument_submissions` DISABLE KEYS */;
INSERT INTO `instrument_submissions` VALUES (1,4,'minat_bakat',10,'Sangat Menonjol','Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.','2026-05-18 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20'),(2,4,'kepribadian',9,'Cukup Berkembang','Kecenderungan personal siswa sudah terlihat dan dapat diperkuat melalui bimbingan.','2026-05-13 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20'),(3,6,'kepribadian',9,'Cukup Berkembang','Kecenderungan personal siswa sudah terlihat dan dapat diperkuat melalui bimbingan.','2026-05-17 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20'),(4,7,'kepribadian',9,'Cukup Berkembang','Kecenderungan personal siswa sudah terlihat dan dapat diperkuat melalui bimbingan.','2026-05-12 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `instrument_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelas`
--

DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sekolah_id` bigint unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenjang` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkatan` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kelas_sekolah_id_nama_unique` (`sekolah_id`,`nama`),
  CONSTRAINT `kelas_sekolah_id_foreign` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolahs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelas`
--

LOCK TABLES `kelas` WRITE;
/*!40000 ALTER TABLE `kelas` DISABLE KEYS */;
INSERT INTO `kelas` VALUES (1,1,'XII IPA 1','SMA','XII','2026-05-18 03:30:17','2026-05-18 03:30:17');
/*!40000 ALTER TABLE `kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_questions`
--

DROP TABLE IF EXISTS `master_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kategori` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teks_pertanyaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_input` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_questions_kategori_index` (`kategori`),
  KEY `master_questions_tipe_input_index` (`tipe_input`),
  KEY `master_questions_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_questions`
--

LOCK TABLES `master_questions` WRITE;
/*!40000 ALTER TABLE `master_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_29_000001_add_role_to_users_table',1),(5,'2026_04_29_000002_create_consultation_requests_table',1),(6,'2026_04_29_000003_add_status_to_users_table',1),(7,'2026_04_29_000004_create_career_infos_table',1),(8,'2026_04_29_000005_add_school_to_users_table',1),(9,'2026_04_29_000006_update_consultation_requests_for_workflow',1),(10,'2026_04_29_000007_create_students_table',1),(11,'2026_04_29_000008_create_guidance_classes_table',1),(12,'2026_04_29_000009_add_code_to_guidance_classes_table',1),(13,'2026_04_30_000001_create_assessment_responses_table',1),(14,'2026_05_06_000001_add_unique_user_id_to_students_table',1),(15,'2026_05_06_000100_create_sekolahs_table',1),(16,'2026_05_06_000101_create_kelas_table',1),(17,'2026_05_06_000102_create_guru_bks_table',1),(18,'2026_05_06_000103_add_kelas_fields_to_students_table',1),(19,'2026_05_06_000104_create_master_questions_table',1),(20,'2026_05_06_000105_create_post_categories_table',1),(21,'2026_05_10_000001_create_instrument_questions_table',1),(22,'2026_05_10_000002_create_instrument_submissions_table',1),(23,'2026_05_10_000003_create_sociometry_responses_table',1),(24,'2026_05_10_000004_create_rpls_table',1),(25,'2026_05_12_000001_create_schools_and_classes_for_bk_demo',1),(26,'2026_05_12_000002_add_case_category_to_consultation_requests',1),(27,'2026_05_12_000003_create_monthly_journals_and_service_feedback_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monthly_journals`
--

DROP TABLE IF EXISTS `monthly_journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monthly_journals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_services` int unsigned NOT NULL DEFAULT '0',
  `group_services` int unsigned NOT NULL DEFAULT '0',
  `classical_services` int unsigned NOT NULL DEFAULT '0',
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `evaluation` text COLLATE utf8mb4_unicode_ci,
  `follow_up` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `monthly_journals_teacher_id_month_year_unique` (`teacher_id`,`month`,`year`),
  CONSTRAINT `monthly_journals_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monthly_journals`
--

LOCK TABLES `monthly_journals` WRITE;
/*!40000 ALTER TABLE `monthly_journals` DISABLE KEYS */;
INSERT INTO `monthly_journals` VALUES (1,3,5,2026,'Jurnal Layanan BK Bulan Ini',8,3,4,'Layanan bulan ini berfokus pada manajemen belajar, komunikasi sosial, dan kesiapan karier siswa.','Sebagian besar siswa mampu mengikuti layanan dengan aktif, namun beberapa siswa masih perlu pendampingan individual.','Menjadwalkan sesi lanjutan untuk siswa prioritas dan memperkuat layanan kelompok komunikasi asertif.','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `monthly_journals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_categories`
--

DROP TABLE IF EXISTS `post_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_categories_name_unique` (`name`),
  UNIQUE KEY `post_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_categories`
--

LOCK TABLES `post_categories` WRITE;
/*!40000 ALTER TABLE `post_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rpls`
--

DROP TABLE IF EXISTS `rpls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_date` date DEFAULT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `materi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metode` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `evaluasi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rpls_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `rpls_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rpls`
--

LOCK TABLES `rpls` WRITE;
/*!40000 ALTER TABLE `rpls` DISABLE KEYS */;
INSERT INTO `rpls` VALUES (1,3,'RPL Individu Manajemen Waktu Belajar','individu','2026-05-25','Andi Siswa','Siswa mampu mengenali hambatan manajemen waktu dan menyusun jadwal belajar realistis.','Prioritas kegiatan, teknik membuat jadwal, dan evaluasi kebiasaan belajar.','Konseling individu, refleksi terarah, dan penyusunan rencana aksi.','Siswa menunjukkan jadwal belajar mingguan dan merefleksikan pelaksanaannya pada pertemuan berikutnya.','2026-05-18 03:30:20','2026-05-18 03:30:20'),(2,3,'RPL Kelompok Komunikasi Asertif','kelompok','2026-06-01','Kelompok siswa kelas bimbingan karier','Siswa mampu menyampaikan pendapat secara jelas, sopan, dan menghargai orang lain.','Konsep komunikasi asertif, contoh kalimat asertif, dan latihan respons sosial.','Bimbingan kelompok, diskusi, permainan peran, dan umpan balik.','Guru BK mengamati partisipasi siswa dan meminta refleksi singkat setelah kegiatan.','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `rpls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `npsn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_npsn_unique` (`npsn`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'SMA Negeri 1 Contoh','20260001','Jl. Pendidikan No. 10','2026-05-18 03:30:17','2026-05-18 03:30:17');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sekolahs`
--

DROP TABLE IF EXISTS `sekolahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sekolahs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paket_aktif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_aktivasi` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sekolahs_nama_unique` (`nama`),
  KEY `sekolahs_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sekolahs`
--

LOCK TABLES `sekolahs` WRITE;
/*!40000 ALTER TABLE `sekolahs` DISABLE KEYS */;
INSERT INTO `sekolahs` VALUES (1,'SMA Negeri 1 Contoh','Basic','2026-05-18',1,'2026-05-18 03:30:17','2026-05-18 03:30:17');
/*!40000 ALTER TABLE `sekolahs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_feedback`
--

DROP TABLE IF EXISTS `service_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_feedback` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `consultation_request_id` bigint unsigned DEFAULT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggestion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_feedback_student_id_foreign` (`student_id`),
  KEY `service_feedback_consultation_request_id_foreign` (`consultation_request_id`),
  CONSTRAINT `service_feedback_consultation_request_id_foreign` FOREIGN KEY (`consultation_request_id`) REFERENCES `consultation_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_feedback_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_feedback`
--

LOCK TABLES `service_feedback` WRITE;
/*!40000 ALTER TABLE `service_feedback` DISABLE KEYS */;
INSERT INTO `service_feedback` VALUES (1,4,2,'Konseling Individu',5,'Saya merasa lebih terbantu menyusun langkah belajar dan lebih tenang setelah konseling.','Sesi lanjutan bisa dibuat lebih sering saat mendekati ujian.','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `service_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('CKjEQQ7xSEUFpzfdBj0XNcjn2ciJVzPPNHtaaCor',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0','eyJfdG9rZW4iOiJxQ1dWODcxNTBhc3JXcE1QVzFudzdPOEJsc1RhYmUzR0pjZGR2dWhIIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2d1cnVcL3N0dWRlbnRzIiwicm91dGUiOiJndXJ1LnN0dWRlbnRzLmluZGV4In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjozfQ==',1779100931);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sociometry_responses`
--

DROP TABLE IF EXISTS `sociometry_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sociometry_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `chosen_student_id` bigint unsigned NOT NULL,
  `relation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sociometry_responses_student_id_foreign` (`student_id`),
  KEY `sociometry_responses_chosen_student_id_foreign` (`chosen_student_id`),
  CONSTRAINT `sociometry_responses_chosen_student_id_foreign` FOREIGN KEY (`chosen_student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sociometry_responses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sociometry_responses`
--

LOCK TABLES `sociometry_responses` WRITE;
/*!40000 ALTER TABLE `sociometry_responses` DISABLE KEYS */;
INSERT INTO `sociometry_responses` VALUES (1,4,6,'teman_dekat','Sering berdiskusi dan saling membantu saat belajar.','2026-05-18 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20'),(2,4,7,'teman_belajar','Cocok untuk kerja kelompok.','2026-05-18 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20'),(3,6,4,'teman_dekat','Mudah diajak komunikasi.','2026-05-18 03:30:20','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `sociometry_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `kelas_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nisn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `status_biodata` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_lengkap',
  `school` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_nisn_unique` (`nisn`),
  UNIQUE KEY `students_user_id_unique` (`user_id`),
  KEY `students_kelas_id_foreign` (`kelas_id`),
  KEY `students_status_biodata_index` (`status_biodata`),
  CONSTRAINT `students_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,4,1,'Andi Siswa','0061234567','2008-05-14','L',NULL,'lengkap','SMA Negeri 1 Contoh','2026-05-18 03:30:19','2026-05-18 03:30:19'),(2,6,1,'Alya Putri','0061234568','2008-06-20','P',NULL,'lengkap','SMA Negeri 1 Contoh','2026-05-18 03:30:19','2026-05-18 03:30:19'),(3,7,1,'Bima Pratama','0061234569','2008-08-02','L',NULL,'lengkap','SMA Negeri 1 Contoh','2026-05-18 03:30:19','2026-05-18 03:30:19'),(4,8,1,'Citra Lestari','0061234570','2008-09-11','P',NULL,'lengkap','SMA Negeri 1 Contoh','2026-05-18 03:30:19','2026-05-18 03:30:19'),(5,9,1,'Dimas Arya','0061234571','2008-10-21','L',NULL,'lengkap','SMA Negeri 1 Contoh','2026-05-18 03:30:20','2026-05-18 03:30:20');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `class_id` bigint unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disetujui',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_status_index` (`status`),
  KEY `users_school_id_foreign` (`school_id`),
  KEY `users_class_id_foreign` (`class_id`),
  CONSTRAINT `users_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin BK','admin@bk.test','SMA Negeri 1 Contoh',1,NULL,'2026-05-18 03:30:18','$2y$12$P.vbFoXdPg7hs7sa0kFH2OSSel8brp.BFQD7NnPnxcAnWN5qMoDX.','admin','disetujui',NULL,'2026-05-18 03:30:18','2026-05-18 03:30:18'),(2,'yola','yola@gmail.com','SMA Negeri 1 Contoh',1,NULL,'2026-05-18 03:30:18','$2y$12$m7pAr0YRVI0ShcAd4ipNFefnqGBci1HvZA8FBVGyanDYpaYRAl1sy','admin','disetujui',NULL,'2026-05-18 03:30:18','2026-05-18 03:30:18'),(3,'Ibu Rina Guru BK','guru@bk.test','SMA Negeri 1 Contoh',1,NULL,'2026-05-18 03:30:18','$2y$12$uDxrIX2Z7PL.94bjVoGhhuq.LvyfoXCQot72U5GD6IM4Y.eYdsSW6','guru','disetujui',NULL,'2026-05-18 03:30:18','2026-05-18 03:30:18'),(4,'Andi Siswa','siswa@bk.test','SMA Negeri 1 Contoh',1,1,'2026-05-18 03:30:18','$2y$12$WzXexkCWy9EB087s9j5EZO08nx81q3ybGao3M8wuN.sWsU3PAsHmG','siswa','disetujui',NULL,'2026-05-18 03:30:18','2026-05-18 03:30:18'),(5,'Pak Dimas Guru Pending','guru.pending@bk.test','SMA Negeri 1 Contoh',1,NULL,'2026-05-18 03:30:19','$2y$12$sP/kM7NxXM/WlqfhzrLU9eGpm28yLYlJRsjq6Oorv5TUOzJLv5Xu.','guru','pending',NULL,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(6,'Alya Putri','alya@bk.test','SMA Negeri 1 Contoh',1,1,'2026-05-18 03:30:19','$2y$12$Mk8junosVcsxLsfyESaiDeZGRpeJf2ngPNVMoYT.FHW9kT5yrhte6','siswa','disetujui',NULL,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(7,'Bima Pratama','bima@bk.test','SMA Negeri 1 Contoh',1,1,'2026-05-18 03:30:19','$2y$12$qqlep3rddEi3kwV0AKi0BOqI7gpPg8AImx65cthTvBNuohvvR4ESm','siswa','disetujui',NULL,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(8,'Citra Lestari','citra@bk.test','SMA Negeri 1 Contoh',1,2,'2026-05-18 03:30:19','$2y$12$pjUNn0cWwWMzT0qPmsPdsOcDXc6xdvdxUDodn8DyiWBK5ONX5/0Zi','siswa','disetujui',NULL,'2026-05-18 03:30:19','2026-05-18 03:30:19'),(9,'Dimas Arya','dimas@bk.test','SMA Negeri 1 Contoh',1,2,'2026-05-18 03:30:20','$2y$12$JH6V7c.tsklNNPpaQY6nFOr7bVpLvVLWVlf4o84sWSJaiQUj6ZSDC','siswa','disetujui',NULL,'2026-05-18 03:30:20','2026-05-18 03:30:20'),(10,'Shaylee Hills','wyman.lemke@example.org',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','siswa','disetujui','sfdEwcSU7j','2026-05-18 03:30:21','2026-05-18 03:30:21'),(11,'Maida Gleason Jr.','jadon43@example.com',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','siswa','disetujui','FwwL7bECQp','2026-05-18 03:30:21','2026-05-18 03:30:21'),(12,'Dr. Sterling Ruecker','krajcik.soledad@example.org',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','guru','disetujui','95CSQgJ9NF','2026-05-18 03:30:21','2026-05-18 03:30:21'),(13,'Crawford McDermott MD','jonathon.ullrich@example.net',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','siswa','disetujui','imgxMQeXTD','2026-05-18 03:30:21','2026-05-18 03:30:21'),(14,'Cassandre Schaefer','guadalupe.terry@example.net',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','siswa','disetujui','V4kAHi1CbV','2026-05-18 03:30:21','2026-05-18 03:30:21'),(15,'Jaquelin Stokes','vkovacek@example.com',NULL,NULL,NULL,'2026-05-18 03:30:21','$2y$12$Qc.q5d2PKHzUiLjR2PyG3ODnDoDPUizhLbHGaJTkABkaBnpfnzjby','guru','disetujui','OlnZeHlAg4','2026-05-18 03:30:21','2026-05-18 03:30:21');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-18 17:44:39
