-- MySQL dump 10.13  Distrib 5.1.58, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: tracon
-- ------------------------------------------------------
-- Server version	5.1.58-1

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
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opt` tinytext NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,'show_tv',0),(2,'show_stream',1);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diat`
--

DROP TABLE IF EXISTS `diat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diat` (
  `dia_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tunniste` tinytext NOT NULL,
  `data` text NOT NULL,
  `jarjestys` smallint(6) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`dia_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `frontends`
--

DROP TABLE IF EXISTS `frontends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frontends` (
  `f_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tunniste` tinytext NOT NULL,
  `uuid` varchar(37) NOT NULL,
  `last_active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `show_tv` int(11) NOT NULL,
  `show_stream` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `use_global` tinyint(1) NOT NULL DEFAULT '1',
  `salt` text NOT NULL,
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `kayttajat`
--

DROP TABLE IF EXISTS `kayttajat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kayttajat` (
  `u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kayttis` tinytext NOT NULL,
  `passu` tinytext NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`u_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kayttajat`
--

LOCK TABLES `kayttajat` WRITE;
/*!40000 ALTER TABLE `kayttajat` DISABLE KEYS */;
INSERT INTO `kayttajat` VALUES (1,'siikakala','f0af89c9a5586cb06a024a76a2b2b436e1545ec8',3),(2,'vieras','2f7b1cb39e386a75ce53299845e26e723d865646',3);
/*!40000 ALTER TABLE `kayttajat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ohjelmadata`
--

DROP TABLE IF EXISTS `ohjelmadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ohjelmadata` (
  `o_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paiva` tinytext NOT NULL,
  `sali` tinytext NOT NULL,
  `alku` int(11) NOT NULL,
  `kesto` int(11) NOT NULL,
  `nimi` tinytext NOT NULL,
  `jarjestaja` tinytext NOT NULL,
  `tyyppi` tinytext NOT NULL,
  `kuvaus` text NOT NULL,
  `update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`o_id`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `rulla`
--

DROP TABLE IF EXISTS `rulla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rulla` (
  `rul_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pos` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `selector` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`rul_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `scroller`
--

DROP TABLE IF EXISTS `scroller`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scroller` (
  `scroll_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pos` int(11) NOT NULL,
  `text` tinytext NOT NULL,
  `style` tinytext NOT NULL,
  `set` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`scroll_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `streamit`
--

DROP TABLE IF EXISTS `streamit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streamit` (
  `stream_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tunniste` tinytext NOT NULL,
  `url` tinytext NOT NULL,
  `selite` tinytext NOT NULL,
  `jarjestys` int(11) NOT NULL,
  PRIMARY KEY (`stream_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-08-14 23:10:23
