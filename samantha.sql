-- MySQL dump 10.13  Distrib 5.1.49, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: samantha
-- ------------------------------------------------------
-- Server version	5.1.49-3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `infobot`
--

DROP TABLE IF EXISTS `infobot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `infobot` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `date` int(10) NOT NULL,
  `setby` varchar(255) NOT NULL,
  `locked` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `infobot`
--

LOCK TABLES `infobot` WRITE;
/*!40000 ALTER TABLE `infobot` DISABLE KEYS */;
INSERT INTO `infobot` VALUES (1,'pills','are mint',1307828666,'Ricki',0),(4,'acora ssh','is git@github.com:ircnode/acorairc.git',1307890642,'Ricki',0),(5,'acora git','is https://github.com/ircnode/acorairc',1307890669,'Ricki',0),(6,'acora wiki','is https://github.com/ircnode/acorairc/wiki',1307898260,'Ricki',0),(7,'acora issues','is https://github.com/ircnode/acorairc/issues',1307898282,'Ricki',0),(8,'ircnode','is the best irc network evar',1307955975,'Huru',0),(10,'Three','is Two boobs. Bowling Ball. Wig.',1307956049,'Huru',0),(11,'acora todo','is https://github.com/ircnode/acorairc/blob/dev/TODO',1307994103,'Ricki',0),(18,'wiki','is http://en.wikipedia.org/wiki/<wiki_encode>',1308402153,'Ricki',1),(19,'php','is http://uk.php.net/<wiki_encode>',1308402313,'Ricki',1),(21,'c++ stl','is http://www.cplusplus.com/reference/stl/<wiki_encode>',1308402423,'Ricki',1),(22,'c++ c','is http://www.cplusplus.com/reference/clibrary/<wiki_encode>',1308402446,'Ricki',1),(23,'c++ io','is http://www.cplusplus.com/reference/iostream/<wiki_encode>',1308402469,'Ricki',1),(24,'c++ string','is http://www.cplusplus.com/reference/string/<wiki_encode>',1308402488,'Ricki',1),(25,'c++ algorithm','is http://www.cplusplus.com/reference/algorithm/<wiki_encode>',1308402518,'Ricki',1),(26,'c++ std','is http://www.cplusplus.com/reference/std/<wiki_encode>',1308402539,'Ricki',1),(27,'ircnode git','is http://github.com/ircnode',1308422409,'Ricki',1),(29,'tar help','is http://www.computerhope.com/unix/utar.htm',1308423858,'Ricki',0);
/*!40000 ALTER TABLE `infobot` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-06-19 10:33:54
