-- MySQL dump 10.13  Distrib 8.0.33, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: melodex
-- ------------------------------------------------------
-- Server version	8.0.33

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `CategoryID` int NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(255) NOT NULL,
  `ParentCategoryID` int DEFAULT NULL,
  PRIMARY KEY (`CategoryID`),
  KEY `ParentCategoryID` (`ParentCategoryID`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`ParentCategoryID`) REFERENCES `categories` (`CategoryID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Test Category',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `OrderItemID` int NOT NULL AUTO_INCREMENT,
  `OrderID` int NOT NULL,
  `ProductID` int NOT NULL,
  `Quantity` int NOT NULL,
  `Price` float NOT NULL,
  PRIMARY KEY (`OrderItemID`),
  KEY `OrderID` (`OrderID`),
  KEY `ProductID` (`ProductID`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,8,2,1,19.99),(2,9,2,1,19.99),(3,10,2,1,19.99),(4,11,2,1,19.99);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `OrderID` int NOT NULL AUTO_INCREMENT,
  `UserID` int DEFAULT NULL,
  `OrderDate` date NOT NULL,
  `TotalAmount` float NOT NULL,
  PRIMARY KEY (`OrderID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,6,'2025-05-24',39.98),(2,6,'2025-05-24',39.98),(3,6,'2025-05-24',39.98),(4,20,'2025-06-04',19.99),(5,21,'2025-06-04',19.99),(6,23,'2025-06-04',19.99),(7,24,'2025-06-04',19.99),(8,25,'2025-06-04',19.99),(9,26,'2025-06-04',19.99),(10,27,'2025-06-04',19.99),(11,28,'2025-06-04',19.99),(12,29,'2025-06-04',19.99);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `ProductID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Rating` float DEFAULT '0',
  `Price` float NOT NULL,
  `Brand` varchar(255) DEFAULT NULL,
  `Description` text,
  `Stock` int NOT NULL DEFAULT '0',
  `CategoryID` int DEFAULT NULL,
  `ImageURL` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ProductID`),
  KEY `CategoryID` (`CategoryID`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,'Test Product 809',0,19.99,'TestBrand','This is a test product',48,1,'https://example.com/image.jpg'),(3,'Test Product 235',0,19.99,'TestBrand','This is a test product',50,1,'https://example.com/image.jpg'),(4,'Test Product 803',0,19.99,'TestBrand','This is a test product',50,1,'https://example.com/image.jpg'),(5,'Test Product 655',0,19.99,'TestBrand','This is a test product',50,1,'https://example.com/image.jpg'),(6,'Test Product 294',0,19.99,'TestBrand','This is a test product',50,1,'https://example.com/image.jpg'),(7,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(8,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(9,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(10,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(11,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(12,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(13,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(14,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(15,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg'),(16,'Test Product',0,99.99,'Test Brand','Test Description',10,1,'test.jpg');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratings` (
  `RatingID` int NOT NULL AUTO_INCREMENT,
  `UserID` int DEFAULT NULL,
  `ProductID` int DEFAULT NULL,
  `RatingValue` int DEFAULT NULL,
  `RatingDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`RatingID`),
  KEY `UserID` (`UserID`),
  KEY `ProductID` (`ProductID`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE,
  CONSTRAINT `ratings_chk_1` CHECK ((`RatingValue` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
INSERT INTO `ratings` VALUES (1,27,2,5,'2025-06-04 00:00:00'),(2,28,2,5,'2025-06-04 20:08:29'),(3,29,2,5,'2025-06-04 20:10:27');
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `DateOfJoin` date NOT NULL DEFAULT (curdate()),
  `Password` varchar(255) NOT NULL,
  `Role` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'Zvonkp','mail@mail.com',NULL,NULL,NULL,'2025-05-23','pass1234','user'),(6,'Test User','test@example.com',NULL,NULL,NULL,'2025-05-23','$2y$10$azO/QrMeHkCRsgcTY0OKX.A///FxBBweUOq75ir4NMO5UQjlnako6','user'),(7,'Test User','test2@example.com','1990-01-01','123 Test Street, Test City','1234567890','2025-06-04','$2y$10$69e8FIFBT1Cv/JDbxPvdUeGbdjWlxwRQbGpeTANRg/.1lcGy0ijhi','user'),(8,'API Test User','apitest_1749057284@melodex.test','1995-06-15','456 Test Avenue','9876543210','2025-06-04','$2y$10$t5sjd8QKkZJSwzEhw4OQi.T4g7Y.IS3isBUKwuAEQIm1nGZWW0ClS','user'),(9,'API Test User','apitest_1749057360@melodex.test','1995-06-15','456 Test Avenue','9876543210','2025-06-04','$2y$10$lelW/y7gBP2a0PXDeK1r/OZejGgBqBaOc9dupr7YVdIYnUer/INNu','user'),(10,'API Test User','apitest_1749057522@melodex.test','1995-06-15','456 Test Avenue','9876543210','2025-06-04','$2y$10$FCWEEZv8aJzyhjGI/uPoZuDNxsdphjaHOKYlNpMD5P4/ny9WIuhom','user'),(11,'Test User','test_1749058165@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$mKQO8Uv433n2Rb6C1F0/A.7T9AMSeCdDTrbVPqsUgjORwXrtFXoWa','user'),(12,'Test User','test_1749058258@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$79dwn8LCU.riwsvsnPJRuuP9t0E3ptPXQ8htqMfsgwAbe4n3dW1Ia','user'),(13,'Test User','test_1749058307@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$W7cyjVXe0Sp3Pg1E69XTqeL19Jf2YDHA.KB2oTLbwyEs0VcWENdVO','user'),(14,'Test User','test_1749058488@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$WRIp/.Gh88sFK9qgCcmYnOWXye5az.RmTMnsA/DvlATWBEnmlXi4e','user'),(15,'Test User','test_1749058701@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$HUV4w1dUVrCTuCc2TCKDNeoohDlPRn1.m8giZ.GUvwJodobmFaQxO','user'),(16,'Test User','test_1749058932@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$xBcjSg0Y/9wkYUxdKwjrhuRz3LExvWSERwkJpkv1B.wOz/RKMHSyq','user'),(17,'Test User','test_1749059009@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$1p3QQU9KFsJUcpZCPOZPpOu3SHwlHt3Ve.3cRpEep0GoLiJmVLq4q','user'),(18,'Test User','test_1749059089@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$ReGyS71RDTSpwERQWT0AQu948N73cTG/sWKWib1vJuSnH8uW3kPyK','user'),(19,'Test User','test_1749059199@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$lNtY7lSjOGdP56KV/mVgmOxNRQBBJk.VWZtPiasC9EQ9pg2y.IC42','user'),(20,'Test User','test_1749059288@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$5UKbechR.lvZy9LXbY.mROL5Q.fQNDoikBWzMRlXYdVHZDv1eefZu','user'),(21,'Test User','test_1749059342@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$fi8b/LJVrVFbNGq5Kts9Gu7RG3xfaWwAx0Mrq8WRhJ30oXTWrcb/6','user'),(22,'Test User','test_1749059405@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$hlkFXzN7vBB3YZs6dduyDOXAtUdaNlwUMCTZQB/YmUYe1y5VKUrlW','user'),(23,'Test User','test_1749059451@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$lTpEDGM5q05T2c2YS2Nizu0HxRG4gDEYcqqL6jLiq5TK5WibbF8a.','user'),(24,'Test User','test_1749059820@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$AC4jO7oy7Z0/pAq4jOeSsulK.yCDfKbMGR9t/p2Dx8zUl/Kezjig.','user'),(25,'Test User','test_1749060101@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$5G07jRn0UyaPEJzCJFZjC.xYwN8IcDIxumdBFjLOJLdEaQ4Msb1hy','user'),(26,'Test User','test_1749060144@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$4nbxZ322agV9dmFCvSCX5Omi5pWQqgOc83yPDEKSEv7Txs05jq5/O','user'),(27,'Test User','test_1749060261@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$UBK.KzlZSNZqd7zc.xj87Ok.wcBzy/RU/1PAKpw1cDMO9eGWGRqr6','user'),(28,'Test User','test_1749060508@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$qG1YdzkMcPYld9mq5sbXfOITiTLbhVO8Hiwf16cFJXd5ZhH5szHKm','user'),(29,'Test User','test_1749060627@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$UoaTLCid1BNOWLYJ/ywp8eYp2u6SvL.NeTajoow8oNb.3V.vZsac6','user'),(30,'Test User','test_1749061626@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$T1ANVeXUNkuBS09O9wHVdOCAe0M6jfz2QdpGDiDd3ClDqyRvYl9K2','user'),(31,'Test User','test_1749061680@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$/8ss9kVcLnSuShAKLoQKv.gxqWNwhNv/pKs4WUG/DMnI03FSC52F.','user'),(32,'Test User','test_1749061770@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$5zHx24aLrFJ3y.2CnMVShu35AweEftFT8PdKG0lkjMzGhsGESGko.','user'),(33,'Test User','test_1749061881@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$PUw3Z5H7dXDL558lPDOsc.Vr1mhRZlEWoqMPAuR/UnLuzTP7RqU2y','user'),(34,'Test User','test_1749062035@example.com','1990-01-01','123 Test St','1234567890','2025-06-04','$2y$10$Sz7ffdeln9bSG7KJu5RNFeUtENaLEHcSzRgzwezoIfIeWIy9g3QTy','user');
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

-- Dump completed on 2025-06-06 15:22:08
