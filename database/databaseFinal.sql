CREATE DATABASE  IF NOT EXISTS `play_and_learn` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `play_and_learn`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: play_and_learn
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `answer_options`
--

DROP TABLE IF EXISTS `answer_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `answer_options` (
  `option_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `uq_option_question` (`question_id`,`option_id`),
  KEY `idx_options_question_order` (`question_id`,`sort_order`),
  CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=403 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answer_options`
--

LOCK TABLES `answer_options` WRITE;
/*!40000 ALTER TABLE `answer_options` DISABLE KEYS */;
INSERT INTO `answer_options` VALUES (1,1,'\"Hello, world!\"',0,1),(2,1,'print',0,2),(3,1,'Hello, world!',1,3),(4,1,'Hello world',0,4),(5,2,'//',0,1),(6,2,'/*',0,2),(7,2,'--',0,3),(8,2,'#',1,4),(9,3,'print()',1,1),(10,3,'show()',0,2),(11,3,'display()',0,3),(12,3,'say()',0,4),(13,4,'<class \'str\'>',0,1),(14,4,'<class \'float\'>',0,2),(15,4,'<class \'bool\'>',0,3),(16,4,'<class \'int\'>',1,4),(17,5,'\"hello\"',1,1),(18,5,'42',0,2),(19,5,'3.14',0,3),(20,5,'True',0,4),(21,6,'3',0,1),(22,6,'5',1,2),(23,6,'2',0,3),(24,6,'32',0,4),(25,7,'2.5',1,1),(26,7,'2',0,2),(27,7,'2.0',0,3),(28,7,'3',0,4),(29,8,'A percentage of a number',0,1),(30,8,'The remainder after dividing',1,2),(31,8,'The number multiplied by 100',0,3),(32,8,'The number raised to a power',0,4),(33,9,'20',0,1),(34,9,'24',0,2),(35,9,'14',1,3),(36,9,'9',0,4),(37,10,'cat 2',0,1),(38,10,'catcat',1,2),(39,10,'cat cat',0,3),(40,10,'catt',0,4),(41,11,'4',0,1),(42,11,'6',0,2),(43,11,'5',1,3),(44,11,'hello',0,4),(45,12,'.small()',0,1),(46,12,'.down()',0,2),(47,12,'.lowercase()',0,3),(48,12,'.lower()',1,4),(49,13,'int',0,1),(50,13,'float',0,2),(51,13,'str',1,3),(52,13,'bool',0,4),(53,14,'Hi {name}!',0,1),(54,14,'Hi name!',0,2),(55,14,'Hi \"Sam\"!',0,3),(56,14,'Hi Sam!',1,4),(57,15,'5',1,1),(58,15,'41',0,2),(59,15,'4',0,3),(60,15,'Error',0,4),(61,16,'True',0,1),(62,16,'4',0,2),(63,16,'Error',0,3),(64,16,'False',1,4),(65,17,'Else if: check another condition',1,1),(66,17,'End if',0,2),(67,17,'Else forever',0,3),(68,17,'Equal if',0,4),(69,18,'B',0,1),(70,18,'A',1,2),(71,18,'AB',0,3),(72,18,'Nothing',0,4),(73,19,'4',1,1),(74,19,'3',0,2),(75,19,'5',0,3),(76,19,'1',0,4),(77,20,'for',0,1),(78,20,'while',1,2),(79,20,'if',0,3),(80,20,'def',0,4),(81,21,'1, 2, 3',0,1),(82,21,'0, 1, 2, 3',0,2),(83,21,'0, 1, 2',1,3),(84,21,'1, 2',0,4),(85,22,'pear',0,1),(86,22,'apple',1,2),(87,22,'fig',0,3),(88,22,'0',0,4),(89,23,'2',0,1),(90,23,'1',0,2),(91,23,'3',1,3),(92,23,'Error',0,4),(93,24,'1',0,1),(94,24,'-1',0,2),(95,24,'first',0,3),(96,24,'0',1,4),(97,25,'func',0,1),(98,25,'function',0,2),(99,25,'def',1,3),(100,25,'define',0,4),(101,26,'10',0,1),(102,26,'5',0,2),(103,26,'55',0,3),(104,26,'25',1,4),(105,27,'Sends a value back to the code that called the function',1,1),(106,27,'Prints the value on the screen',0,2),(107,27,'Stops Python completely',0,3),(108,27,'Repeats the function',0,4),(109,28,'name',0,1),(110,28,'3',0,2),(111,28,'Error',0,3),(112,28,'Rex',1,4),(113,29,'The key',1,1),(114,29,'The value',0,2),(115,29,'The index',0,3),(116,29,'The function',0,4),(117,30,'1',0,1),(118,30,'2',1,2),(119,30,'85',0,3),(120,30,'Error',0,4),(121,31,'Java Debug Kit',0,1),(122,31,'Java Development Kit',1,2),(123,31,'Java Deployment Key',0,3),(124,31,'Java Dynamic Kernel',0,4),(128,32,'java',0,1),(129,32,'javadoc',0,2),(130,32,'javac',1,3),(131,32,'jar',0,4),(135,33,'.class',0,1),(136,33,'.java',1,2),(137,33,'.jar',0,3),(138,33,'.jvm',0,4),(142,34,'Writes your source code',0,1),(143,34,'Runs compiled bytecode',1,2),(144,34,'Downloads libraries',0,3),(145,34,'Draws the user interface',0,4),(149,35,'start',0,1),(150,35,'main',1,2),(151,35,'run',0,3),(152,35,'init',0,4),(156,36,'Hi !',0,1),(157,36,'!Hi',0,2),(158,36,'Hi!',1,3),(159,36,'Nothing is printed',0,4),(163,37,'With a period',0,1),(164,37,'With a semicolon',1,2),(165,37,'With a colon',0,3),(166,37,'With nothing',0,4),(170,38,'hello.txt',0,1),(171,38,'Main.java',0,2),(172,38,'Hello.java',1,3),(173,38,'Hello.class',0,4),(177,39,'String',0,1),(178,39,'boolean',0,2),(179,39,'double',0,3),(180,39,'int',1,4),(184,40,'int count = \"5\";',0,1),(185,40,'int count = 5;',1,2),(186,40,'String s = 5;',0,3),(187,40,'boolean b = yes;',0,4),(191,41,'char',0,1),(192,41,'String',1,2),(193,41,'int',0,3),(194,41,'text',0,4),(198,42,'3.5',0,1),(199,42,'3',0,2),(200,42,'3.0',1,3),(201,42,'4.0',0,4),(205,43,'3',0,1),(206,43,'1',1,2),(207,43,'0',0,3),(208,43,'3.33',0,4),(212,44,'5',0,1),(213,44,'2',0,2),(214,44,'7',1,3),(215,44,'52',0,4),(219,45,'3',1,1),(220,45,'3.5',0,2),(221,45,'4',0,3),(222,45,'2',0,4),(226,46,'true',0,1),(227,46,'false',1,2),(228,46,'1',0,3),(229,46,'An error',0,4),(233,47,'3',0,1),(234,47,'5',0,2),(235,47,'4',1,3),(236,47,'Java',0,4),(240,48,'h',0,1),(241,48,'e',1,2),(242,48,'l',0,3),(243,48,'o',0,4),(247,49,'a == b',0,1),(248,49,'a = b',0,2),(249,49,'a.equals(b)',1,3),(250,49,'a.compare(b)',0,4),(254,50,'Ja',0,1),(255,50,'av',1,2),(256,50,'ava',0,3),(257,50,'va',0,4),(261,51,'small',0,1),(262,51,'big',1,2),(263,51,'bigsmall',0,3),(264,51,'Nothing',0,4),(268,52,'then',0,1),(269,52,'otherwise',0,2),(270,52,'else',1,3),(271,52,'elif',0,4),(275,53,'odd',0,1),(276,53,'even',1,2),(277,53,'10',0,3),(278,53,'Nothing',0,4),(282,54,'or',0,1),(283,54,'not',0,2),(284,54,'and',1,3),(285,54,'equals',0,4),(289,55,'1 2 3',0,1),(290,55,'0 1 2',1,2),(291,55,'0 1 2 3',0,3),(292,55,'Nothing',0,4),(296,56,'3',0,1),(297,56,'1',0,2),(298,56,'-1',0,3),(299,56,'0',1,4),(303,57,'stop',0,1),(304,57,'break',1,2),(305,57,'exit',0,3),(306,57,'continue',0,4),(310,58,'4',0,1),(311,58,'5',1,2),(312,58,'6',0,3),(313,58,'It never stops',0,4),(317,59,'4',0,1),(318,59,'8',1,2),(319,59,'15',0,3),(320,59,'1',0,4),(324,60,'1',0,1),(325,60,'-1',0,2),(326,60,'0',1,3),(327,60,'It depends',0,4),(331,61,'null',0,1),(332,61,'0',1,2),(333,61,'1',0,3),(334,61,'An error',0,4),(338,62,'a.size()',0,1),(339,62,'a.length()',0,2),(340,62,'a.length',1,3),(341,62,'length(a)',0,4),(345,63,'23',0,1),(346,63,'5',1,2),(347,63,'6',0,3),(348,63,'Nothing',0,4),(352,64,'It returns zero',0,1),(353,64,'It returns nothing',1,2),(354,64,'It cannot be called',0,3),(355,64,'It returns text',0,4),(359,65,'parameters',1,1),(360,65,'return values',0,2),(361,65,'classes',0,3),(362,65,'loops',0,4),(366,66,'4',0,1),(367,66,'6',0,2),(368,66,'8',1,3),(369,66,'16',0,4),(373,67,'make',0,1),(374,67,'create',0,2),(375,67,'new',1,3),(376,67,'object',0,4),(380,68,'A blueprint for objects',1,1),(381,68,'A kind of loop',0,2),(382,68,'A single variable',0,3),(383,68,'A file extension',0,4),(387,69,'Dog',0,1),(388,69,'name',0,2),(389,69,'Rex',1,3),(390,69,'null',0,4),(394,70,'A loop that builds arrays',0,1),(395,70,'A method that runs when an object is created',1,2),(396,70,'A comment',0,3),(397,70,'A type of variable',0,4);
/*!40000 ALTER TABLE `answer_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'Python 101','Intro to Python: variables, loops, and functions for beginners.',1,'2026-09-29 22:35:32'),(2,'Java 101','Intro to Java: syntax, classes, and basic OOP concepts.',1,'2026-09-29 22:48:52');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback_messages`
--

DROP TABLE IF EXISTS `feedback_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `idx_feedback_student_created` (`student_id`,`created_at`),
  KEY `idx_feedback_sender` (`sender_id`),
  CONSTRAINT `fk_feedback_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_feedback_body` CHECK ((char_length(trim(`body`)) between 1 and 2000))
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback_messages`
--

LOCK TABLES `feedback_messages` WRITE;
/*!40000 ALTER TABLE `feedback_messages` DISABLE KEYS */;
INSERT INTO `feedback_messages` VALUES (1,2,1,'Good start, keep it up!','2026-11-24 23:47:17','2026-11-24 23:47:24'),(101,4,1,'Great work on the first eight Python lessons, Maria. Your quiz scores are excellent. Try the Java course next whenever you are ready.','2026-11-04 20:10:00','2026-11-05 01:22:31'),(102,4,8,'Proud of you for sticking with it this month. Keep going!','2026-11-17 02:45:00','2026-11-17 22:05:12'),(103,5,1,'Nice job getting through Lists-style problems. Loops and conditionals take practice, so replay the Making Decisions game if the quiz felt tricky.','2026-11-12 21:30:00','2026-11-14 00:02:44'),(104,5,7,'Kevin, thank you for working on this every few days. Let me know if you want help with the quiz questions.','2026-11-19 03:10:00',NULL),(105,6,1,'Welcome, Aisha! You are off to a good start. Take your time with each lesson and use the games to practice.','2026-11-05 19:15:00','2026-11-06 23:40:09'),(106,6,1,'You are close on Making Decisions. Re-read the lesson once more and try the quiz again.','2026-11-20 20:00:00',NULL);
/*!40000 ALTER TABLE `feedback_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_attempts`
--

DROP TABLE IF EXISTS `game_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_attempts` (
  `game_attempt_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `score` int NOT NULL,
  `max_score` int NOT NULL,
  `time_spent_seconds` int NOT NULL DEFAULT '0',
  `completed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`game_attempt_id`),
  KEY `idx_games_student_completed` (`student_id`,`completed_at`),
  KEY `idx_games_lesson` (`lesson_id`),
  CONSTRAINT `fk_games_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_games_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_game_score` CHECK (((`max_score` > 0) and (`score` >= 0) and (`score` <= `max_score`))),
  CONSTRAINT `chk_game_time` CHECK ((`time_spent_seconds` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_attempts`
