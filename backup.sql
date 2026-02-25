-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: project_management
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
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
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `epics`
--

DROP TABLE IF EXISTS `epics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `epics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `epics_name_index` (`name`),
  KEY `idx_epics_project` (`project_id`),
  CONSTRAINT `epics_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `epics`
--

LOCK TABLES `epics` WRITE;
/*!40000 ALTER TABLE `epics` DISABLE KEYS */;
INSERT INTO `epics` VALUES (1,4,'SS AICG','<p></p>',NULL,NULL,0,'2026-02-03 11:43:27','2026-02-03 11:43:27'),(2,7,'Nâng cấp Giao diện Website 2.0','<p>Nâng cấp Giao diện Website 2.0</p>',NULL,NULL,0,'2026-02-06 14:23:30','2026-02-06 14:23:30');
/*!40000 ALTER TABLE `epics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_access`
--

DROP TABLE IF EXISTS `external_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_access` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `migration_generated` tinyint(1) NOT NULL DEFAULT '0',
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_access_access_token_unique` (`access_token`),
  KEY `external_access_project_id_foreign` (`project_id`),
  CONSTRAINT `external_access_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_access`
--

LOCK TABLES `external_access` WRITE;
/*!40000 ALTER TABLE `external_access` DISABLE KEYS */;
INSERT INTO `external_access` VALUES (1,1,'sKTI8EGNhj64MI6SmpP3GvbQTcu4juBb','gqC5XbJ7',1,0,NULL,'2026-02-06 16:01:08','2026-02-06 16:01:08');
/*!40000 ALTER TABLE `external_access` ENABLE KEYS */;
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
  `attempts` tinyint unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_03_02_200055_create_projects_table',1),(5,'2025_03_02_200109_create_project_members_table',1),(6,'2025_03_02_200213_create_ticket_statuses_table',1),(7,'2025_03_02_200246_create_tickets_table',1),(8,'2025_03_13_154334_add_uuid_to_ticket_table',1),(9,'2025_03_13_223706_create_permission_tables',1),(10,'2025_03_27_065113_create_epics_table',1),(11,'2025_03_28_144500_create_ticket_histories_table',1),(12,'2025_04_11_173545_create_ticket_comments_table',1),(13,'2025_05_06_220233_add_sort_order_to_ticket_statuses_table',1),(14,'2025_05_06_221002_add_sort_color_to_ticket_statuses_table',1),(15,'2025_05_10_202453_add_start_date_end_date_to_project',1),(16,'2025_06_24_212547_add_ticket_users_table_and_created_by_column',1),(17,'2025_06_24_212750_migrate_existing_user_id_data',1),(18,'2025_06_24_212838_drop_user_id_column_from_tickets',1),(19,'2025_06_29_052227_change_tickets_description_to_longtext',1),(20,'2025_07_04_164429_create_ticket_priorities_table',1),(21,'2025_07_04_164558_add_priority_id_to_tickets_table',1),(22,'2025_07_16_182905_add_pinned_to_projects_table',1),(23,'2025_07_30_211411_create_project_notes_table',1),(24,'2025_08_05_030102_create_external_access_table',1),(25,'2025_08_05_031001_generate_client_access_for_existing_projects_safe',1),(26,'2025_08_08_051806_create_notifications_table',1),(27,'2025_08_17_041650_add_start_date_to_tickets_table',1),(28,'2025_08_17_041901_populate_start_date_from_created_at_in_tickets_table',1),(29,'2025_08_23_214007_add_is_completed_to_ticket_statuses_table',1),(30,'2025_08_25_174118_add_google_id_to_users_table',1),(31,'2025_09_16_051002_add_performance_indexes',1),(32,'2025_10_17_215303_add_sort_order_to_epics_table',1),(33,'2025_11_04_181918_add_color_to_projects_table',1),(34,'2025_11_06_052000_create_settings_table',1),(35,'2025_11_08_063526_add_user_id_to_settings_table',1),(36,'2026_01_07_224849_add_performance_indexes',1),(37,'2026_01_08_115323_add_infrastructure_indexes_v2',1),(38,'2026_01_12_110044_add_is_pinned_to_projects_table',1),(39,'2026_01_23_005635_create_project_health_checks_table',1),(40,'2026_01_23_124317_add_missing_performance_indexes_v3',1),(41,'2026_01_30_004511_add_ai_analysis_columns_to_projects_table',1),(42,'2026_01_30_015540_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',2),(3,'App\\Models\\User',3),(3,'App\\Models\\User',4),(3,'App\\Models\\User',5),(3,'App\\Models\\User',6),(3,'App\\Models\\User',7),(4,'App\\Models\\User',9),(4,'App\\Models\\User',10);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,3,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 4, \"comment_id\": 1, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(2,4,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 4, \"comment_id\": 1, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(3,3,'comment_added','New Comment on Ticket','Bob (Backend Lead) added a comment on ticket ','{\"ticket_id\": 4, \"comment_id\": 2, \"commenter_id\": 4, \"commenter_name\": \"Bob (Backend Lead)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(4,5,'comment_added','New Comment on Ticket','Bob (Backend Lead) added a comment on ticket ','{\"ticket_id\": 4, \"comment_id\": 2, \"commenter_id\": 4, \"commenter_name\": \"Bob (Backend Lead)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(5,3,'comment_added','New Comment on Ticket','David (QA) added a comment on ticket ','{\"ticket_id\": 5, \"comment_id\": 3, \"commenter_id\": 6, \"commenter_name\": \"David (QA)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(6,5,'comment_added','New Comment on Ticket','David (QA) added a comment on ticket ','{\"ticket_id\": 5, \"comment_id\": 3, \"commenter_id\": 6, \"commenter_name\": \"David (QA)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(7,3,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 5, \"comment_id\": 4, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(8,6,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 5, \"comment_id\": 4, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(9,6,'comment_added','New Comment on Ticket','Alice (PM) added a comment on ticket ','{\"ticket_id\": 8, \"comment_id\": 5, \"commenter_id\": 3, \"commenter_name\": \"Alice (PM)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(10,5,'comment_added','New Comment on Ticket','Alice (PM) added a comment on ticket ','{\"ticket_id\": 8, \"comment_id\": 5, \"commenter_id\": 3, \"commenter_name\": \"Alice (PM)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(11,6,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 8, \"comment_id\": 6, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(12,3,'comment_added','New Comment on Ticket','Charlie (Frontend) added a comment on ticket ','{\"ticket_id\": 8, \"comment_id\": 6, \"commenter_id\": 5, \"commenter_name\": \"Charlie (Frontend)\"}',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(13,1,'system_demo','? Kiểm tra Thông báo','Xin chào! Đây là thông báo thử nghiệm.','{\"ticket_id\": 1}',NULL,'2026-02-06 11:13:54','2026-02-06 11:13:54'),(14,1,'system_demo','? Kiểm tra Thông báo','Xin chào! Đây là thông báo thử nghiệm.','{\"ticket_id\": 1}',NULL,'2026-02-06 11:14:13','2026-02-06 11:14:13'),(15,1,'system_demo','? Kiểm tra Thông báo (DB)','Thông báo tạo trực tiếp từ Database Facade.','{\"ticket_id\": 1}',NULL,'2026-02-06 11:14:30','2026-02-06 11:14:30'),(16,10,'project_assigned','Added to Project','You have been added to project \'Dự án Demo Health Check\' by Super Admin','{\"project_id\": 7, \"project_name\": \"Dự án Demo Health Check\", \"assigned_by_id\": 1, \"assigned_by_name\": \"Super Admin\"}',NULL,'2026-02-06 14:32:18','2026-02-06 14:32:18'),(17,10,'project_assigned','Added to Project','You have been added to project \'Dự án Demo Health Check\' by Super Admin','{\"project_id\": 7, \"project_name\": \"Dự án Demo Health Check\", \"assigned_by_id\": 1, \"assigned_by_name\": \"Super Admin\"}',NULL,'2026-02-06 14:32:20','2026-02-06 14:32:20');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
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
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_project','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(2,'view_any_project','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(3,'create_project','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(4,'update_project','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(5,'delete_project','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(6,'view_ticket','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(7,'view_any_ticket','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(8,'create_ticket','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(9,'update_ticket','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(10,'delete_ticket','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(11,'view_ticket_priority','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(12,'view_any_ticket_priority','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(13,'create_ticket_priority','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(14,'update_ticket_priority','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(15,'delete_ticket_priority','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(16,'view_ticket_comment','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(17,'view_any_ticket_comment','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(18,'create_ticket_comment','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(19,'update_ticket_comment','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(20,'delete_ticket_comment','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(21,'view_notification','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(22,'view_any_notification','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(23,'create_notification','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(24,'update_notification','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(25,'delete_notification','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(26,'view_user','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(27,'view_any_user','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(28,'create_user','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(29,'update_user','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(30,'delete_user','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(31,'view_any_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(32,'view_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(33,'create_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(34,'update_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(35,'delete_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(36,'restore_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(37,'force_delete_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(38,'force_delete_any_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(39,'restore_any_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(40,'replicate_epic','web','2026-02-06 14:36:34','2026-02-06 14:36:34'),(41,'reorder_epic','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(42,'restore_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(43,'force_delete_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(44,'force_delete_any_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(45,'restore_any_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(46,'replicate_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(47,'reorder_notification','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(48,'restore_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(49,'force_delete_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(50,'force_delete_any_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(51,'restore_any_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(52,'replicate_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(53,'reorder_project','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(54,'view_any_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(55,'view_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(56,'create_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(57,'update_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(58,'delete_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(59,'restore_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(60,'force_delete_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(61,'force_delete_any_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(62,'restore_any_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(63,'replicate_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(64,'reorder_role','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(65,'view_any_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(66,'view_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(67,'create_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(68,'update_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(69,'delete_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(70,'restore_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(71,'force_delete_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(72,'force_delete_any_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(73,'restore_any_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(74,'replicate_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(75,'reorder_ticket::comment','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(76,'view_any_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(77,'view_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(78,'create_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(79,'update_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(80,'delete_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(81,'restore_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(82,'force_delete_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(83,'force_delete_any_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(84,'restore_any_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(85,'replicate_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(86,'reorder_ticket::priority','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(87,'restore_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(88,'force_delete_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(89,'force_delete_any_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(90,'restore_any_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(91,'replicate_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(92,'reorder_ticket','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(93,'restore_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(94,'force_delete_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(95,'force_delete_any_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(96,'restore_any_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(97,'replicate_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(98,'reorder_user','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(99,'page_EpicsOverview','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(100,'page_Leaderboard','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(101,'page_ProjectBoard','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(102,'page_ProjectTimeline','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(103,'page_SystemSettings','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(104,'page_TicketTimeline','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(105,'page_UserContributions','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(106,'widget_StatsOverview','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(107,'widget_TicketsPerProjectChart','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(108,'widget_UserStatisticsChart','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(109,'widget_MonthlyTicketTrendChart','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(110,'widget_ProjectTimeline','web','2026-02-06 14:36:35','2026-02-06 14:36:35'),(111,'widget_RecentActivityTable','web','2026-02-06 14:36:35','2026-02-06 14:36:35');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'Test','bff7ac2508d0ffcdadc289c5663b63e545e4c6cb05a4d06e48fee59876c4d2f1','[\"*\"]',NULL,NULL,'2026-02-03 14:58:55','2026-02-03 14:58:55');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_health_checks`
--

DROP TABLE IF EXISTS `project_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_health_checks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_health_checks`
--

LOCK TABLES `project_health_checks` WRITE;
/*!40000 ALTER TABLE `project_health_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_health_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_members_project_id_user_id_unique` (`project_id`,`user_id`),
  KEY `idx_project_members_project_user` (`project_id`,`user_id`),
  KEY `idx_project_members_user` (`user_id`),
  KEY `idx_project_members_project` (`project_id`),
  CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_members`
--

LOCK TABLES `project_members` WRITE;
/*!40000 ALTER TABLE `project_members` DISABLE KEYS */;
INSERT INTO `project_members` VALUES (1,1,3,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(2,1,4,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(3,1,5,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(4,1,6,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(5,1,7,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(6,2,3,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(7,2,4,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(8,2,5,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(9,2,6,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(10,2,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(11,3,3,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(12,3,4,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(13,3,5,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(14,3,6,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(15,3,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(16,4,3,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(17,4,4,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(18,4,5,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(19,4,6,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(20,4,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(23,7,1,'2026-02-06 11:44:17','2026-02-06 11:44:17'),(24,7,10,'2026-02-06 14:32:18','2026-02-06 14:32:18');
/*!40000 ALTER TABLE `project_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_notes`
--

DROP TABLE IF EXISTS `project_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_notes_created_by_foreign` (`created_by`),
  KEY `project_notes_project_id_note_date_index` (`project_id`,`note_date`),
  CONSTRAINT `project_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_notes_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_notes`
--

LOCK TABLES `project_notes` WRITE;
/*!40000 ALTER TABLE `project_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ticket_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `pinned_date` timestamp NULL DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ai_analysis` longtext COLLATE utf8mb4_unicode_ci,
  `ai_analysis_at` datetime DEFAULT NULL,
  `ai_analysis_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'idle',
  PRIMARY KEY (`id`),
  KEY `idx_projects_pinned` (`pinned_date`),
  KEY `idx_projects_dates` (`start_date`,`end_date`),
  KEY `projects_name_index` (`name`),
  KEY `projects_ticket_prefix_index` (`ticket_prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'SuperApp Mobile Banking 2025','Revamp toàn bộ ứng dụng Mobile Banking với giao diện mới và tích hợp AI.','SAM',NULL,'2025-01-01','2025-06-30','2026-02-03 13:06:48',1,'2026-02-03 06:08:38','2026-02-06 11:10:00','Dưới đây là báo cáo sức khỏe dự án \'SuperApp Mobile Banking 2025\':\n\n**Báo cáo Sức khỏe Dự án: SuperApp Mobile Banking 2025**\n\n**Đánh giá chung:**\nDự án đang ở trạng thái \"At Risk\" nghiêm trọng. Với tiến độ chỉ đạt 30% và vận tốc cực kỳ thấp (0.93 ticket/ngày), dự kiến hoàn thành đã bị đẩy lùi tới 2026-02-14, chậm hơn đáng kể so với mục tiêu ban đầu. Tình hình này cho thấy dự án đang gặp phải những trở ngại lớn về hiệu suất và có nguy cơ trật bánh khỏi lộ trình đã định.\n\n**Rủi ro chính:**\nRủi ro lớn nhất nằm ở vận tốc dự án kém hiệu quả và sự tồn đọng của các vé ưu tiên cao. Có 4 vé quá hạn, trong đó 2 vé Critical và 2 vé High chưa hoàn thành. Bên cạnh đó, Charlie (Frontend) và Bob (Backend Lead) đang bị quá tải với 3 vé mỗi người. Đặc biệt, việc Bob - trưởng nhóm backend - quá tải có thể trở thành nút thắt cổ chai, ảnh hưởng đến tiến độ chung của đội backend.\n\n**Hành động đề xuất:**\n1.  **Ưu tiên giải quyết vé Critical & High:** Ngay lập tức tổ chức \"swarming\" để tháo gỡ các trở ngại và hoàn thành 2 vé Critical cùng 2 vé High priority.\n2.  **Tái phân bổ nguồn lực:** Đánh giá lại tải trọng công việc của Charlie và Bob. Phân công lại hoặc hỗ trợ họ để giảm tải, đặc biệt là Bob để anh có thể tập trung vào vai trò lãnh đạo kỹ thuật.\n3.  **Phân tích nguyên nhân gốc rễ (Root Cause Analysis):** Triển khai một buổi Retrospective chuyên sâu để xác định lý do vận tốc thấp và các nút thắt cổ chai trong quy trình, từ đó đề xuất các cải tiến bền vững.','2026-02-06 18:10:00','completed'),(2,'E-commerce Platform','Hệ thống bán hàng trực tuyến đa kênh.','ECOMM',NULL,'2026-01-11','2027-01-03','2026-02-03 13:06:48',1,'2026-02-03 06:08:39','2026-02-03 13:06:48',NULL,NULL,'idle'),(3,'HR Management System','Quản lý nhân sự, chấm công, tính lương.','HRM',NULL,'2026-01-20','2026-12-03','2026-02-03 13:06:45',1,'2026-02-03 06:08:39','2026-02-06 11:09:13','Kính gửi Ban Quản lý Dự án,\n\nĐây là báo cáo sức khỏe dự án \'HR Management System\' dựa trên dữ liệu hiện có:\n\n**Đánh giá chung:**\nMặc dù trạng thái tổng quan là \'Good\' và không có vé quá hạn hay bị lãng quên, nhận định này có vẻ lạc quan quá mức. Tiến độ dự án đang rất chậm (12.5%) với vận tốc chỉ 0.31 ticket/ngày, dẫn đến thời gian hoàn thành dự kiến là 2026-03-01, cho thấy nguy cơ chậm trễ nghiêm trọng. Đặc biệt đáng lo ngại là sự tồn tại của 4 Critical tickets chưa được giải quyết, mâu thuẫn trực tiếp với trạng thái \'Good\' của dự án.\n\n**Rủi ro chính:**\nRủi ro lớn nhất là **khả năng hoàn thành dự án đúng thời hạn và phạm vi**. Vận tốc hiện tại quá thấp sẽ khiến dự án kéo dài, gây lãng phí nguồn lực và mất cơ hội. Sự tồn tại của 4 Critical tickets cho thấy có những nút thắt hoặc vấn đề nghiêm trọng đang cản trở tiến độ. Việc Eve (Designer) và Alice (PM) đang \"ôm\" nhiều việc cũng có thể là dấu hiệu của việc phân bổ công việc không hiệu quả hoặc thiếu nguồn lực ở các khâu quan trọng.\n\n**Hành động đề xuất:**\n1.  **Ngay lập tức tổ chức buổi điều tra sâu (Deep Dive) về 4 Critical tickets** để xác định nguyên nhân tắc nghẽn, gỡ bỏ các rào cản và đẩy nhanh việc hoàn thành chúng.\n2.  **Thực hiện một buổi Retrospective tập trung vào phân tích vận tốc thấp** (0.31 ticket/ngày) và các rào cản quy trình hiện tại để tìm giải pháp cải thiện hiệu suất của đội.\n3.  **Rà soát lại toàn bộ backlog và phân bổ ưu tiên** để đảm bảo đội đang tập trung vào các hạng mục có giá trị cao nhất, đồng thời làm rõ vai trò và tối ưu hóa nguồn lực của các thành viên, đặc biệt là Eve (Designer) và Alice (PM).','2026-02-06 18:09:13','completed'),(4,'AI Content Generator','Tool tạo nội dung marketing tự động bằng AI.','AICG',NULL,'2026-01-12','2026-08-03','2026-02-03 13:06:48',1,'2026-02-03 06:08:39','2026-02-06 10:37:55','Chào đội ngũ,\n\nDưới đây là phân tích sức khỏe dự án \'AI Content Generator\':\n\n**1. Đánh giá chung:**\nTình hình dự án hiện tại nhìn chung \"Good\" với tiến độ 35% và vận tốc 2.2 ticket/ngày. Tuy nhiên, dự báo hoàn thành vào 2026-02-12 có vẻ xa vời so với tiến độ hiện tại, và chúng ta đang đối mặt với những dấu hiệu cảnh báo rõ rệt về ưu tiên và phân bổ nguồn lực, cần được giải quyết ngay để đảm bảo chất lượng và tiến độ.\n\n**2. Rủi ro chính:**\nRủi ro lớn nhất là sự tồn đọng của 4 vé Critical và 1 vé High đang chưa được xử lý, cùng với 2 vé quá hạn. Điều này cho thấy có sự lệch pha trong quản lý ưu tiên hoặc tắc nghẽn trong quy trình giải quyết các vấn đề quan trọng. Đặc biệt, Eve (Designer) và David (QA) đang ôm nhiều việc, gây ra điểm nghẽn tiềm ẩn trong luồng công việc thiết kế và kiểm thử.\n\n**3. Hành động đề xuất:**\n1.  **Ưu tiên giải quyết khẩn cấp:** Tổ chức họp nhanh để đánh giá và giải quyết ngay 4 vé Critical và 1 vé High. Đảm bảo toàn đội tập trung vào các vấn đề này.\n2.  **Tối ưu hóa phân bổ công việc:** Rà soát lại tải công việc của Eve (Designer) và David (QA) để giảm tải, xem xét hỗ trợ chéo hoặc tái phân bổ các vé để tránh tắc nghẽn.\n3.  **Cải thiện quy trình:** Phân tích nguyên nhân 2 vé quá hạn và tăng cường kỷ luật trong ước lượng/cam kết sprint cũng như quy trình Daily Scrum để đảm bảo các blockers được gỡ bỏ kịp thời.','2026-02-06 17:37:55','completed'),(7,'Dự án Demo Health Check','<p></p>','DEMO','#3e1259','2026-02-01','2026-02-28','2026-02-06 11:44:13',0,'2026-02-06 11:44:17','2026-02-06 12:59:33','Báo cáo sức khỏe dự án: Dự án Demo Health Check\n\n**Đánh giá chung:**\nDự án \"Demo Health Check\" đang ở trạng thái cực kỳ đáng báo động (\'At Risk\') với tiến độ 0% và vận tốc 0 ticket/ngày, cho thấy dự án chưa hề khởi động hoặc đã đình trệ hoàn toàn. Các dấu hiệu như 2 vé quá hạn, 1 vé bị lãng quên, và nút thắt cổ chai ở trạng thái \'To Do\' (mất trung bình 1.22 giờ) càng khẳng định tình hình nghiêm trọng này. Rõ ràng, không có công việc nào đang được thực hiện.\n\n**Rủi ro chính:**\nRủi ro chính yếu là sự tắc nghẽn toàn diện trong quy trình thực thi và thiếu trách nhiệm giải trình. Việc không có vé nào được tiến hành, cùng với các vé bị bỏ quên và quá hạn, cho thấy một sự đổ vỡ cơ bản trong việc bắt đầu và theo dõi công việc. Vai trò \'Super Admin\' đang ôm 3 vé trong khi dự án đình trệ cũng đặt ra câu hỏi về phân bổ nguồn lực và khả năng thực hiện.\n\n**Hành động đề xuất:**\n1.  **Họp khẩn cấp & Phân tích nguyên nhân:** Tổ chức ngay một buổi họp với đội ngũ để xác định nguyên nhân gốc rễ của việc đình trệ hoàn toàn (0% tiến độ, 0 vận tốc).\n2.  **Quản lý & Ưu tiên hóa công việc:** Rà soát và cập nhật trạng thái các vé quá hạn, bị lãng quên. Lập kế hoạch hành động cụ thể để xử lý các vé ưu tiên cao (High) ngay lập tức.\n3.  **Đánh giá lại nguồn lực & Phân công:** Làm rõ vai trò và khả năng thực hiện của \'Super Admin\' đối với 3 vé đang giữ. Đảm bảo mọi vé đều có chủ sở hữu rõ ràng và có thể bắt đầu được tiến hành.','2026-02-06 19:59:33','completed');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(23,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(1,3),(2,3),(6,3),(7,3),(9,3),(11,3),(12,3),(16,3),(17,3),(21,3),(22,3),(1,4),(2,4),(6,4),(7,4),(8,4),(9,4),(21,4),(22,4),(55,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(2,'admin','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(3,'member','web','2026-02-03 06:08:37','2026-02-03 06:08:37'),(4,'Intern','web','2026-02-06 14:29:11','2026-02-06 14:29:11');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_user_id_key_unique` (`user_id`,`key`),
  KEY `settings_group_index` (`group`),
  CONSTRAINT `settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_comments_ticket` (`ticket_id`),
  KEY `idx_ticket_comments_user` (`user_id`),
  CONSTRAINT `ticket_comments_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_comments`
--

LOCK TABLES `ticket_comments` WRITE;
/*!40000 ALTER TABLE `ticket_comments` DISABLE KEYS */;
INSERT INTO `ticket_comments` VALUES (1,4,5,'API này trả về lỗi 500 khi số dư không đủ, check lại nhé.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(2,4,4,'Đã fix, trả về 400 Bad Request rồi.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(3,5,6,'Nhập số tiền âm vẫn submit được form.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(4,5,5,'Oops, để fix validation.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(5,8,3,'Cái này gấp nhé, ảnh hưởng khách hàng VIP.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(6,8,5,'Đang điều tra, nghi do thư viện animation cũ.','2026-02-03 06:08:38','2026-02-03 06:08:38'),(7,57,1,'<p>làm nhanh lên</p><p></p>','2026-02-06 13:00:49','2026-02-06 13:00:49');
/*!40000 ALTER TABLE `ticket_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_histories`
--

DROP TABLE IF EXISTS `ticket_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `ticket_status_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_histories_ticket` (`ticket_id`),
  KEY `idx_ticket_histories_user` (`user_id`),
  KEY `idx_ticket_histories_status` (`ticket_status_id`),
  CONSTRAINT `ticket_histories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_histories_ticket_status_id_foreign` FOREIGN KEY (`ticket_status_id`) REFERENCES `ticket_statuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_histories`
--

LOCK TABLES `ticket_histories` WRITE;
/*!40000 ALTER TABLE `ticket_histories` DISABLE KEYS */;
INSERT INTO `ticket_histories` VALUES (1,1,3,6,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(2,2,3,6,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(3,3,4,6,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(4,4,3,4,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(5,5,3,5,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(6,6,3,2,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(7,7,7,1,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(8,8,6,3,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(9,9,6,2,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(10,10,3,1,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(11,11,3,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(12,12,6,8,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(13,13,6,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(14,14,7,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(15,15,5,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(16,16,5,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(17,17,6,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(18,18,3,8,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(19,19,3,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(20,20,3,8,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(21,21,4,7,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(22,22,7,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(23,23,6,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(24,24,6,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(25,25,4,9,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(26,26,3,11,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(27,27,6,12,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(28,28,4,10,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(29,29,7,10,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(30,30,4,11,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(31,31,3,11,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(32,32,3,10,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(33,33,4,11,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(34,34,3,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(35,35,4,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(36,36,6,14,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(37,37,7,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(38,38,3,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(39,39,3,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(40,40,6,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(41,41,6,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(42,42,7,14,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(43,43,4,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(44,44,3,14,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(45,45,3,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(46,46,7,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(47,47,4,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(48,48,4,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(49,49,5,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(50,50,6,13,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(51,51,7,14,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(52,52,5,15,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(53,53,7,14,'2026-02-03 06:08:39','2026-02-03 06:08:39'),(54,55,1,28,'2026-02-06 11:46:25','2026-02-06 11:46:25'),(55,55,1,27,'2026-02-06 11:46:28','2026-02-06 11:46:28'),(56,58,1,28,'2026-02-06 14:24:55','2026-02-06 14:24:55');
/*!40000 ALTER TABLE `ticket_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_priorities`
--

DROP TABLE IF EXISTS `ticket_priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_priorities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_priorities_name_unique` (`name`),
  KEY `idx_ticket_priorities_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_priorities`
--

LOCK TABLES `ticket_priorities` WRITE;
/*!40000 ALTER TABLE `ticket_priorities` DISABLE KEYS */;
INSERT INTO `ticket_priorities` VALUES (1,'Low','#10B981','2026-02-03 06:08:34','2026-02-03 06:08:34'),(2,'Medium','#F59E0B','2026-02-03 06:08:34','2026-02-03 06:08:34'),(3,'High','#EF4444','2026-02-03 06:08:34','2026-02-03 06:08:34'),(4,'Critical','danger','2026-02-03 06:08:38','2026-02-03 06:08:38');
/*!40000 ALTER TABLE `ticket_priorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_statuses`
--

DROP TABLE IF EXISTS `ticket_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3490dc',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ticket_statuses_project_sort` (`project_id`,`sort_order`),
  KEY `idx_ticket_statuses_project_completed` (`project_id`,`is_completed`),
  KEY `idx_ticket_statuses_project` (`project_id`),
  CONSTRAINT `ticket_statuses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_statuses`
--

LOCK TABLES `ticket_statuses` WRITE;
/*!40000 ALTER TABLE `ticket_statuses` DISABLE KEYS */;
INSERT INTO `ticket_statuses` VALUES (1,1,'Backlog','2026-02-03 06:08:38','2026-02-03 06:08:38',1,'gray',0),(2,1,'To Do','2026-02-03 06:08:38','2026-02-03 06:08:38',2,'info',0),(3,1,'In Progress','2026-02-03 06:08:38','2026-02-03 06:08:38',3,'primary',0),(4,1,'Code Review','2026-02-03 06:08:38','2026-02-03 06:08:38',4,'warning',0),(5,1,'Testing (QA)','2026-02-03 06:08:38','2026-02-03 06:08:38',5,'danger',0),(6,1,'Done','2026-02-03 06:08:38','2026-02-03 06:08:38',6,'success',1),(7,2,'To Do','2026-02-03 06:08:39','2026-02-03 06:08:39',1,'gray',0),(8,2,'In Progress','2026-02-03 06:08:39','2026-02-03 06:08:39',2,'primary',0),(9,2,'Done','2026-02-03 06:08:39','2026-02-03 06:08:39',3,'success',1),(10,3,'To Do','2026-02-03 06:08:39','2026-02-03 06:08:39',1,'gray',0),(11,3,'In Progress','2026-02-03 06:08:39','2026-02-03 06:08:39',2,'primary',0),(12,3,'Done','2026-02-03 06:08:39','2026-02-03 06:08:39',3,'success',1),(13,4,'To Do','2026-02-03 06:08:39','2026-02-03 06:08:39',1,'gray',0),(14,4,'In Progress','2026-02-03 06:08:39','2026-02-03 06:08:39',2,'primary',0),(15,4,'Done','2026-02-03 06:08:39','2026-02-03 06:08:39',3,'success',1),(26,7,'Backlog','2026-02-06 11:44:17','2026-02-06 11:44:17',0,'#6B7280',0),(27,7,'To Do','2026-02-06 11:44:17','2026-02-06 11:44:17',1,'#F59E0B',0),(28,7,'In Progress','2026-02-06 11:44:17','2026-02-06 11:44:17',2,'#3B82F6',0),(29,7,'Review','2026-02-06 11:44:17','2026-02-06 11:44:17',3,'#8B5CF6',0),(30,7,'Done','2026-02-06 11:44:17','2026-02-06 11:44:17',4,'#10B981',1);
/*!40000 ALTER TABLE `ticket_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_users`
--

DROP TABLE IF EXISTS `ticket_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_users_ticket_id_user_id_unique` (`ticket_id`,`user_id`),
  KEY `idx_ticket_users_ticket_user` (`ticket_id`,`user_id`),
  KEY `idx_ticket_users_user` (`user_id`),
  KEY `idx_ticket_users_ticket` (`ticket_id`),
  CONSTRAINT `ticket_users_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_users`
--

LOCK TABLES `ticket_users` WRITE;
/*!40000 ALTER TABLE `ticket_users` DISABLE KEYS */;
INSERT INTO `ticket_users` VALUES (1,1,7,NULL,NULL),(2,2,4,NULL,NULL),(3,3,5,NULL,NULL),(4,4,4,NULL,NULL),(5,5,5,NULL,NULL),(6,6,4,NULL,NULL),(7,7,5,NULL,NULL),(8,8,5,NULL,NULL),(9,9,7,NULL,NULL),(10,10,4,NULL,NULL),(11,11,3,NULL,NULL),(12,12,3,NULL,NULL),(13,13,7,NULL,NULL),(14,14,3,NULL,NULL),(15,15,3,NULL,NULL),(16,16,3,NULL,NULL),(17,17,6,NULL,NULL),(18,18,4,NULL,NULL),(19,19,5,NULL,NULL),(20,20,5,NULL,NULL),(21,21,5,NULL,NULL),(22,22,3,NULL,NULL),(23,23,7,NULL,NULL),(24,24,5,NULL,NULL),(25,25,4,NULL,NULL),(26,26,7,NULL,NULL),(27,27,3,NULL,NULL),(28,28,3,NULL,NULL),(29,29,5,NULL,NULL),(30,30,4,NULL,NULL),(31,31,3,NULL,NULL),(32,32,7,NULL,NULL),(33,33,7,NULL,NULL),(34,34,3,NULL,NULL),(35,35,6,NULL,NULL),(36,36,7,NULL,NULL),(37,37,7,NULL,NULL),(38,38,4,NULL,NULL),(39,39,6,NULL,NULL),(40,40,3,NULL,NULL),(41,41,6,NULL,NULL),(42,42,5,NULL,NULL),(43,43,4,NULL,NULL),(44,44,7,NULL,NULL),(45,45,3,NULL,NULL),(46,46,4,NULL,NULL),(47,47,3,NULL,NULL),(48,48,7,NULL,NULL),(49,49,7,NULL,NULL),(50,50,3,NULL,NULL),(51,51,6,NULL,NULL),(52,52,4,NULL,NULL),(53,53,5,NULL,NULL),(54,55,1,NULL,NULL),(55,56,1,NULL,NULL),(56,57,1,NULL,NULL),(57,58,1,NULL,NULL);
/*!40000 ALTER TABLE `ticket_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` bigint unsigned NOT NULL,
  `ticket_status_id` bigint unsigned NOT NULL,
  `priority_id` bigint unsigned DEFAULT NULL,
  `epic_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_uuid_unique` (`uuid`),
  KEY `idx_tickets_project_status` (`project_id`,`ticket_status_id`),
  KEY `idx_tickets_status_created` (`ticket_status_id`,`created_at`),
  KEY `idx_tickets_project_created` (`project_id`,`created_at`),
  KEY `idx_tickets_project_updated` (`project_id`,`updated_at`),
  KEY `idx_tickets_due_date` (`due_date`),
  KEY `idx_tickets_priority` (`priority_id`),
  KEY `idx_tickets_created_by` (`created_by`),
  KEY `tickets_name_index` (`name`),
  KEY `tickets_created_at_index` (`created_at`),
  KEY `idx_tickets_epic` (`epic_id`),
  KEY `idx_tickets_project` (`project_id`),
  KEY `idx_tickets_status` (`ticket_status_id`),
  KEY `idx_tickets_creator` (`created_by`),
  CONSTRAINT `tickets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_epic_id_foreign` FOREIGN KEY (`epic_id`) REFERENCES `epics` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_priority_id_foreign` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_ticket_status_id_foreign` FOREIGN KEY (`ticket_status_id`) REFERENCES `ticket_statuses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,'SAM-YOVXCB',1,6,2,NULL,'Thiết kế màn hình Login','Hoàn thiện UI/UX cho màn hình đăng nhập, hỗ trợ Dark Mode.',NULL,'2026-02-09','2026-02-03 06:08:38','2026-02-03 06:08:38',3),(2,'SAM-YPCK8Q',1,6,3,NULL,'API Login & Register','Implement JWT Auth, Rate Limiting.',NULL,'2026-02-12','2026-02-03 06:08:38','2026-02-03 06:08:38',3),(3,'SAM-ZLP0B2',1,6,3,NULL,'Frontend Login Integration','Ghép API Login vào UI.',NULL,'2026-02-08','2026-02-03 06:08:38','2026-02-03 06:08:38',4),(4,'SAM-MW2W3K',1,4,4,NULL,'API Chuyển khoản nội bộ','Xử lý logic chuyển tiền, rollback nếu lỗi DB.',NULL,'2026-02-05','2026-02-03 06:08:38','2026-02-03 06:08:38',3),(5,'SAM-DMBHOY',1,5,3,NULL,'Màn hình Chuyển khoản','Form nhập số tài khoản, xác thực tên người nhận tự động.',NULL,'2026-02-07','2026-02-03 06:08:38','2026-02-03 06:08:38',3),(6,'SAM-W1JHKM',1,2,3,NULL,'Tích hợp cổng VNPay','Đang chờ tài khoản Sandbox từ đối tác.',NULL,'2026-02-08','2026-02-03 06:08:38','2026-02-03 06:08:38',3),(7,'SAM-Z8QXAB',1,1,2,NULL,'UI Scan QR Code','Tính năng quét QR thanh toán.',NULL,'2026-02-10','2026-02-03 06:08:38','2026-02-03 06:08:38',7),(8,'SAM-TYJTUN',1,3,4,NULL,'[BUG] App crash trên iOS 15','Khách hàng báo cáo app bị văng khi mở màn hình Lịch sử giao dịch.',NULL,'2026-02-02','2026-02-03 06:08:38','2026-02-03 06:08:38',6),(9,'SAM-BXGFSO',1,2,1,NULL,'[BUG] Sai font chữ tiếng Việt','Một số chỗ bị lỗi hiển thị font.',NULL,'2026-02-06','2026-02-03 06:08:38','2026-02-03 06:08:38',6),(10,'SAM-P6PKF4',1,1,2,NULL,'AI Phân tích chi tiêu','Gợi ý tiết kiệm dựa trên lịch sử mua sắm.',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(11,'ECOMM-IO7RGJ',2,7,3,NULL,'Ticket mẫu #0 cho ECOMM','Mô tả tự động cho ticket 0...',NULL,'2026-02-11','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(12,'ECOMM-R5SBAC',2,8,1,NULL,'Ticket mẫu #1 cho ECOMM','Mô tả tự động cho ticket 1...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(13,'ECOMM-TMTRBO',2,9,3,NULL,'Ticket mẫu #2 cho ECOMM','Mô tả tự động cho ticket 2...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(14,'ECOMM-5EM9OS',2,7,4,NULL,'Ticket mẫu #3 cho ECOMM','Mô tả tự động cho ticket 3...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(15,'ECOMM-90NYBJ',2,7,4,NULL,'Ticket mẫu #4 cho ECOMM','Mô tả tự động cho ticket 4...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',5),(16,'ECOMM-JX6W1F',2,7,2,NULL,'Ticket mẫu #5 cho ECOMM','Mô tả tự động cho ticket 5...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',5),(17,'ECOMM-XXBDFA',2,9,2,NULL,'Ticket mẫu #6 cho ECOMM','Mô tả tự động cho ticket 6...',NULL,'2026-02-10','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(18,'ECOMM-9KITOU',2,8,1,NULL,'Ticket mẫu #7 cho ECOMM','Mô tả tự động cho ticket 7...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(19,'ECOMM-JBXQRU',2,7,2,NULL,'Ticket mẫu #8 cho ECOMM','Mô tả tự động cho ticket 8...',NULL,'2026-02-13','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(20,'ECOMM-QVWQPP',2,8,2,NULL,'Ticket mẫu #9 cho ECOMM','Mô tả tự động cho ticket 9...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(21,'ECOMM-XQ0BNW',2,7,4,NULL,'Ticket mẫu #10 cho ECOMM','Mô tả tự động cho ticket 10...',NULL,'2026-02-12','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(22,'ECOMM-ATKM88',2,9,4,NULL,'Ticket mẫu #11 cho ECOMM','Mô tả tự động cho ticket 11...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(23,'ECOMM-BYMILQ',2,9,2,NULL,'Ticket mẫu #12 cho ECOMM','Mô tả tự động cho ticket 12...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(24,'ECOMM-ETMZUS',2,9,2,NULL,'Ticket mẫu #13 cho ECOMM','Mô tả tự động cho ticket 13...',NULL,'2026-02-10','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(25,'ECOMM-F7NWVJ',2,9,1,NULL,'Ticket mẫu #14 cho ECOMM','Mô tả tự động cho ticket 14...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(26,'HRM-LSXYYT',3,11,4,NULL,'Ticket mẫu #0 cho HRM','Mô tả tự động cho ticket 0...',NULL,'2026-02-11','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(27,'HRM-3PKKGB',3,12,3,NULL,'Ticket mẫu #1 cho HRM','Mô tả tự động cho ticket 1...',NULL,'2026-02-12','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(28,'HRM-MMDXAM',3,10,2,NULL,'Ticket mẫu #2 cho HRM','Mô tả tự động cho ticket 2...',NULL,'2026-02-13','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(29,'HRM-HGQZ5L',3,10,3,NULL,'Ticket mẫu #3 cho HRM','Mô tả tự động cho ticket 3...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(30,'HRM-XMRSR3',3,11,4,NULL,'Ticket mẫu #4 cho HRM','Mô tả tự động cho ticket 4...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(31,'HRM-MF4CZ0',3,11,3,NULL,'Ticket mẫu #5 cho HRM','Mô tả tự động cho ticket 5...',NULL,'2026-02-13','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(32,'HRM-SRABO5',3,10,4,NULL,'Ticket mẫu #6 cho HRM','Mô tả tự động cho ticket 6...',NULL,'2026-02-09','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(33,'HRM-E9XPLD',3,11,4,NULL,'Ticket mẫu #7 cho HRM','Mô tả tự động cho ticket 7...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(34,'AICG-FYB6EK',4,13,1,NULL,'Ticket mẫu #0 cho AICG','Mô tả tự động cho ticket 0...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(35,'AICG-C9QMLY',4,15,1,NULL,'Ticket mẫu #1 cho AICG','Mô tả tự động cho ticket 1...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(36,'AICG-3BQUQH',4,14,1,NULL,'Ticket mẫu #2 cho AICG','Mô tả tự động cho ticket 2...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(37,'AICG-XUMFAV',4,13,1,NULL,'Ticket mẫu #3 cho AICG','Mô tả tự động cho ticket 3...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(38,'AICG-RIGIUO',4,13,2,NULL,'Ticket mẫu #4 cho AICG','Mô tả tự động cho ticket 4...',NULL,'2026-02-09','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(39,'AICG-BGF1JJ',4,13,1,NULL,'Ticket mẫu #5 cho AICG','Mô tả tự động cho ticket 5...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(40,'AICG-SWZXFF',4,13,2,NULL,'Ticket mẫu #6 cho AICG','Mô tả tự động cho ticket 6...',NULL,'2026-02-11','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(41,'AICG-5LZSEX',4,13,4,NULL,'Ticket mẫu #7 cho AICG','Mô tả tự động cho ticket 7...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(42,'AICG-TC05AK',4,14,1,NULL,'Ticket mẫu #8 cho AICG','Mô tả tự động cho ticket 8...',NULL,'2026-02-10','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(43,'AICG-3UFV3H',4,15,3,NULL,'Ticket mẫu #9 cho AICG','Mô tả tự động cho ticket 9...',NULL,'2026-02-13','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(44,'AICG-WCCOAT',4,14,4,NULL,'Ticket mẫu #10 cho AICG','Mô tả tự động cho ticket 10...',NULL,'2026-02-11','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(45,'AICG-GXQJ5S',4,15,4,NULL,'Ticket mẫu #11 cho AICG','Mô tả tự động cho ticket 11...',NULL,'2026-02-11','2026-02-03 06:08:39','2026-02-03 06:08:39',3),(46,'AICG-ZSJTQ4',4,15,4,NULL,'Ticket mẫu #12 cho AICG','Mô tả tự động cho ticket 12...',NULL,'2026-02-12','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(47,'AICG-WEUXLH',4,15,2,NULL,'Ticket mẫu #13 cho AICG','Mô tả tự động cho ticket 13...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(48,'AICG-T6RMJU',4,13,3,NULL,'Ticket mẫu #14 cho AICG','Mô tả tự động cho ticket 14...',NULL,'2026-02-06','2026-02-03 06:08:39','2026-02-03 06:08:39',4),(49,'AICG-HDXUFE',4,15,3,NULL,'Ticket mẫu #15 cho AICG','Mô tả tự động cho ticket 15...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',5),(50,'AICG-Q2SNUB',4,13,4,NULL,'Ticket mẫu #16 cho AICG','Mô tả tự động cho ticket 16...',NULL,'2026-02-07','2026-02-03 06:08:39','2026-02-03 06:08:39',6),(51,'AICG-HKNT0H',4,14,1,NULL,'Ticket mẫu #17 cho AICG','Mô tả tự động cho ticket 17...',NULL,'2026-02-08','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(52,'AICG-CP0VR9',4,15,1,NULL,'Ticket mẫu #18 cho AICG','Mô tả tự động cho ticket 18...',NULL,'2026-02-13','2026-02-03 06:08:39','2026-02-03 06:08:39',5),(53,'AICG-SEOWF3',4,14,4,NULL,'Ticket mẫu #19 cho AICG','Mô tả tự động cho ticket 19...',NULL,'2026-02-09','2026-02-03 06:08:39','2026-02-03 06:08:39',7),(55,'DEMO-EUDHUZ',7,27,1,NULL,'Thiết kế logo','<p>logo1</p>','2026-02-01','2026-02-06','2026-01-26 12:55:24','2026-01-27 12:55:24',1),(56,'DEMO-XGAQ2J',7,27,3,NULL,'Báo cáo Doanh thu','<p></p>','2026-02-01','2026-02-03','2026-02-06 12:52:26','2026-02-06 12:52:26',1),(57,'DEMO-LRDLQW',7,28,2,NULL,'Nâng cấp Server','<p></p>','2026-02-01','2026-02-08','2026-02-06 12:54:50','2026-02-06 12:54:50',1),(58,'DEMO-KDQSI9',7,28,3,2,'Thiết kế Homepage','<p></p>','2026-02-06',NULL,'2026-02-06 14:24:27','2026-02-06 14:24:55',1);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
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
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin','admin@admin.com',NULL,NULL,'$2y$12$cEr2hbcCzLQu/qhLOiPHgO3ascL/Y7/aYWFDyXkXR3rsTWueQoJyu','nPwVw0rbbRZojby7Ib1e3S1KzoOldK1pKXszQZc3qw9e5bh5lHDmjd8mV3YW','2026-02-03 06:08:37','2026-02-03 06:08:37'),(2,'Test User','test@example.com',NULL,NULL,'$2y$12$i8qbJVh2Cw2P5XMdgJDXbO/DRImMLjsZ4tMn4yhq7gYvQ4NGf/8/i',NULL,'2026-02-03 06:08:37','2026-02-03 06:08:37'),(3,'Alice (PM)','pm@company.com',NULL,NULL,'$2y$12$DR6jjRhMLjnesKbmDDdAbeUtJV9K1GysMiJvnvuTSBVT8MWa/1m0q',NULL,'2026-02-03 06:08:37','2026-02-03 06:08:37'),(4,'Bob (Backend Lead)','techlead@company.com',NULL,NULL,'$2y$12$EU89YmdAqGtBUVccD7FLNuQbWQsNQH9/oXvsqs2bw67w/mMnWQjFu',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(5,'Charlie (Frontend)','frontend@company.com',NULL,NULL,'$2y$12$K6Q6StSejstJoH5aZmKU6eSiz6KaIbgA9Bu8punovY2hu4zP5T0Ye',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(6,'David (QA)','qa@company.com',NULL,NULL,'$2y$12$F8O1nv/0/wlLvptrF16g/uO0MEglCq5hmVX94ozKeCookwQ2PbgUa',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(7,'Eve (Designer)','design@company.com',NULL,NULL,'$2y$12$YQrTCL3UKVXXrYR7VLtlP.S6wf7tXTTp5zk3H96WL2s26xHWr9kI6',NULL,'2026-02-03 06:08:38','2026-02-03 06:08:38'),(9,'Nhân Viên Test','staff@test.com',NULL,NULL,'$2y$12$Au25ZsE7FHZSs.kHAWiii.Uxzdo6YZjWrEQRBIJ2XwlWYzhpGTqtS',NULL,'2026-02-06 14:30:02','2026-02-06 14:30:02'),(10,'test1','test1@gmail.com',NULL,NULL,'$2y$12$HzqIiSApWGdo1ykWKUDHw.h1O8JfLwtPYY5ifOuF9oKfNjaC2LSBm','QXlPNTPqnDTwaAEiKRNW1FlnCn7B2FuY9uMMDfpEnZT65ZN2ZqPIGW34unAY','2026-02-06 14:32:12','2026-02-06 14:32:12');
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

-- Dump completed on 2026-02-19 16:08:51
