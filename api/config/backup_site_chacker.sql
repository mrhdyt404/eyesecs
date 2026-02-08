/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: site_chacker
-- ------------------------------------------------------
-- Server version	10.5.29-MariaDB-0+deb11u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$HASH_YANG_BARU','2026-01-16 03:42:58');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(64) NOT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `type` enum('guest','admin') DEFAULT 'guest',
  `status` enum('active','inactive') DEFAULT 'active',
  `rate_limit` int(11) DEFAULT 10,
  `expired_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (1,'ADMIN2y10AIzaSyCnx2AEXK9mF5F1qHjhCDhwuXJpWCwUonMKEY','admin','admin','active',NULL,NULL,'2026-01-16 03:43:09'),(65,'GUEST_fa88f178a584832e324e6fd72d6c9f4c',NULL,'guest','active',10,'2026-01-17 01:25:54','2026-01-16 17:25:54'),(66,'GUEST_405c6e1f63f538eb768acea1ed0a7aee',NULL,'guest','active',10,'2026-01-17 12:09:45','2026-01-17 04:09:45'),(67,'GUEST_5c14690bfd017924c1eca098e492f9b6',NULL,'guest','active',10,'2026-01-17 17:08:12','2026-01-17 09:08:12'),(68,'GUEST_75bcb14891ea97de826999a6682512c5',NULL,'guest','active',10,'2026-01-17 17:08:23','2026-01-17 09:08:23'),(69,'GUEST_abaa972f179223b2a519dacd89aa76cb',NULL,'guest','active',10,'2026-01-17 17:11:52','2026-01-17 09:11:52'),(70,'GUEST_1b7d808a83e98c4823daa664d2d287c8',NULL,'guest','active',10,'2026-01-17 19:30:12','2026-01-17 11:30:12'),(71,'GUEST_e6312e18d8093dd7642851da9536fdec',NULL,'guest','active',10,'2026-01-17 22:00:24','2026-01-17 14:00:24'),(72,'GUEST_ed137dad6f439e078dbee44fffddeaf9',NULL,'guest','active',10,'2026-01-17 22:19:57','2026-01-17 14:19:57'),(73,'GUEST_f95368b2fa174f3af7f61ec46fafda9a',NULL,'guest','active',10,'2026-01-18 22:52:45','2026-01-18 14:52:45');
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key_id` int(11) DEFAULT NULL,
  `url_id` int(11) DEFAULT NULL,
  `client_ip` varchar(45) DEFAULT NULL,
  `method` text DEFAULT NULL,
  `endpoint` text DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `api_key_id` (`api_key_id`),
  KEY `url_id` (`url_id`),
  CONSTRAINT `api_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`),
  CONSTRAINT `api_logs_ibfk_2` FOREIGN KEY (`url_id`) REFERENCES `urls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1451 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_logs`
--