--

LOCK TABLES `game_attempts` WRITE;
/*!40000 ALTER TABLE `game_attempts` DISABLE KEYS */;
INSERT INTO `game_attempts` VALUES (1,2,29,230,300,20,'2026-11-24 23:44:36'),(101,4,29,220,300,134,'2026-10-07 01:11:57'),(102,4,30,170,200,125,'2026-10-10 00:45:12'),(103,4,31,130,200,121,'2026-10-14 22:19:34'),(104,4,32,150,200,86,'2026-10-17 21:46:24'),(105,4,33,210,300,129,'2026-10-22 01:40:29'),(106,4,34,150,200,120,'2026-10-24 23:45:36'),(107,4,34,190,200,76,'2026-10-25 14:45:36'),(108,4,35,150,200,65,'2026-10-26 23:36:32'),(109,4,36,220,300,59,'2026-10-29 20:26:09'),(110,4,39,180,300,65,'2026-11-08 23:14:22'),(111,4,39,260,300,42,'2026-11-09 17:14:22'),(112,4,40,230,250,46,'2026-11-10 23:00:07'),(113,4,41,220,300,122,'2026-11-13 02:13:06'),(114,5,29,210,300,142,'2026-10-13 22:40:12'),(115,5,30,130,200,130,'2026-10-17 22:32:04'),(116,5,31,140,200,79,'2026-10-22 01:25:43'),(117,5,33,260,300,91,'2026-10-31 23:08:23'),(118,5,34,130,200,55,'2026-11-05 22:44:45'),(119,5,39,190,300,57,'2026-11-10 00:24:59'),(120,5,40,190,250,145,'2026-11-12 23:16:10'),(121,6,29,230,300,133,'2026-11-03 02:29:29'),(122,6,30,130,200,55,'2026-11-06 23:05:01'),(123,6,30,190,200,54,'2026-11-07 17:05:01'),(124,6,31,130,200,100,'2026-11-10 23:13:14');
/*!40000 ALTER TABLE `game_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lesson_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `status` enum('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `time_spent_seconds` int NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `uq_student_lesson` (`student_id`,`lesson_id`),
  KEY `idx_progress_lesson` (`lesson_id`),
  KEY `idx_progress_student_completed` (`student_id`,`completed_at`),
  CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_progress_completed` CHECK (((`status` <> _utf8mb4'completed') or (`completed_at` is not null))),
  CONSTRAINT `chk_progress_time` CHECK ((`time_spent_seconds` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
INSERT INTO `lesson_progress` VALUES (1,2,29,'completed',0,'2026-11-24 23:46:24'),(2,2,30,'in_progress',0,NULL),(3,2,39,'in_progress',0,NULL),(101,4,29,'completed',309,'2026-10-07 01:06:13'),(102,4,30,'completed',186,'2026-10-10 00:39:29'),(103,4,31,'completed',539,'2026-10-14 22:13:42'),(104,4,32,'completed',606,'2026-10-17 21:40:35'),(105,4,33,'completed',568,'2026-10-22 01:35:15'),(106,4,34,'completed',626,'2026-10-24 23:41:33'),(107,4,35,'completed',421,'2026-10-26 23:30:27'),(108,4,36,'completed',514,'2026-10-29 20:20:46'),(109,4,37,'in_progress',289,NULL),(110,4,39,'completed',402,'2026-11-08 23:09:11'),(111,4,40,'completed',527,'2026-11-10 22:54:15'),(112,4,41,'completed',393,'2026-11-13 02:10:25'),(113,5,29,'completed',519,'2026-10-13 22:33:59'),(114,5,30,'completed',472,'2026-10-17 22:28:12'),(115,5,31,'completed',583,'2026-10-22 01:23:03'),(116,5,32,'completed',514,'2026-10-28 00:22:01'),(117,5,33,'completed',370,'2026-10-31 23:03:57'),(118,5,34,'completed',575,'2026-11-05 22:41:21'),(119,5,35,'in_progress',140,NULL),(120,5,39,'completed',549,'2026-11-10 00:19:57'),(121,5,40,'completed',681,'2026-11-12 23:12:39'),(122,6,29,'completed',389,'2026-11-03 02:24:00'),(123,6,30,'completed',371,'2026-11-06 22:59:10'),(124,6,31,'completed',363,'2026-11-10 23:10:28'),(125,6,32,'in_progress',210,NULL);
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `lesson_id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text,
  `game_type` varchar(50) DEFAULT NULL,
  `game_data` json DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`lesson_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (29,1,'Welcome to Python','Python is a beginner-friendly programming language. A program is a list of instructions that the computer follows one line at a time.\n\nThe print() function shows a message on the screen:\n\nprint(\"Hello, world!\")\n\nText inside quotes is called a string. A line that starts with # is a comment: a note for people that Python ignores.\n\n# This line is a comment\nprint(\"Python is fun\")\n\nIf Python does not understand something, it shows an error message. Do not worry: errors are normal, and they tell you what to fix.','memory','{\"pairs\": [{\"a\": \"Python\", \"b\": \"A beginner-friendly programming language\"}, {\"a\": \"code\", \"b\": \"Instructions a computer can follow\"}, {\"a\": \"print()\", \"b\": \"Shows a message on the screen\"}, {\"a\": \"comment\", \"b\": \"A note for people that Python ignores\"}, {\"a\": \"error\", \"b\": \"A message that says something needs fixing\"}, {\"a\": \"string\", \"b\": \"Text inside quotes\"}], \"title\": \"Memory Match\", \"context\": \"Python 101 - Welcome to Python\", \"instructions\": \"Flip two cards to match each word with its meaning.\"}',1),(30,1,'Variables and Data Types','A variable is a name that holds a value. You create one with the = sign:\n\nage = 10\nname = \"Ada\"\n\nYou can change a variable later:\n\nage = age + 1\nprint(age)\n\nEvery value has a type. The four you will use most:\n- int: whole numbers, like 7\n- float: decimals, like 3.5\n- str: text in quotes, like \"hi\"\n- bool: True or False\n\nThe type() function tells you which one a value is:\n\nprint(type(3.5))','match_output','{\"pairs\": [{\"code\": \"x = 7\\nprint(x)\", \"output\": \"7\"}, {\"code\": \"name = \\\"Ada\\\"\\nprint(name)\", \"output\": \"Ada\"}, {\"code\": \"age = 10\\nage = age + 1\\nprint(age)\", \"output\": \"11\"}, {\"code\": \"print(type(3.5))\", \"output\": \"<class \'float\'>\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Variables and Data Types\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',2),(31,1,'Numbers and Math','Python is a great calculator. The math operators are:\n\n+  add          -  subtract\n*  multiply     /  divide (gives a decimal)\n// divide and drop the decimal\n%  the remainder after dividing\n**  to the power of\n\nprint(7 + 3)\nprint(7 // 2)\nprint(7 % 2)\nprint(2 ** 3)\n\nPython follows the usual order of operations: multiplication and division happen before addition and subtraction. Use parentheses to change the order:\n\nprint(2 + 3 * 4)\nprint((2 + 3) * 4)','match_output','{\"pairs\": [{\"code\": \"print(2 + 3 * 4)\", \"output\": \"14\"}, {\"code\": \"print(7 // 2)\", \"output\": \"3\"}, {\"code\": \"print(7 % 2)\", \"output\": \"1\"}, {\"code\": \"print(2 ** 3)\", \"output\": \"8\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Numbers and Math\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',3),(32,1,'Working with Strings','A string is text. Use single or double quotes:\n\ngreeting = \"Hello\"\n\nJoin strings with +, and repeat them with *:\n\nprint(\"Hi \" + \"there\")\nprint(\"ab\" * 3)\n\nlen() counts the characters. Each character has a position number called an index, starting at 0:\n\nword = \"Python\"\nprint(len(word))\nprint(word[0])\n\nStrings also have handy methods:\n\nprint(word.upper())\nprint(word.lower())','match_output','{\"pairs\": [{\"code\": \"print(\\\"Hi \\\" + \\\"there\\\")\", \"output\": \"Hi there\"}, {\"code\": \"print(len(\\\"Python\\\"))\", \"output\": \"6\"}, {\"code\": \"print(\\\"python\\\".upper())\", \"output\": \"PYTHON\"}, {\"code\": \"print(\\\"Python\\\"[0])\", \"output\": \"P\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Working with Strings\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',4),(33,1,'Input and Output','Programs get more fun when they talk to the user. The input() function asks a question and waits for an answer:\n\nname = input(\"What is your name? \")\nprint(\"Hello, \" + name)\n\ninput() always gives back a string, even if the user types a number. Convert it with int() or float():\n\nage = int(input(\"How old are you? \"))\nprint(age + 1)\n\nAn f-string lets you put variables inside text. Start the string with f and put each variable in { }:\n\nname = \"Sam\"\nprint(f\"Hi {name}!\")','memory','{\"pairs\": [{\"a\": \"input()\", \"b\": \"Asks the user to type something\"}, {\"a\": \"f-string\", \"b\": \"Text with variables inside { }\"}, {\"a\": \"int()\", \"b\": \"Turns text into a whole number\"}, {\"a\": \"float()\", \"b\": \"Turns text into a decimal number\"}, {\"a\": \"str()\", \"b\": \"Turns a value into text\"}, {\"a\": \"prompt\", \"b\": \"The message shown when asking a question\"}], \"title\": \"Memory Match\", \"context\": \"Python 101 - Input and Output\", \"instructions\": \"Flip two cards to match each term with its meaning.\"}',5),(34,1,'Making Decisions','Programs can choose what to do with if statements. The code under the if only runs when the condition is True. Notice the colon and the indented line:\n\nage = 12\nif age >= 13:\n    print(\"teen\")\nelse:\n    print(\"kid\")\n\nComparison operators give True or False:\n==  equal to      !=  not equal to\n>  greater than   <  less than\n>=  at least      <=  at most\n\nUse elif (short for else if) to check more than one thing in order:\n\nn = 0\nif n > 0:\n    print(\"pos\")\nelif n < 0:\n    print(\"neg\")\nelse:\n    print(\"zero\")\n\nCombine conditions with and, or and not.','match_output','{\"pairs\": [{\"code\": \"age = 12\\nif age >= 13:\\n    print(\\\"teen\\\")\\nelse:\\n    print(\\\"kid\\\")\", \"output\": \"kid\"}, {\"code\": \"print(3 == 3)\", \"output\": \"True\"}, {\"code\": \"n = 0\\nif n > 0:\\n    print(\\\"pos\\\")\\nelif n < 0:\\n    print(\\\"neg\\\")\\nelse:\\n    print(\\\"zero\\\")\", \"output\": \"zero\"}, {\"code\": \"print(not True)\", \"output\": \"False\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Making Decisions\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',6),(35,1,'Loops','A loop repeats code so you do not have to write it again and again.\n\nA for loop repeats a set number of times. range(3) gives the numbers 0, 1, 2:\n\nfor i in range(3):\n    print(i)\n\nYou can also loop over the letters in a string:\n\nfor c in \"go\":\n    print(c)\n\nA while loop keeps going as long as its condition is True. Make sure something inside changes, or it will never stop:\n\nn = 3\nwhile n > 0:\n    print(n)\n    n -= 1\n\nLoops are great for adding things up:\n\ntotal = 0\nfor i in range(1, 4):\n    total += i\nprint(total)','match_output','{\"pairs\": [{\"code\": \"for i in range(3):\\n    print(i)\", \"output\": \"0\\n1\\n2\"}, {\"code\": \"for c in \\\"go\\\":\\n    print(c)\", \"output\": \"g\\no\"}, {\"code\": \"n = 3\\nwhile n > 0:\\n    print(n)\\n    n -= 1\", \"output\": \"3\\n2\\n1\"}, {\"code\": \"total = 0\\nfor i in range(1, 4):\\n    total += i\\nprint(total)\", \"output\": \"6\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Loops\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',7),(36,1,'Lists','A list holds several values in order. Write it with square brackets and commas:\n\nfruits = [\"apple\", \"pear\", \"fig\"]\n\nEach item has an index, starting at 0:\n\nprint(fruits[0])\nprint(fruits[2])\n\nLists can change. append() adds to the end and remove() takes an item out:\n\nnums = [1, 2]\nnums.append(3)\nprint(len(nums))\n\nYou can visit every item with a for loop:\n\nfor f in fruits:\n    print(f)','memory','{\"pairs\": [{\"a\": \"list\", \"b\": \"An ordered collection of items\"}, {\"a\": \"[ ]\", \"b\": \"Square brackets that make a list\"}, {\"a\": \"index\", \"b\": \"An item\'s position, starting at 0\"}, {\"a\": \"append()\", \"b\": \"Adds an item to the end\"}, {\"a\": \"remove()\", \"b\": \"Takes an item out of the list\"}, {\"a\": \"len()\", \"b\": \"Counts how many items there are\"}], \"title\": \"Memory Match\", \"context\": \"Python 101 - Lists\", \"instructions\": \"Flip two cards to match each term with its meaning.\"}',8),(37,1,'Functions','A function is a named block of code you can use again and again. Define one with def:\n\ndef hi():\n    print(\"hi\")\n\nhi()\n\nA function can take inputs, called parameters, and send a value back with return:\n\ndef add(a, b):\n    return a + b\n\nprint(add(2, 3))\n\nprint() shows a value on the screen, but return hands the value back to your program so you can keep using it:\n\ndef double(n):\n    return n * 2\n\nresult = double(4)\nprint(result)','match_output','{\"pairs\": [{\"code\": \"def hi():\\n    print(\\\"hi\\\")\\nhi()\", \"output\": \"hi\"}, {\"code\": \"def add(a, b):\\n    return a + b\\nprint(add(2, 3))\", \"output\": \"5\"}, {\"code\": \"def double(n):\\n    return n * 2\\nprint(double(4))\", \"output\": \"8\"}, {\"code\": \"def greet(name):\\n    print(\\\"Hello \\\" + name)\\ngreet(\\\"Bo\\\")\", \"output\": \"Hello Bo\"}], \"title\": \"Match the Output\", \"context\": \"Python 101 - Functions\", \"instructions\": \"Pick a code card, then pick the output it prints.\"}',9),(38,1,'Dictionaries','A dictionary stores information as key and value pairs, like a real dictionary stores a word and its meaning. Write it with curly braces:\n\npet = {\"name\": \"Rex\", \"age\": 3}\n\nLook up a value by its key:\n\nprint(pet[\"name\"])\n\nAdd or change a value by assigning to a key:\n\nscores = {\"Ana\": 90}\nscores[\"Bo\"] = 85\nprint(len(scores))\n\nUse in to check whether a key exists:\n\nprint(\"Ana\" in scores)\n\nDictionaries are perfect when you want to look things up by name instead of by position.','memory','{\"pairs\": [{\"a\": \"dictionary\", \"b\": \"A collection of key and value pairs\"}, {\"a\": \"key\", \"b\": \"The label used to look a value up\"}, {\"a\": \"value\", \"b\": \"The data stored under a key\"}, {\"a\": \"{ }\", \"b\": \"Curly braces that make a dictionary\"}, {\"a\": \"in\", \"b\": \"Checks whether a key exists\"}, {\"a\": \"len()\", \"b\": \"Counts how many pairs there are\"}], \"title\": \"Memory Match\", \"context\": \"Python 101 - Dictionaries\", \"instructions\": \"Flip two cards to match each term with its meaning.\"}',10),(39,2,'Setting Up Your Environment','Before you write Java, you need the right tools.\n\nThe JDK (Java Development Kit) is the toolbox. It includes javac, the compiler that turns your source code (.java files) into bytecode (.class files). The JVM (Java Virtual Machine) then runs that bytecode, which is why Java programs can run on many kinds of computers.\n\nSetup checklist:\n1. Install a JDK (version 17 or newer).\n2. Check it works: open a terminal and type  java -version  and  javac -version.\n3. Pick an editor or IDE, such as VS Code, IntelliJ IDEA or Eclipse.\n\nTip: if the terminal says \"command not found\", the JDK\'s bin folder is probably not on your PATH.','memory','{\"pairs\": [{\"a\": \"JDK\", \"b\": \"Tools for building Java programs\"}, {\"a\": \"JVM\", \"b\": \"Runs compiled bytecode\"}, {\"a\": \"javac\", \"b\": \"The Java compiler\"}, {\"a\": \".java\", \"b\": \"A source code file\"}, {\"a\": \".class\", \"b\": \"A compiled bytecode file\"}, {\"a\": \"IDE\", \"b\": \"An editor for writing code\"}], \"instructions\": \"Flip two cards to match each Java setup term with its meaning.\"}',1),(40,2,'Your First Java Program','Every Java program lives inside a class, and it starts running at the main method.\n\npublic class Hello {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, world!\");\n    }\n}\n\nThings to notice:\n- The file must be named Hello.java because the public class is called Hello.\n- Each statement ends with a semicolon ( ; ).\n- System.out.println prints text and then moves to a new line. System.out.print prints without a new line.\n- Text goes in double quotes.\n\nCompile with  javac Hello.java  and run with  java Hello.','match_output','{\"pairs\": [{\"code\": \"System.out.println(\\\"Hi\\\");\", \"output\": \"Hi\"}, {\"code\": \"System.out.print(\\\"A\\\");\\nSystem.out.print(\\\"B\\\");\", \"output\": \"AB\"}, {\"code\": \"System.out.println(2 + 3);\", \"output\": \"5\"}, {\"code\": \"System.out.println(\\\"2 + 3\\\");\", \"output\": \"2 + 3\"}, {\"code\": \"System.out.println(\\\"A\\\");\\nSystem.out.println(\\\"B\\\");\", \"output\": \"A\\nB\"}], \"instructions\": \"Pick a code card, then pick the output it prints.\"}',2),(41,2,'Variables and Data Types','A variable is a named box that holds a value. In Java every variable has a type that says what kind of value it can hold.\n\nCommon types:\n- int: whole numbers, like 42\n- double: numbers with a decimal point, like 3.14\n- boolean: true or false\n- char: a single character in single quotes, like \'A\'\n- String: text in double quotes, like \"Sam\"\n\nint count = 5;\ndouble price = 9.99;\nboolean done = false;\nString name = \"Sam\";\n\nCareful: dividing two ints gives an int, so 7 / 2 is 3, not 3.5. Use the keyword final to make a value that cannot change.','memory','{\"pairs\": [{\"a\": \"int\", \"b\": \"A whole number\"}, {\"a\": \"double\", \"b\": \"A number with a decimal point\"}, {\"a\": \"boolean\", \"b\": \"true or false\"}, {\"a\": \"char\", \"b\": \"A single character\"}, {\"a\": \"String\", \"b\": \"Text in double quotes\"}, {\"a\": \"final\", \"b\": \"A variable that cannot change\"}], \"instructions\": \"Flip two cards to match each data type with its meaning.\"}',3),(42,2,'Operators and Expressions','Operators combine values into expressions.\n\nArithmetic: +  -  *  /  %\n- % is the remainder: 10 % 3 is 1.\n- Integer division drops the decimal part: 9 / 2 is 4.\n- Multiplication and division happen before addition and subtraction: 2 + 3 * 4 is 14. Use parentheses to change the order.\n\nShortcuts: x += 2 means x = x + 2, and x++ adds one.\n\nComparison: ==  !=  >  <  >=  <=  (these give true or false)\nLogical: &&  (and),  ||  (or),  !  (not)','match_output','{\"pairs\": [{\"code\": \"System.out.println(10 % 3);\", \"output\": \"1\"}, {\"code\": \"int x = 4;\\nx++;\\nSystem.out.println(x);\", \"output\": \"5\"}, {\"code\": \"System.out.println(9 / 2);\", \"output\": \"4\"}, {\"code\": \"System.out.println(2 + 3 * 4);\", \"output\": \"14\"}, {\"code\": \"System.out.println(5 > 3);\", \"output\": \"true\"}], \"instructions\": \"Pick a code snippet, then pick the output it prints.\"}',4),(43,2,'Working with Strings','A String holds text. Strings are objects, so they come with useful methods.\n\nString word = \"Java\";\nword.length()        gives 4\nword.charAt(0)       gives \'J\' (positions start at 0)\nword.toUpperCase()   gives \"JAVA\"\nword.substring(1, 3) gives \"av\" (from position 1 up to, but not including, 3)\n\"Hi\" + \"!\"           gives \"Hi!\" (+ joins strings)\n\nCompare strings with .equals(), not ==. The == operator checks whether two variables point to the same object, while .equals() checks whether the text is the same:\n\n\"cat\".equals(\"cat\")  is true','match_output','{\"pairs\": [{\"code\": \"System.out.println(\\\"Java\\\".length());\", \"output\": \"4\"}, {\"code\": \"System.out.println(\\\"hello\\\".charAt(0));\", \"output\": \"h\"}, {\"code\": \"System.out.println(\\\"Hi\\\" + \\\"!\\\");\", \"output\": \"Hi!\"}, {\"code\": \"System.out.println(\\\"abc\\\".toUpperCase());\", \"output\": \"ABC\"}, {\"code\": \"System.out.println(\\\"Java\\\".substring(1, 3));\", \"output\": \"av\"}], \"instructions\": \"Pick a code snippet, then pick the output it prints.\"}',5),(44,2,'Conditionals','Conditionals let a program choose what to do.\n\nint score = 85;\nif (score >= 90) {\n    System.out.println(\"A\");\n} else if (score >= 80) {\n    System.out.println(\"B\");\n} else {\n    System.out.println(\"Keep going\");\n}\n\nJava checks each condition from the top and runs only the first block whose condition is true. The condition must be a boolean.\n\nCombine conditions with && (and), || (or) and ! (not). Use == for numbers and .equals() for Strings.','match_output','{\"pairs\": [{\"code\": \"if (5 > 3) {\\n    System.out.println(\\\"yes\\\");\\n}\", \"output\": \"yes\"}, {\"code\": \"int a = 2;\\nif (a == 3) {\\n    System.out.println(\\\"three\\\");\\n} else {\\n    System.out.println(\\\"other\\\");\\n}\", \"output\": \"other\"}, {\"code\": \"int age = 20;\\nif (age >= 18 && age < 65) {\\n    System.out.println(\\\"adult\\\");\\n}\", \"output\": \"adult\"}, {\"code\": \"boolean ok = false;\\nif (!ok) {\\n    System.out.println(\\\"retry\\\");\\n}\", \"output\": \"retry\"}], \"instructions\": \"Pick a code snippet, then pick the output it prints.\"}',6),(45,2,'Loops','Loops repeat code.\n\nfor loop (when you know how many times):\nfor (int i = 0; i < 3; i++) {\n    System.out.println(i);\n}\nprints 0, 1 and 2. The three parts are: start (int i = 0), keep going while (i < 3), and step (i++).\n\nwhile loop (repeat while a condition is true):\nint n = 3;\nwhile (n > 0) {\n    n--;\n}\n\nbreak leaves a loop early. continue skips to the next round. Be careful: a loop whose condition never becomes false runs forever.','match_output','{\"pairs\": [{\"code\": \"for (int i = 0; i < 3; i++) {\\n    System.out.println(i);\\n}\", \"output\": \"0\\n1\\n2\"}, {\"code\": \"int n = 0;\\nwhile (n < 2) {\\n    System.out.println(n);\\n    n++;\\n}\", \"output\": \"0\\n1\"}, {\"code\": \"for (int i = 1; i <= 3; i++) {\\n    System.out.print(i);\\n}\", \"output\": \"123\"}, {\"code\": \"int sum = 0;\\nfor (int i = 1; i <= 4; i++) {\\n    sum += i;\\n}\\nSystem.out.println(sum);\", \"output\": \"10\"}], \"instructions\": \"Pick a code snippet, then pick the output it prints.\"}',7),(46,2,'Arrays','An array holds several values of the same type.\n\nint[] scores = {90, 75, 82};\nscores[0]        is 90 (the first element: positions start at 0)\nscores[2]        is 82\nscores.length    is 3 (the number of elements; there are no parentheses)\n\nint[] zeros = new int[3];   // three ints, each starting at 0\n\nLoop over an array:\nfor (int i = 0; i < scores.length; i++) {\n    System.out.println(scores[i]);\n}\nor with a for-each loop:\nfor (int s : scores) {\n    System.out.println(s);\n}\n\nAn array\'s size is fixed once it is created. Using an index outside the array causes an ArrayIndexOutOfBoundsException.','memory','{\"pairs\": [{\"a\": \"array\", \"b\": \"A fixed-size list of same-type values\"}, {\"a\": \"index\", \"b\": \"A position number, starting at 0\"}, {\"a\": \"length\", \"b\": \"The number of elements in an array\"}, {\"a\": \"new\", \"b\": \"Creates a new array or object\"}, {\"a\": \"a[0]\", \"b\": \"The first element of a\"}, {\"a\": \"for-each\", \"b\": \"Visits every element in turn\"}], \"instructions\": \"Flip two cards to match each array term with its meaning.\"}',8),(47,2,'Methods','A method is a named block of code you can run whenever you need it.\n\nstatic int add(int a, int b) {\n    return a + b;\n}\n\n- int before the name is the return type: the kind of value the method gives back.\n- (int a, int b) are the parameters, the inputs.\n- return sends the result back to the caller.\n- void means the method returns nothing.\n\nCalling it:\nint total = add(2, 3);   // total is 5\n\nMethods keep programs organized and let you reuse code instead of copying it. A method can call other methods.','match_output','{\"pairs\": [{\"code\": \"static int sq(int n) {\\n    return n * n;\\n}\\nSystem.out.println(sq(4));\", \"output\": \"16\"}, {\"code\": \"static void hi() {\\n    System.out.println(\\\"Hi\\\");\\n}\\nhi();\\nhi();\", \"output\": \"Hi\\nHi\"}, {\"code\": \"static int add(int a, int b) {\\n    return a + b;\\n}\\nSystem.out.println(add(2, 3));\", \"output\": \"5\"}, {\"code\": \"static String greet(String n) {\\n    return \\\"Hi \\\" + n;\\n}\\nSystem.out.println(greet(\\\"Sam\\\"));\", \"output\": \"Hi Sam\"}], \"instructions\": \"Pick a code snippet, then pick the output it prints.\"}',9),(48,2,'Classes and Objects','A class is a blueprint. An object is something built from that blueprint.\n\nclass Dog {\n    String name;\n\n    Dog(String name) {      // constructor\n        this.name = name;\n    }\n\n    void bark() {\n        System.out.println(name + \" says woof\");\n    }\n}\n\nDog d = new Dog(\"Rex\");\nd.bark();                   // Rex says woof\n\n- Fields (like name) store an object\'s data.\n- Methods (like bark) are what an object can do.\n- A constructor runs when you create an object with new. It has the same name as the class.\n- this means \"the current object\".\n\nYou can make many objects from one class, and each keeps its own field values.','memory','{\"pairs\": [{\"a\": \"class\", \"b\": \"A blueprint for objects\"}, {\"a\": \"object\", \"b\": \"An instance of a class\"}, {\"a\": \"new\", \"b\": \"Creates an object\"}, {\"a\": \"constructor\", \"b\": \"Runs when an object is created\"}, {\"a\": \"field\", \"b\": \"A variable inside a class\"}, {\"a\": \"this\", \"b\": \"The current object\"}], \"instructions\": \"Flip two cards to match each object-oriented term with its meaning.\"}',10);
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parent_students`
--

DROP TABLE IF EXISTS `parent_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parent_students` (
  `parent_id` int NOT NULL,
  `student_id` int NOT NULL,
  `linked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`parent_id`,`student_id`),
  KEY `idx_parent_students_student` (`student_id`),
  CONSTRAINT `fk_ps_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_ps_not_self` CHECK ((`parent_id` <> `student_id`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parent_students`
--

LOCK TABLES `parent_students` WRITE;
/*!40000 ALTER TABLE `parent_students` DISABLE KEYS */;
INSERT INTO `parent_students` VALUES (7,5,'2026-10-13 01:03:00'),(8,4,'2026-10-06 00:12:00'),(9,6,'2026-11-25 00:14:55');
/*!40000 ALTER TABLE `parent_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_text` text NOT NULL,
  `code_snippet` text,
  `explanation` text,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`question_id`),
  KEY `idx_questions_quiz_order` (`quiz_id`,`sort_order`),
  CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,33,'What does this code print?','print(\"Hello, world!\")','print() shows the text without the quotes.',1),(2,33,'Which symbol starts a comment in Python?',NULL,'Python ignores everything after # on that line.',2),(3,33,'Which function shows text on the screen?',NULL,'print() is the function that displays a message.',3),(4,34,'What does this code print?','x = 5\nprint(type(x))','5 is a whole number, so its type is int.',1),(5,34,'Which value is a string?',NULL,'Text inside quotes is a string.',2),(6,34,'What does this code print?','y = 3\ny = y + 2\nprint(y)','y starts at 3, then becomes 3 + 2.',3),(7,35,'What does this code print?','print(10 / 4)','A single / always gives a decimal answer.',1),(8,35,'What does the % operator give you?',NULL,'7 % 2 is 1 because 7 divided by 2 leaves 1 over.',2),(9,35,'What does this code print?','print(2 + 3 * 4)','Multiplication happens first: 3 * 4 is 12, then 2 + 12.',3),(10,36,'What does this code print?','s = \"cat\"\nprint(s * 2)','Multiplying a string repeats it.',1),(11,36,'What does this code print?','print(len(\"hello\"))','len() counts the characters in the string.',2),(12,36,'Which method turns text into lowercase letters?',NULL,'\"HELLO\".lower() gives \"hello\".',3),(13,37,'What type of value does input() always give back?',NULL,'input() returns text, even when the user types digits.',1),(14,37,'What does this code print?','name = \"Sam\"\nprint(f\"Hi {name}!\")','The f-string replaces {name} with the value of name.',2),(15,37,'What does this code print?','n = int(\"4\")\nprint(n + 1)','int(\"4\") turns the text into the number 4.',3),(16,38,'What does this code print?','print(4 == 5)','4 is not equal to 5, so the comparison is False.',1),(17,38,'What does elif mean?',NULL,'elif is checked only when the earlier conditions were False.',2),(18,38,'What does this code print?','x = 10\nif x > 5:\n    print(\"A\")\nelse:\n    print(\"B\")','10 is greater than 5, so the if branch runs.',3),(19,39,'How many times does this loop print?','for i in range(4):\n    print(\"x\")','range(4) gives four numbers: 0, 1, 2, 3.',1),(20,39,'Which loop repeats while a condition is True?',NULL,'A while loop checks its condition before every round.',2),(21,39,'Which numbers does range(3) give?',NULL,'range starts at 0 and stops before the number you give it.',3),(22,40,'What does this code print?','fruits = [\"apple\", \"pear\", \"fig\"]\nprint(fruits[0])','Index 0 is the first item.',1),(23,40,'What does this code print?','nums = [1, 2]\nnums.append(3)\nprint(len(nums))','append() adds a third item, so len() is 3.',2),(24,40,'What is the index of the first item in a list?',NULL,'Python counts positions from 0.',3),(25,41,'Which keyword defines a function?',NULL,'A function definition starts with def.',1),(26,41,'What does this code print?','def square(n):\n    return n * n\nprint(square(5))','square(5) returns 5 * 5.',2),(27,41,'What does return do?',NULL,'The returned value can be stored or used in more math.',3),(28,42,'What does this code print?','pet = {\"name\": \"Rex\", \"age\": 3}\nprint(pet[\"name\"])','The key \"name\" holds the value \"Rex\".',1),(29,42,'In {\"a\": 1}, what is \"a\"?',NULL,'The part before the colon is the key.',2),(30,42,'What does this code print?','scores = {\"Ana\": 90}\nscores[\"Bo\"] = 85\nprint(len(scores))','Assigning a new key adds a second pair.',3),(31,48,'What does JDK stand for?',NULL,'The JDK is the Java Development Kit: the compiler (javac) and other tools you need to build Java programs.',1),(32,48,'Which tool compiles Java source code into bytecode?',NULL,'javac is the Java compiler. The java command runs compiled programs.',2),(33,48,'What file extension do Java source files use?',NULL,'Source code is saved in .java files. The compiler produces .class files.',3),(34,48,'What does the JVM do?',NULL,'The Java Virtual Machine runs compiled bytecode, so the same program can run on different computers.',4),(35,49,'Which method is where a Java program starts running?',NULL,'The JVM looks for public static void main(String[] args) to start your program.',1),(36,49,'What does this code print?','System.out.print(\"Hi\");\nSystem.out.print(\"!\");','print does not add a new line, so both pieces appear together.',2),(37,49,'How does a Java statement end?',NULL,'Statements end with a semicolon.',3),(38,49,'A public class named Hello must be saved in which file?',NULL,'A public class must live in a file with exactly the same name plus .java.',4),(39,50,'Which type stores a whole number?',NULL,'int holds whole numbers. double holds numbers with decimals.',1),(40,50,'Which declaration is valid Java?',NULL,'The value must match the type: int needs a whole number, and strings need double quotes.',2),(41,50,'What type should hold the text \"Sam\"?','name = \"Sam\";','Text is stored in a String.',3),(42,50,'What does this code print?','double x = 7 / 2;\nSystem.out.println(x);','7 / 2 is integer division, so the result is 3 before it is stored as the double 3.0.',4),(43,51,'What does this print?','System.out.println(10 % 3);','% gives the remainder of the division: 10 divided by 3 leaves 1.',1),(44,51,'What does this print?','int x = 5;\nx += 2;\nSystem.out.println(x);','x += 2 is the same as x = x + 2.',2),(45,51,'What does this print?','System.out.println(7 / 2);','Both numbers are ints, so Java drops the decimal part.',3),(46,51,'What is the value of b?','boolean b = (4 > 2) && (3 < 1);','&& is true only if both sides are true. 3 < 1 is false.',4),(47,52,'What does this print?','System.out.println(\"Java\".length());','length() counts the characters: J, a, v, a.',1),(48,52,'What does this print?','System.out.println(\"hello\".charAt(1));','Positions start at 0, so charAt(1) is the second character.',2),(49,52,'Which is the right way to compare the text of two Strings?',NULL,'Use .equals() to compare text. == compares whether they are the same object.',3),(50,52,'What does this print?','System.out.println(\"Java\".substring(1, 3));','substring(1, 3) starts at position 1 and stops before position 3.',4),(51,53,'What does this print?','int x = 5;\nif (x > 3) {\n    System.out.println(\"big\");\n} else {\n    System.out.println(\"small\");\n}','x > 3 is true, so the if block runs.',1),(52,53,'Which keyword handles the \"otherwise\" case of an if?',NULL,'else runs when the if condition is false.',2),(53,53,'What does this print?','int n = 10;\nif (n % 2 == 0) {\n    System.out.println(\"even\");\n} else {\n    System.out.println(\"odd\");\n}','10 % 2 is 0, so n is even.',3),(54,53,'What does && mean in a condition?',NULL,'&& is logical AND: both sides must be true.',4),(55,54,'What does this print?','for (int i = 0; i < 3; i++) {\n    System.out.println(i);\n}','i starts at 0 and the loop stops when i reaches 3, so it prints 0, 1 and 2 on separate lines.',1),(56,54,'What does this print?','int n = 3;\nwhile (n > 0) {\n    n--;\n}\nSystem.out.println(n);','n counts down from 3 and the loop ends when n is 0.',2),(57,54,'Which keyword leaves a loop immediately?',NULL,'break exits the nearest loop. continue only skips to the next round.',3),(58,54,'How many times does this loop run?','for (int i = 1; i <= 5; i++) {\n    // ...\n}','i takes the values 1, 2, 3, 4 and 5.',4),(59,55,'What does this print?','int[] a = {4, 8, 15};\nSystem.out.println(a[1]);','Positions start at 0, so a[1] is the second element.',1),(60,55,'What is the index of the first element of an array?',NULL,'Java arrays are zero-indexed.',2),(61,55,'What does this print?','int[] a = new int[3];\nSystem.out.println(a[0]);','A new int array is filled with zeros.',3),(62,55,'How do you get the number of elements in an array named a?',NULL,'For arrays, length is a field, so there are no parentheses.',4),(63,56,'What does this print?','static int add(int a, int b) {\n    return a + b;\n}\n// ...\nSystem.out.println(add(2, 3));','add returns the sum of its two inputs.',1),(64,56,'What does the keyword void mean for a method?',NULL,'A void method does its work but does not return a value.',2),(65,56,'The inputs listed in a method\'s parentheses are called:',NULL,'They are the method\'s parameters (the values you pass in are arguments).',3),(66,56,'What does this print?','static int twice(int n) {\n    return n * 2;\n}\n// ...\nSystem.out.println(twice(twice(2)));','twice(2) is 4, and twice(4) is 8.',4),(67,57,'Which keyword creates a new object?',NULL,'new creates an object from a class and calls its constructor.',1),(68,57,'What is a class?',NULL,'A class describes what its objects store and can do.',2),(69,57,'What does this print?','class Dog { String name; }\n// ...\nDog d = new Dog();\nd.name = \"Rex\";\nSystem.out.println(d.name);','The object d stores \"Rex\" in its name field.',3),(70,57,'What is a constructor?',NULL,'A constructor has the same name as the class and sets up a new object.',4);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_attempts` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `correct_count` int DEFAULT NULL,
  `total_questions` int DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `idx_attempts_student_finished` (`student_id`,`finished_at`),
  KEY `idx_attempts_quiz_student` (`quiz_id`,`student_id`),
  CONSTRAINT `fk_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attempts_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_attempt_counts` CHECK (((`correct_count` is null) or (`total_questions` is null) or (`correct_count` <= `total_questions`))),
  CONSTRAINT `chk_attempt_finished` CHECK (((`score` is null) or (`finished_at` is not null))),
  CONSTRAINT `chk_attempt_score` CHECK (((`score` is null) or ((`score` >= 0) and (`score` <= 100))))
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_attempts`
--

LOCK TABLES `quiz_attempts` WRITE;
/*!40000 ALTER TABLE `quiz_attempts` DISABLE KEYS */;
INSERT INTO `quiz_attempts` VALUES (1,2,33,0.00,0,3,'2026-11-24 21:52:51','2026-11-24 21:52:51'),(2,2,48,0.00,0,4,'2026-11-24 22:51:29','2026-11-24 22:51:47'),(3,2,33,33.33,1,3,'2026-11-24 23:45:41','2026-11-24 23:45:52'),(4,2,33,66.67,2,3,'2026-11-24 23:46:05','2026-11-24 23:46:24'),(101,4,33,100.00,3,3,'2026-10-07 01:04:00','2026-10-07 01:06:13'),(102,4,34,100.00,3,3,'2026-10-10 00:38:26','2026-10-10 00:39:29'),(103,4,35,100.00,3,3,'2026-10-14 22:11:35','2026-10-14 22:13:42'),(104,4,36,100.00,3,3,'2026-10-17 21:39:45','2026-10-17 21:40:35'),(105,4,37,100.00,3,3,'2026-10-22 01:34:06','2026-10-22 01:35:15'),(106,4,38,0.00,0,3,'2026-10-24 23:32:05','2026-10-24 23:33:44'),(107,4,38,100.00,3,3,'2026-10-24 23:40:36','2026-10-24 23:41:33'),(108,4,39,100.00,3,3,'2026-10-26 23:28:28','2026-10-26 23:30:27'),(109,4,40,100.00,3,3,'2026-10-29 20:19:54','2026-10-29 20:20:46'),(110,4,41,33.33,1,3,'2026-11-23 22:44:27','2026-11-23 22:46:15'),(111,4,48,100.00,4,4,'2026-11-08 23:06:59','2026-11-08 23:09:11'),(112,4,49,100.00,4,4,'2026-11-10 22:53:25','2026-11-10 22:54:15'),(113,4,50,100.00,4,4,'2026-11-13 02:08:59','2026-11-13 02:10:25'),(114,5,33,66.67,2,3,'2026-10-13 22:32:52','2026-10-13 22:33:59'),(115,5,34,66.67,2,3,'2026-10-17 22:26:51','2026-10-17 22:28:12'),(116,5,35,33.33,1,3,'2026-10-22 01:10:12','2026-10-22 01:12:15'),(117,5,35,100.00,3,3,'2026-10-22 01:20:45','2026-10-22 01:23:03'),(118,5,36,100.00,3,3,'2026-10-28 00:19:46','2026-10-28 00:22:01'),(119,5,37,33.33,1,3,'2026-10-31 22:51:34','2026-10-31 22:52:48'),(120,5,37,33.33,1,3,'2026-10-31 22:54:51','2026-10-31 22:55:53'),(121,5,37,66.67,2,3,'2026-10-31 23:02:20','2026-10-31 23:03:57'),(122,5,38,100.00,3,3,'2026-11-05 22:39:21','2026-11-05 22:41:21'),(123,5,48,75.00,3,4,'2026-11-10 00:18:24','2026-11-10 00:19:57'),(124,5,49,50.00,2,4,'2026-11-12 23:03:19','2026-11-12 23:05:11'),(125,5,49,100.00,4,4,'2026-11-12 23:11:18','2026-11-12 23:12:39'),(126,6,33,100.00,3,3,'2026-11-03 02:22:27','2026-11-03 02:24:00'),(127,6,34,33.33,1,3,'2026-11-06 22:52:16','2026-11-06 22:53:34'),(128,6,34,100.00,3,3,'2026-11-06 22:56:48','2026-11-06 22:59:10'),(129,6,35,100.00,3,3,'2026-11-10 23:08:50','2026-11-10 23:10:28'),(130,6,36,33.33,1,3,'2026-11-19 23:25:08','2026-11-19 23:26:18');
/*!40000 ALTER TABLE `quiz_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_responses`
--

DROP TABLE IF EXISTS `quiz_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_responses` (
  `response_id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  PRIMARY KEY (`response_id`),
  UNIQUE KEY `uq_attempt_question` (`attempt_id`,`question_id`),
  KEY `idx_responses_option` (`question_id`,`option_id`),
  CONSTRAINT `fk_responses_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_responses_option` FOREIGN KEY (`question_id`, `option_id`) REFERENCES `answer_options` (`question_id`, `option_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_responses`
--

LOCK TABLES `quiz_responses` WRITE;
/*!40000 ALTER TABLE `quiz_responses` DISABLE KEYS */;
INSERT INTO `quiz_responses` VALUES (1,1,1,1,0),(2,1,2,6,0),(3,1,3,11,0),(4,2,31,121,0),(5,2,32,131,0),(6,2,33,137,0),(7,2,34,144,0),(8,3,1,3,1),(9,3,2,5,0),(10,3,3,10,0),(11,4,1,3,1),(12,4,2,6,0),(13,4,3,9,1),(101,101,1,3,1),(102,101,2,8,1),(103,101,3,9,1),(104,102,4,16,1),(105,102,5,17,1),(106,102,6,22,1),(107,103,7,25,1),(108,103,8,30,1),(109,103,9,35,1),(110,104,10,38,1),(111,104,11,43,1),(112,104,12,48,1),(113,105,13,51,1),(114,105,14,56,1),(115,105,15,57,1),(116,106,16,61,0),(117,106,17,67,0),(118,106,18,71,0),(119,107,16,64,1),(120,107,17,65,1),(121,107,18,70,1),(122,108,19,73,1),(123,108,20,78,1),(124,108,21,83,1),(125,109,22,86,1),(126,109,23,91,1),(127,109,24,96,1),(128,110,25,97,0),(129,110,26,104,1),(130,110,27,107,0),(131,111,31,122,1),(132,111,32,130,1),(133,111,33,136,1),(134,111,34,143,1),(135,112,35,150,1),(136,112,36,158,1),(137,112,37,164,1),(138,112,38,172,1),(139,113,39,180,1),(140,113,40,185,1),(141,113,41,192,1),(142,113,42,200,1),(143,114,1,3,1),(144,114,2,5,0),(145,114,3,9,1),(146,115,4,16,1),(147,115,5,19,0),(148,115,6,22,1),(149,116,7,28,0),(150,116,8,32,0),(151,116,9,35,1),(152,117,7,25,1),(153,117,8,30,1),(154,117,9,35,1),(155,118,10,38,1),(156,118,11,43,1),(157,118,12,48,1),(158,119,13,52,0),(159,119,14,56,1),(160,119,15,59,0),(161,120,13,52,0),(162,120,14,54,0),(163,120,15,57,1),(164,121,13,51,1),(165,121,14,56,1),(166,121,15,58,0),(167,122,16,64,1),(168,122,17,65,1),(169,122,18,70,1),(170,123,31,122,1),(171,123,32,129,0),(172,123,33,136,1),(173,123,34,143,1),(174,124,35,150,1),(175,124,36,158,1),(176,124,37,165,0),(177,124,38,171,0),(178,125,35,150,1),(179,125,36,158,1),(180,125,37,164,1),(181,125,38,172,1),(182,126,1,3,1),(183,126,2,8,1),(184,126,3,9,1),(185,127,4,16,1),(186,127,5,20,0),(187,127,6,23,0),(188,128,4,16,1),(189,128,5,17,1),(190,128,6,22,1),(191,129,7,25,1),(192,129,8,30,1),(193,129,9,35,1),(194,130,10,38,1),(195,130,11,41,0),(196,130,12,46,0);
/*!40000 ALTER TABLE `quiz_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quizzes` (
  `quiz_id` int NOT NULL AUTO_INCREMENT,
  `lesson_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  PRIMARY KEY (`quiz_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quizzes`
--

LOCK TABLES `quizzes` WRITE;
/*!40000 ALTER TABLE `quizzes` DISABLE KEYS */;
INSERT INTO `quizzes` VALUES (33,29,'Welcome to Python Quiz'),(34,30,'Variables and Data Types Quiz'),(35,31,'Numbers and Math Quiz'),(36,32,'Working with Strings Quiz'),(37,33,'Input and Output Quiz'),(38,34,'Making Decisions Quiz'),(39,35,'Loops Quiz'),(40,36,'Lists Quiz'),(41,37,'Functions Quiz'),(42,38,'Dictionaries Quiz'),(48,39,'Setting Up Your Environment Quiz'),(49,40,'Your First Java Program Quiz'),(50,41,'Variables and Data Types Quiz'),(51,42,'Operators and Expressions Quiz'),(52,43,'Working with Strings Quiz'),(53,44,'Conditionals Quiz'),(54,45,'Loops Quiz'),(55,46,'Arrays Quiz'),(56,47,'Methods Quiz'),(57,48,'Classes and Objects Quiz');
/*!40000 ALTER TABLE `quizzes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role` enum('admin','student','parent') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Test','Admin','admin@test.com','$2y$12$LEuyD3erkM24f24/zwudG./axPEfgSE0dIZ7UzSoO9h0m61I.n8wm','2026-09-29 22:30:02'),(2,'student','Test','Student','student@test.com','$2y$12$daROkCEuqQjJcYit6a6ZROufjmQe3L5pssMTZFhjZ3HAiSRu6Pcwe','2026-09-29 22:30:02'),(3,'parent','Test','Parent','parent@test.com','$2y$12$CgRGFtlv4L8Kp.yqrhgFLugGA2ZH2S2JBI975SPPFhtPf2YQVaHMa','2026-09-29 22:30:02'),(4,'student','Maria','Gonzalez','maria.g@test.com','$2y$10$REPLACE_WITH_REAL_HASH','2026-09-29 22:48:51'),(5,'student','Kevin','Nguyen','kevin.n@test.com','$2y$10$REPLACE_WITH_REAL_HASH','2026-09-29 22:48:51'),(6,'student','Aisha','Bello','aisha.b@test.com','$2y$10$REPLACE_WITH_REAL_HASH','2026-09-29 22:48:51'),(7,'parent','Diane','Nguyen','diane.n@test.com','$2y$10$REPLACE_WITH_REAL_HASH','2026-09-29 22:48:51'),(8,'parent','Robert','Gonzalez','robert.g@test.com','$2y$10$REPLACE_WITH_REAL_HASH','2026-09-29 22:48:51'),(9,'parent','Brian','Bello','brian.b@test.com','$2y$12$kICqd2XiDRej0kIYatxDtOz3IiZldymPEMby8vvLU7U35MMp2kT/C','2026-11-25 00:14:47');
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

-- Dump completed on 2026-11-24 18:21:49
