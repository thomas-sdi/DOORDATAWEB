-- MySQL dump 10.13  Distrib 5.1.42, for Win32 (ia32)
--
-- Host: localhost    Database: doordata
-- ------------------------------------------------------
-- Server version	5.1.42-community

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
-- Table structure for table `audio`
--

DROP TABLE IF EXISTS `audio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audio` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) DEFAULT NULL,
  `AUDIO_FILE` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `CONTROL_NAME` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `INK_STROKES` longtext CHARACTER SET cp1251,
  `NOTE` text CHARACTER SET cp1251,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `DOOR_ID_2` (`DOOR_ID`,`AUDIO_FILE`,`CONTROL_NAME`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `audio_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `building`
--

DROP TABLE IF EXISTS `building`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `building` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `ADDRESS_1` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `ADDRESS_2` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `CITY` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `STATE` int(11) DEFAULT NULL,
  `COUNTRY` int(11) DEFAULT NULL,
  `ZIP` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `SUMMARY` text CHARACTER SET cp1251,
  `CUSTOMER_ID` int(11) DEFAULT NULL,
  `PRIMARY_CONTACT` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `STATE` (`STATE`),
  KEY `COUNTRY` (`COUNTRY`),
  KEY `CUSTOMER_ID` (`CUSTOMER_ID`),
  KEY `PRIMARY_CONTACT` (`PRIMARY_CONTACT`),
  CONSTRAINT `building_ibfk_1` FOREIGN KEY (`STATE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `building_ibfk_2` FOREIGN KEY (`COUNTRY`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `building_ibfk_3` FOREIGN KEY (`CUSTOMER_ID`) REFERENCES `company` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `building_ibfk_4` FOREIGN KEY (`PRIMARY_CONTACT`) REFERENCES `employee` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `ADDRESS_1` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `ADDRESS_2` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `CITY` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `STATE` int(11) DEFAULT NULL,
  `ZIP` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `TYPE` int(11) DEFAULT NULL,
  `INSPECTION_COMPANY` int(11) DEFAULT NULL,
  `PRIMARY_CONTACT` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `STATE` (`STATE`),
  KEY `TYPE` (`TYPE`),
  KEY `INSPECTION_COMPANY` (`INSPECTION_COMPANY`),
  KEY `PRIMARY_CONTACT` (`PRIMARY_CONTACT`),
  CONSTRAINT `company_ibfk_4` FOREIGN KEY (`INSPECTION_COMPANY`) REFERENCES `company` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_6` FOREIGN KEY (`STATE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_7` FOREIGN KEY (`TYPE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_8` FOREIGN KEY (`PRIMARY_CONTACT`) REFERENCES `employee` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CATEGORY` varchar(50) CHARACTER SET cp1251 NOT NULL DEFAULT '',
  `ITEM` varchar(50) CHARACTER SET cp1251 NOT NULL DEFAULT '',
  `DESCRIPTION` varchar(255) CHARACTER SET cp1251 DEFAULT NULL,
  `VALUE_ORDER` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `CATEGORY` (`CATEGORY`,`ITEM`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1384 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `door`
--

DROP TABLE IF EXISTS `door`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `door` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BUILDING_ID` int(11) DEFAULT NULL,
  `INSPECTION_ID` int(11) DEFAULT NULL,
  `INSPECTOR_ID` int(11) DEFAULT NULL,
  `NUMBER` varchar(20) CHARACTER SET cp1251 NOT NULL DEFAULT '',
  `DOOR_BARCODE` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `TYPE_OTHER` text CHARACTER SET cp1251,
  `STYLE` int(11) DEFAULT NULL,
  `MATERIAL` int(11) DEFAULT NULL,
  `MATERIAL_OTHER` text CHARACTER SET cp1251,
  `ELEVATION` int(11) DEFAULT NULL,
  `ELEVATION_OTHER` text CHARACTER SET cp1251,
  `FRAME_MATERIAL` int(11) DEFAULT NULL,
  `FRAME_MATERIAL_OTHER` text CHARACTER SET cp1251,
  `FRAME_ELEVATION` int(11) DEFAULT NULL,
  `FRAME_ELEVATION_OTHER` text CHARACTER SET cp1251,
  `LOCATION` text CHARACTER SET cp1251,
  `FIRE_RATING_1` int(11) DEFAULT NULL,
  `FIRE_RATING_2` int(11) DEFAULT NULL,
  `FIRE_RATING_3` int(11) DEFAULT NULL,
  `FIRE_RATING_4` int(11) DEFAULT NULL,
  `TEMP_RISE` int(11) DEFAULT NULL,
  `MANUFACTURER` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `BARCODE` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `REMARKS` text CHARACTER SET cp1251,
  `COMPLIANT` int(11) DEFAULT NULL,
  `MODEL` varchar(12) CHARACTER SET cp1251 DEFAULT NULL,
  `FRAME_MANUFACTURER` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `RFID` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `LISTING_AGENCY` int(11) DEFAULT NULL,
  `LISTING_AGENCY_OTHER` text CHARACTER SET cp1251,
  `GAUGE` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `HANDING` int(11) DEFAULT NULL,
  `INK_STROKES` longtext CHARACTER SET cp1251,
  `HINGE_HEIGHT` int(11) DEFAULT NULL,
  `HINGE_THICKNESS` int(11) DEFAULT NULL,
  `HINGE_HEIGHT1` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `HINGE_HEIGHT2` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `HINGE_HEIGHT3` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `HINGE_HEIGHT4` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `HINGE_FRACTION1` int(11) DEFAULT NULL,
  `HINGE_FRACTION2` int(11) DEFAULT NULL,
  `HINGE_FRACTION3` int(11) DEFAULT NULL,
  `HINGE_FRACTION4` int(11) DEFAULT NULL,
  `HINGE_BACKSET` int(11) DEFAULT NULL,
  `HINGE_MANUFACTURER` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `HINGE_MANUFACTURER_NO` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `TOP_TO_CENTERLINE` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `TOP_TO_CENTERLINE_FRACTION` int(11) DEFAULT NULL,
  `LOCK_BACKSET` int(11) DEFAULT NULL,
  `FRAME_BOTTOM_TO_CENTER` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `STRIKE_HEIGHT` int(11) DEFAULT NULL,
  `PREFIT_DOOR_SIZE_X` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `PREFIT_FRACTION_X` int(11) DEFAULT NULL,
  `PREFIT_DOOR_SIZE_Y` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `PREFIT_FRACTION_Y` int(11) DEFAULT NULL,
  `FRAME_OPENING_SIZE_X` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `FRAME_OPENING_FRACTION_X` int(11) DEFAULT NULL,
  `FRAME_OPENING_SIZE_Y` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `FRAME_OPENING_FRACTION_Y` int(11) DEFAULT NULL,
  `LITE_CUTOUT_SIZE_X` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `LITE_CUTOUT_FRACTION_X` int(11) DEFAULT NULL,
  `LITE_CUTOUT_SIZE_Y` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `LITE_CUTOUT_FRACTION_Y` int(11) DEFAULT NULL,
  `LOCKSTILE_SIZE` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `LOCKSTILE_FRACTION` int(11) DEFAULT NULL,
  `TOPRAIL_SIZE` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `TOPRAIL_FRACTION` int(11) DEFAULT NULL,
  `FRAME_INK_STROKES` text CHARACTER SET cp1251,
  `A` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `A_FRACTION` int(11) DEFAULT NULL,
  `B` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `B_FRACTION` int(11) DEFAULT NULL,
  `C` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `C_FRACTION` int(11) DEFAULT NULL,
  `D` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `D_FRACTION` int(11) DEFAULT NULL,
  `E` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `E_FRACTION` int(11) DEFAULT NULL,
  `F` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `F_FRACTION` int(11) DEFAULT NULL,
  `G` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `G_FRACTION` int(11) DEFAULT NULL,
  `H` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `H_FRACTION` int(11) DEFAULT NULL,
  `I` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `I_FRACTION` int(11) DEFAULT NULL,
  `J` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `J_FRACTION` int(11) DEFAULT NULL,
  `K` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `K_FRACTION` int(11) DEFAULT NULL,
  `L` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `L_FRACTION` int(11) DEFAULT NULL,
  `M` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `M_FRACTION` int(11) DEFAULT NULL,
  `N` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `N_FRACTION` int(11) DEFAULT NULL,
  `O` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `O_FRACTION` int(11) DEFAULT NULL,
  `P` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `P_FRACTION` int(11) DEFAULT NULL,
  `Q` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `Q_FRACTION` int(11) DEFAULT NULL,
  `R` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `R_FRACTION` int(11) DEFAULT NULL,
  `S` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `S_FRACTION` int(11) DEFAULT NULL,
  `T` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `T_FRACTION` int(11) DEFAULT NULL,
  `U` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `U_FRACTION` int(11) DEFAULT NULL,
  `V` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `V_FRACTION` int(11) DEFAULT NULL,
  `HARDWARE_GROUP` varchar(28) CHARACTER SET cp1251 DEFAULT NULL,
  `HARDWARE_SET` varchar(42) CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `NUMBER_INSPECTION` (`NUMBER`,`INSPECTION_ID`),
  KEY `DOOR_MATERIAL` (`MATERIAL`),
  KEY `DOOR_ELEV` (`ELEVATION`),
  KEY `FRAME_ELEV` (`FRAME_ELEVATION`),
  KEY `FIRE_RATING_2` (`FIRE_RATING_2`),
  KEY `FIRE_RATING_1` (`FIRE_RATING_1`),
  KEY `FIRE_RATING_3` (`FIRE_RATING_3`),
  KEY `FIRE_RATING_4` (`FIRE_RATING_4`),
  KEY `STYLE` (`STYLE`),
  KEY `TEMP_RISE` (`TEMP_RISE`),
  KEY `FRAME_MATERIAL` (`FRAME_MATERIAL`),
  KEY `BUILDING_ID` (`BUILDING_ID`),
  KEY `LISTING_AGENCY` (`LISTING_AGENCY`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  KEY `INSPECTOR_ID` (`INSPECTOR_ID`),
  KEY `HANDING` (`HANDING`),
  KEY `door_ibfk_48` (`HINGE_HEIGHT`),
  KEY `door_ibfk_49` (`HINGE_THICKNESS`),
  KEY `door_ibfk_50` (`HINGE_FRACTION1`),
  KEY `door_ibfk_51` (`HINGE_FRACTION2`),
  KEY `door_ibfk_52` (`HINGE_FRACTION3`),
  KEY `door_ibfk_53` (`HINGE_FRACTION4`),
  KEY `door_ibfk_54` (`HINGE_BACKSET`),
  KEY `door_ibfk_55` (`TOP_TO_CENTERLINE_FRACTION`),
  KEY `door_ibfk_56` (`STRIKE_HEIGHT`),
  KEY `door_ibfk_57` (`PREFIT_FRACTION_X`),
  KEY `door_ibfk_58` (`PREFIT_FRACTION_Y`),
  KEY `door_ibfk_59` (`FRAME_OPENING_FRACTION_X`),
  KEY `door_ibfk_60` (`FRAME_OPENING_FRACTION_Y`),
  KEY `door_ibfk_61` (`LITE_CUTOUT_FRACTION_X`),
  KEY `door_ibfk_62` (`LITE_CUTOUT_FRACTION_Y`),
  KEY `door_ibfk_63` (`LOCKSTILE_FRACTION`),
  KEY `door_ibfk_64` (`TOPRAIL_FRACTION`),
  KEY `LOCK_BACKSET` (`LOCK_BACKSET`),
  KEY `COMPLIANT` (`COMPLIANT`),
  KEY `A_FRACTION` (`A_FRACTION`),
  KEY `B_FRACTION` (`B_FRACTION`),
  KEY `C_FRACTION` (`C_FRACTION`),
  KEY `D_FRACTION` (`D_FRACTION`),
  KEY `E_FRACTION` (`E_FRACTION`),
  KEY `F_FRACTION` (`F_FRACTION`),
  KEY `G_FRACTION` (`G_FRACTION`),
  KEY `H_FRACTION` (`H_FRACTION`),
  KEY `I_FRACTION` (`I_FRACTION`),
  KEY `J_FRACTION` (`J_FRACTION`),
  KEY `K_FRACTION` (`K_FRACTION`),
  KEY `L_FRACTION` (`L_FRACTION`),
  KEY `M_FRACTION` (`M_FRACTION`),
  KEY `N_FRACTION` (`N_FRACTION`),
  KEY `O_FRACTION` (`O_FRACTION`),
  KEY `P_FRACTION` (`P_FRACTION`),
  KEY `Q_FRACTION` (`Q_FRACTION`),
  KEY `R_FRACTION` (`R_FRACTION`),
  KEY `S_FRACTION` (`S_FRACTION`),
  KEY `T_FRACTION` (`T_FRACTION`),
  KEY `U_FRACTION` (`U_FRACTION`),
  KEY `V_FRACTION` (`V_FRACTION`),
  CONSTRAINT `door_ibfk_1` FOREIGN KEY (`FIRE_RATING_2`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_10` FOREIGN KEY (`FRAME_MATERIAL`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_11` FOREIGN KEY (`LISTING_AGENCY`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_12` FOREIGN KEY (`INSPECTOR_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_13` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_14` FOREIGN KEY (`BUILDING_ID`) REFERENCES `building` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_15` FOREIGN KEY (`HANDING`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_16` FOREIGN KEY (`HINGE_HEIGHT`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_17` FOREIGN KEY (`HINGE_THICKNESS`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_18` FOREIGN KEY (`HINGE_FRACTION1`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_19` FOREIGN KEY (`HINGE_FRACTION2`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_2` FOREIGN KEY (`FIRE_RATING_1`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_20` FOREIGN KEY (`HINGE_FRACTION3`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_21` FOREIGN KEY (`HINGE_FRACTION4`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_22` FOREIGN KEY (`HINGE_BACKSET`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_23` FOREIGN KEY (`TOP_TO_CENTERLINE_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_24` FOREIGN KEY (`STRIKE_HEIGHT`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_25` FOREIGN KEY (`PREFIT_FRACTION_X`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_26` FOREIGN KEY (`PREFIT_FRACTION_Y`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_27` FOREIGN KEY (`FRAME_OPENING_FRACTION_X`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_28` FOREIGN KEY (`FRAME_OPENING_FRACTION_Y`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_29` FOREIGN KEY (`LITE_CUTOUT_FRACTION_X`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_3` FOREIGN KEY (`FIRE_RATING_3`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_30` FOREIGN KEY (`LITE_CUTOUT_FRACTION_Y`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_31` FOREIGN KEY (`LOCKSTILE_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_32` FOREIGN KEY (`TOPRAIL_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_33` FOREIGN KEY (`LOCK_BACKSET`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_34` FOREIGN KEY (`COMPLIANT`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_35` FOREIGN KEY (`A_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_36` FOREIGN KEY (`B_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_37` FOREIGN KEY (`C_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_38` FOREIGN KEY (`D_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_39` FOREIGN KEY (`E_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_4` FOREIGN KEY (`FIRE_RATING_4`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_40` FOREIGN KEY (`F_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_41` FOREIGN KEY (`G_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_42` FOREIGN KEY (`H_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_43` FOREIGN KEY (`I_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_44` FOREIGN KEY (`J_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_45` FOREIGN KEY (`K_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_46` FOREIGN KEY (`L_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_47` FOREIGN KEY (`M_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_48` FOREIGN KEY (`N_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_49` FOREIGN KEY (`O_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_5` FOREIGN KEY (`MATERIAL`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_50` FOREIGN KEY (`P_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_51` FOREIGN KEY (`Q_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_52` FOREIGN KEY (`R_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_53` FOREIGN KEY (`S_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_54` FOREIGN KEY (`T_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_55` FOREIGN KEY (`U_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_56` FOREIGN KEY (`V_FRACTION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_6` FOREIGN KEY (`ELEVATION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_7` FOREIGN KEY (`FRAME_ELEVATION`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_8` FOREIGN KEY (`STYLE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_ibfk_9` FOREIGN KEY (`TEMP_RISE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `door_code`
--

DROP TABLE IF EXISTS `door_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `door_code` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `CODE_ID` int(11) NOT NULL DEFAULT '0',
  `ACTIVE` tinyint(1) DEFAULT NULL,
  `CONTROL_NAME` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `DOOR_ID` (`DOOR_ID`,`CODE_ID`),
  KEY `CODE_ID` (`CODE_ID`),
  CONSTRAINT `door_code_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_code_ibfk_2` FOREIGN KEY (`CODE_ID`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `door_note`
--

DROP TABLE IF EXISTS `door_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `door_note` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `NOTE` longtext CHARACTER SET cp1251,
  `CONTROL_NAME` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `door_note_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `door_type`
--

DROP TABLE IF EXISTS `door_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `door_type` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `TYPE_ID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  KEY `TYPE_ID` (`TYPE_ID`),
  CONSTRAINT `door_type_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `door_type_ibfk_2` FOREIGN KEY (`TYPE_ID`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `download`
--

DROP TABLE IF EXISTS `download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `download` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EMAIL` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `HASH` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `CREATE_DATE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DOWNLOADED` int(11) NOT NULL DEFAULT '136',
  PRIMARY KEY (`ID`),
  KEY `DOWNLOADED` (`DOWNLOADED`),
  CONSTRAINT `download_ibfk_1` FOREIGN KEY (`DOWNLOADED`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email`
--

DROP TABLE IF EXISTS `email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identity` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `subject` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `message` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRST_NAME` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `LAST_NAME` varchar(50) CHARACTER SET cp1251 NOT NULL DEFAULT '',
  `LAST_LOGIN` date DEFAULT NULL,
  `LICENSE_NUMBER` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `EXPIRATION_DATE` date DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `COMPANY_ID` int(11) DEFAULT NULL,
  `EMAIL` varchar(255) CHARACTER SET cp1251 DEFAULT NULL,
  `PHONE` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `USER_ID` (`USER_ID`),
  KEY `COMPANY_ID` (`COMPANY_ID`),
  KEY `ID_2` (`ID`),
  KEY `ID_3` (`ID`),
  CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`COMPANY_ID`) REFERENCES `company` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `floorplan`
--

DROP TABLE IF EXISTS `floorplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `floorplan` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `INK_STROKES` longtext CHARACTER SET cp1251,
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `floorplan_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `framedetail`
--

DROP TABLE IF EXISTS `framedetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `framedetail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `INK_STROKES` longtext CHARACTER SET cp1251,
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `framedetail_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hardware`
--

DROP TABLE IF EXISTS `hardware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hardware` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `ITEM_ID` int(11) DEFAULT NULL,
  `VERIFY` varchar(20) CHARACTER SET cp1251 DEFAULT NULL,
  `QTY` varchar(10) CHARACTER SET cp1251 DEFAULT NULL,
  `ITEM` varchar(42) CHARACTER SET cp1251 DEFAULT NULL,
  `PRODUCT` varchar(63) CHARACTER SET cp1251 DEFAULT NULL,
  `MFG` char(10) CHARACTER SET cp1251 DEFAULT NULL,
  `FINISH` char(10) CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `hardware_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ink`
--

DROP TABLE IF EXISTS `ink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ink` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) NOT NULL DEFAULT '0',
  `INK_STROKE` longtext CHARACTER SET cp1251,
  `FORM_NUM` int(11) DEFAULT NULL,
  `CONTROL_NAME` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `ink_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inspect`
--

DROP TABLE IF EXISTS `inspect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspect` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `INSPECTOR_ID` int(11) NOT NULL DEFAULT '0',
  `INSPECTION_ID` int(11) NOT NULL DEFAULT '0',
  `ASSIGNED_DATE` date DEFAULT NULL,
  `COMMENTS` text CHARACTER SET cp1251,
  PRIMARY KEY (`ID`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  KEY `INSPECTOR_ID` (`INSPECTOR_ID`,`INSPECTION_ID`),
  CONSTRAINT `inspect_ibfk_1` FOREIGN KEY (`INSPECTOR_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspect_ibfk_2` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inspection`
--

DROP TABLE IF EXISTS `inspection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspection` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `INSPECTION_DATE` date DEFAULT NULL,
  `INSPECTION_COMPLETE_DATE` date DEFAULT NULL,
  `REINSPECT_DATE` date DEFAULT NULL,
  `BUILDING_ID` int(11) NOT NULL DEFAULT '0',
  `COMPANY_ID` int(11) NOT NULL DEFAULT '0',
  `SIGNATURE_INSPECTOR` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `SIGNATURE_STROKES_INSPECTOR` longtext CHARACTER SET cp1251,
  `SIGNATURE_BUILDING` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `SIGNATURE_STROKES_BUILDING` longtext CHARACTER SET cp1251,
  `STATUS` int(11) DEFAULT NULL,
  `SUMMARY` text CHARACTER SET cp1251,
  `PDF` varchar(255) CHARACTER SET cp1251 DEFAULT NULL,
  `INSPECTOR_ID` int(11) DEFAULT NULL,
  `TEMPLATE_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UI_ID` (`ID`),
  KEY `FK_BUILDING_ID` (`BUILDING_ID`),
  KEY `STATUS` (`STATUS`),
  KEY `COMPANY_ID` (`COMPANY_ID`),
  KEY `TEMPLATE_ID` (`TEMPLATE_ID`),
  CONSTRAINT `inspection_ibfk_1` FOREIGN KEY (`STATUS`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_2` FOREIGN KEY (`BUILDING_ID`) REFERENCES `building` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_3` FOREIGN KEY (`COMPANY_ID`) REFERENCES `company` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_4` FOREIGN KEY (`TEMPLATE_ID`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inspection_other`
--

DROP TABLE IF EXISTS `inspection_other`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspection_other` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `INSPECTION_ID` int(11) NOT NULL DEFAULT '0',
  `OTHER_ID` int(11) DEFAULT NULL,
  `OTHER_VALUE` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `inspection_id` (`INSPECTION_ID`),
  KEY `other_id` (`OTHER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `integration`
--

DROP TABLE IF EXISTS `integration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `integration` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATE` date DEFAULT NULL,
  `TIME` time DEFAULT NULL,
  `INSPECTION_ID` int(11) NOT NULL DEFAULT '0',
  `TYPE` int(11) DEFAULT NULL,
  `REQUEST` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `RESPONSE` longtext CHARACTER SET latin1,
  `STATUS` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `TYPE` (`TYPE`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  KEY `STATUS` (`STATUS`),
  CONSTRAINT `integration_ibfk_1` FOREIGN KEY (`TYPE`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `integration_ibfk_2` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `integration_ibfk_3` FOREIGN KEY (`STATUS`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `photobucket`
--

DROP TABLE IF EXISTS `photobucket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photobucket` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `INSPECTION_ID` int(11) NOT NULL DEFAULT '0',
  `URL` text CHARACTER SET latin1 NOT NULL,
  `NAME` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `DESCRIPTION` text CHARACTER SET latin1,
  `IMAGE` text CHARACTER SET latin1,
  PRIMARY KEY (`ID`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  CONSTRAINT `photobucket_ibfk_3` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `picture`
--

DROP TABLE IF EXISTS `picture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `picture` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOOR_ID` int(11) DEFAULT NULL,
  `PICTURE_FILE` varchar(100) CHARACTER SET cp1251 DEFAULT NULL,
  `CONTROL_NAME` varchar(50) CHARACTER SET cp1251 DEFAULT NULL,
  `ROTATION` int(11) DEFAULT NULL,
  `INK_STROKES` longtext CHARACTER SET cp1251,
  `NOTE` text CHARACTER SET cp1251,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UI_ID` (`ID`),
  UNIQUE KEY `DOOR_ID` (`DOOR_ID`,`PICTURE_FILE`,`CONTROL_NAME`),
  KEY `FK_DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `picture_ibfk_1` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `ID` int(19) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(40) NOT NULL DEFAULT '',
  `PARENT_ROLE_ID` int(19) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `PARENT_ROLE_ID` (`PARENT_ROLE_ID`),
  CONSTRAINT `role_ibfk_1` FOREIGN KEY (`PARENT_ROLE_ID`) REFERENCES `role` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `ID` int(19) NOT NULL AUTO_INCREMENT,
  `LOGIN` varbinary(40) NOT NULL DEFAULT '',
  `PASSWORD` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `id` (`ID`),
  UNIQUE KEY `login` (`LOGIN`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_file`
--

DROP TABLE IF EXISTS `user_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_file` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) DEFAULT NULL,
  `FILE_NAME` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `FILE_SIZE` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `ADDED_ON` date DEFAULT NULL,
  `DESCRIPTION` text CHARACTER SET latin1,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USER_ID_2` (`USER_ID`,`FILE_NAME`),
  KEY `USER_ID` (`USER_ID`),
  CONSTRAINT `user_file_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `ID` int(19) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(19) NOT NULL DEFAULT '0',
  `ROLE_ID` int(19) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `user_id` (`USER_ID`,`ROLE_ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `role_id` (`ROLE_ID`),
  KEY `user_role_ibfk_3` (`USER_ID`),
  KEY `user_role_ibfk_4` (`ROLE_ID`),
  CONSTRAINT `user_role_ibfk_3` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_role_ibfk_4` FOREIGN KEY (`ROLE_ID`) REFERENCES `role` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=cp1250;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-24 22:06:36