LOCK TABLES `api_logs` WRITE;
/*!40000 ALTER TABLE `api_logs` DISABLE KEYS */;
INSERT INTO `api_logs` VALUES (1379,65,458,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-16 17:46:54'),(1380,65,459,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-16 17:47:26'),(1381,65,460,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-16 17:54:12'),(1382,65,461,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:00:55'),(1383,65,462,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:24'),(1384,65,463,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:28'),(1385,65,464,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:31'),(1386,65,465,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:33'),(1387,65,466,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:35'),(1388,65,467,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:01:37'),(1389,65,468,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:02'),(1390,65,469,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:03'),(1391,65,470,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:04'),(1392,65,471,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:04'),(1393,65,472,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:05'),(1394,65,473,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:05'),(1395,65,474,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:06'),(1396,65,475,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:06'),(1397,65,476,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:07'),(1398,65,477,'210.79.191.138','POST','/project_akhir_sop/api/v1/url/check','curl/7.74.0','2026-01-17 04:02:07'),(1399,66,478,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 04:10:06'),(1400,66,479,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 05:00:50'),(1401,66,480,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 05:04:24'),(1402,66,481,'125.165.101.249','POST','/project_akhir_sop/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 05:10:04'),(1403,66,482,'210.79.191.138','POST','/api/v1/url/check','curl/7.74.0','2026-01-17 05:32:07'),(1404,66,483,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 05:37:12'),(1405,66,484,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 05:41:27'),(1406,66,485,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 09:04:08'),(1407,66,486,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 09:08:08'),(1408,66,487,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 09:11:48'),(1409,66,488,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 09:12:02'),(1410,70,489,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:36'),(1411,70,490,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:37'),(1412,70,491,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:37'),(1413,70,492,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:38'),(1414,70,493,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:39'),(1415,70,494,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:39'),(1416,70,495,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:40'),(1417,70,496,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:40'),(1418,70,497,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:40'),(1419,70,498,'210.79.191.138','POST','//api/v1/url/check','curl/7.74.0','2026-01-17 11:30:41'),(1420,70,499,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:31:09'),(1421,70,500,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:03'),(1422,70,501,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:06'),(1423,70,502,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:09'),(1424,70,503,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:11'),(1425,70,504,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:13'),(1426,70,505,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:16'),(1427,70,506,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:19'),(1428,70,507,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:20'),(1429,70,508,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 11:43:23'),(1430,70,509,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36','2026-01-17 12:33:10'),(1431,70,510,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:00:19'),(1432,70,511,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:01:09'),(1433,70,512,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:03:26'),(1434,70,513,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:05:00'),(1435,70,514,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:11:50'),(1436,72,515,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:42:25'),(1437,72,516,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 14:48:36'),(1438,72,517,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-17 15:07:10'),(1439,72,518,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36','2026-01-17 17:21:43'),(1440,72,519,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36','2026-01-17 17:22:16'),(1441,72,520,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:52:24'),(1442,72,521,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:53:25'),(1443,72,522,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:53:46'),(1444,72,523,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:53:51'),(1445,72,524,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:55:17'),(1446,72,525,'125.165.110.166','POST','/api/v1/url/check','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36','2026-01-18 06:55:29'),(1447,72,526,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-18 14:08:54'),(1448,72,527,'125.165.101.249',NULL,NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-18 14:50:34'),(1449,72,528,'125.165.101.249',NULL,NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-18 14:50:58'),(1450,72,529,'125.165.101.249','POST','/api/v1/url/check','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0','2026-01-18 14:53:20');
/*!40000 ALTER TABLE `api_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urls`
--

DROP TABLE IF EXISTS `urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `risk_score` decimal(5,2) DEFAULT NULL,
  `is_phishing` tinyint(1) DEFAULT NULL,
  `ssl_valid` tinyint(1) DEFAULT NULL,
  `redirect_count` int(11) DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=530 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urls`
--

LOCK TABLES `urls` WRITE;
/*!40000 ALTER TABLE `urls` DISABLE KEYS */;
INSERT INTO `urls` VALUES (458,'https://example.com','example.com',0.00,0,1,0,'2026-01-16 17:46:54'),(459,'https://example.com','example.com',0.00,0,1,0,'2026-01-16 17:47:26'),(460,'https://example.com','example.com',0.00,0,1,0,'2026-01-16 17:54:12'),(461,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:00:55'),(462,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:23'),(463,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:28'),(464,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:31'),(465,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:33'),(466,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:35'),(467,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:01:37'),(468,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:02'),(469,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:02'),(470,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:04'),(471,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:04'),(472,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:05'),(473,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:05'),(474,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:06'),(475,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:06'),(476,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:07'),(477,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 04:02:07'),(478,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 04:10:06'),(479,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 05:00:50'),(480,'https://6heh.instamixsnapbridgeapp.com/?qr=cp&zqs=ce3f366a67562b3fdbc3533c904ea44f','6heh.instamixsnapbridgeapp.com',0.00,0,1,0,'2026-01-17 05:04:24'),(481,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 05:10:04'),(482,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 05:32:07'),(483,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 05:37:12'),(484,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 05:41:27'),(485,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 09:04:08'),(486,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 09:08:08'),(487,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 09:11:48'),(488,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 09:12:02'),(489,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:36'),(490,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:36'),(491,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:37'),(492,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:38'),(493,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:39'),(494,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:39'),(495,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:39'),(496,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:40'),(497,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:40'),(498,'https://google.com','google.com',0.00,0,1,0,'2026-01-17 11:30:41'),(499,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:31:09'),(500,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:03'),(501,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:06'),(502,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:09'),(503,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:11'),(504,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:13'),(505,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:16'),(506,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:19'),(507,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:20'),(508,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 11:43:23'),(509,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 12:33:09'),(510,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 14:00:17'),(511,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 14:01:09'),(512,'https://phantom-stem-another-birds.trycloudflare.com','phantom-stem-another-birds.trycloudflare.com',0.00,0,1,0,'2026-01-17 14:03:26'),(513,'https://phantom-stem-another-birds.trycloudflare.com','phantom-stem-another-birds.trycloudflare.com',0.00,0,1,0,'2026-01-17 14:05:00'),(514,'https://example.com','example.com',0.00,0,1,0,'2026-01-17 14:11:50'),(515,'https://youtube.com','youtube.com',0.00,0,1,0,'2026-01-17 14:42:25'),(516,'http://www.ikenmijnkunst.nl/index.php/exposities/exposities-2006','www.ikenmijnkunst.nl',0.10,0,0,0,'2026-01-17 14:48:36'),(517,'https://phantom-stem-another-birds.trycloudflare.com','phantom-stem-another-birds.trycloudflare.com',0.00,0,1,0,'2026-01-17 15:07:09'),(518,'https://youtube.com','youtube.com',0.00,0,1,0,'2026-01-17 17:21:43'),(519,'https://get-youtube-subsrice.com','get-youtube-subsrice.com',0.00,0,1,0,'2026-01-17 17:22:16'),(520,'https://usti.ac.id','usti.ac.id',0.00,0,1,0,'2026-01-18 06:52:24'),(521,'https://Dimas.com','Dimas.com',0.00,0,1,0,'2026-01-18 06:53:25'),(522,'https://kontolmamakkau.com','kontolmamakkau.com',0.00,0,1,0,'2026-01-18 06:53:46'),(523,'https://kontolmamakvdksokwkkau.com','kontolmamakvdksokwkkau.com',0.00,0,1,0,'2026-01-18 06:53:51'),(524,'https://192.168.0.2','192.168.0.2',0.00,0,1,0,'2026-01-18 06:55:17'),(525,'http://192.168.0.2','192.168.0.2',0.10,0,0,0,'2026-01-18 06:55:29'),(526,'https://eyesecs.site/api/v1/url/check','eyesecs.site',0.00,0,1,0,'2026-01-18 14:08:54'),(527,'https://eyesecs.site/api/v1/url/check','eyesecs.site',0.00,0,1,0,'2026-01-18 14:50:34'),(528,'https://dimas.com','dimas.com',0.00,0,1,0,'2026-01-18 14:50:57'),(529,'https://eyesecs.site/api/v1/url/check','eyesecs.site',0.00,0,1,0,'2026-01-18 14:53:20');
/*!40000 ALTER TABLE `urls` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-18 22:11:07
