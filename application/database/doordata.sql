/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50045
Source Host           : localhost:3306
Source Database       : doordata

Target Server Type    : MYSQL
Target Server Version : 50045
File Encoding         : 65001

Date: 2012-11-16 19:16:09
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `audio`
-- ----------------------------
DROP TABLE IF EXISTS `audio`;
CREATE TABLE `audio` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) default NULL,
  `AUDIO_FILE` varchar(100) default NULL,
  `CONTROL_NAME` varchar(50) default NULL,
  `INK_STROKES` longtext,
  `NOTE` text,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `DOOR_ID_2` (`DOOR_ID`,`AUDIO_FILE`,`CONTROL_NAME`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `audio_ibfk_7` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of audio
-- ----------------------------
INSERT INTO `audio` VALUES ('1', '1', null, null, null, '');
INSERT INTO `audio` VALUES ('2', '4', null, null, null, '');
INSERT INTO `audio` VALUES ('4', '7', null, null, null, '');
INSERT INTO `audio` VALUES ('5', '7', null, null, null, '');
INSERT INTO `audio` VALUES ('8', '11', null, null, null, '');
INSERT INTO `audio` VALUES ('9', '17', 'audio_door_17_20121114193633.', null, null, '');
INSERT INTO `audio` VALUES ('10', '18', 'audio_door_18_20121114193633.', null, null, '');
INSERT INTO `audio` VALUES ('11', '19', 'audio_door_19_20121114193633.', null, null, '');
INSERT INTO `audio` VALUES ('12', '19', 'audio_door_19_20121114193633.', null, null, '');
INSERT INTO `audio` VALUES ('13', '20', 'audio_door_20_20121114193633.', null, null, '');
INSERT INTO `audio` VALUES ('14', '21', 'audio_door_21_20121114193640.', null, null, '');
INSERT INTO `audio` VALUES ('15', '22', 'audio_door_22_20121114193640.', null, null, '');
INSERT INTO `audio` VALUES ('16', '23', 'audio_door_23_20121114193640.', null, null, '');
INSERT INTO `audio` VALUES ('17', '23', 'audio_door_23_20121114193640.', null, null, '');
INSERT INTO `audio` VALUES ('18', '24', 'audio_door_24_20121114193640.', null, null, '');
INSERT INTO `audio` VALUES ('19', '25', 'audio_door_25_20121114193830.', null, null, '');
INSERT INTO `audio` VALUES ('20', '26', 'audio_door_26_20121114193830.', null, null, '');
INSERT INTO `audio` VALUES ('21', '26', 'audio_door_26_20121114193830.', null, null, '');
INSERT INTO `audio` VALUES ('22', '27', 'audio_door_27_20121114193843.', null, null, '');
INSERT INTO `audio` VALUES ('23', '28', 'audio_door_28_20121114193843.', null, null, '');
INSERT INTO `audio` VALUES ('24', '28', 'audio_door_28_20121114193843.', null, null, '');

-- ----------------------------
-- Table structure for `building`
-- ----------------------------
DROP TABLE IF EXISTS `building`;
CREATE TABLE `building` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` varchar(100) default NULL,
  `ADDRESS_1` varchar(100) default NULL,
  `ADDRESS_2` varchar(100) default NULL,
  `CITY` varchar(50) default NULL,
  `STATE` int(11) default NULL,
  `COUNTRY` int(11) default NULL,
  `ZIP` varchar(10) default NULL,
  `SUMMARY` text,
  `CUSTOMER_ID` int(11) default NULL,
  `PRIMARY_CONTACT` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `STATE` (`STATE`),
  KEY `COUNTRY` (`COUNTRY`),
  KEY `CUSTOMER_ID` (`CUSTOMER_ID`),
  KEY `PRIMARY_CONTACT` (`PRIMARY_CONTACT`),
  CONSTRAINT `building_ibfk_2` FOREIGN KEY (`STATE`) REFERENCES `dictionary` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `building_ibfk_3` FOREIGN KEY (`COUNTRY`) REFERENCES `dictionary` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `building_ibfk_4` FOREIGN KEY (`CUSTOMER_ID`) REFERENCES `company` (`ID`),
  CONSTRAINT `building_ibfk_5` FOREIGN KEY (`PRIMARY_CONTACT`) REFERENCES `employee` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of building
-- ----------------------------
INSERT INTO `building` VALUES ('5', 'Chris Building', '123 Memory Lane', null, 'Myrtle Beach', '949', '999', '27000', 'Summary of Chris Building', '1', null);
INSERT INTO `building` VALUES ('10', 'Kung Fu Building', '56 Rodger st.', null, null, null, null, null, null, '6', null);
INSERT INTO `building` VALUES ('11', 'Green Hills', '43 4th st SW', null, 'NY', '982', null, '90210', 'inspection summary (building)<br />', '8', null);
INSERT INTO `building` VALUES ('15', 'Empire Building', null, null, 'NY', '950', '999', null, null, '9', null);
INSERT INTO `building` VALUES ('16', 'Empire Building 2', null, null, null, null, null, null, null, '9', null);

-- ----------------------------
-- Table structure for `company`
-- ----------------------------
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` varchar(100) default NULL,
  `ADDRESS_1` varchar(100) default NULL,
  `ADDRESS_2` varchar(100) default NULL,
  `CITY` varchar(50) default NULL,
  `STATE` int(11) default NULL,
  `ZIP` varchar(10) default NULL,
  `TYPE` int(11) default NULL,
  `INSPECTION_COMPANY` int(11) default NULL,
  `PRIMARY_CONTACT` int(11) default NULL,
  `BRANDING` int(1) default '0',
  `LOGO_FILE` varchar(100) default NULL,
  `COLOR_THEME` int(1) default '0',
  PRIMARY KEY  (`ID`),
  KEY `STATE` (`STATE`),
  KEY `TYPE` (`TYPE`),
  KEY `INSPECTION_COMPANY` (`INSPECTION_COMPANY`),
  KEY `PRIMARY_CONTACT` (`PRIMARY_CONTACT`),
  CONSTRAINT `company_ibfk_2` FOREIGN KEY (`STATE`) REFERENCES `dictionary` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_3` FOREIGN KEY (`TYPE`) REFERENCES `dictionary` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_4` FOREIGN KEY (`INSPECTION_COMPANY`) REFERENCES `company` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `company_ibfk_5` FOREIGN KEY (`PRIMARY_CONTACT`) REFERENCES `employee` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of company
-- ----------------------------
INSERT INTO `company` VALUES ('1', 'System', 'Administrator', null, null, null, null, '1001', '7', null, '0', null, '0');
INSERT INTO `company` VALUES ('5', 'XYZ Inspectors', null, null, null, null, null, '1002', null, null, '3', null, '0');
INSERT INTO `company` VALUES ('6', 'Kung Fu Master', 'China st. 12-12', null, 'Pekin', '965', '42222', '1001', '5', null, '0', null, '0');
INSERT INTO `company` VALUES ('7', 'ABC Inspectors', '555 5th st ne', 'office 5', '', null, '', '1002', null, null, '3', '/doordata/public/logos/inspectionLogo7_1352823310.png', '1');
INSERT INTO `company` VALUES ('8', 'Customer Company', null, null, null, null, null, '1001', '7', null, '0', null, '0');
INSERT INTO `company` VALUES ('9', 'Hakuna Matata', null, null, null, '961', null, '1001', '7', null, '0', null, '0');
INSERT INTO `company` VALUES ('10', 'BBC Inspectors', null, null, null, null, null, '1002', null, null, '0', null, '0');
INSERT INTO `company` VALUES ('11', 'Unknown Building Owner', null, null, null, null, null, '1001', '10', null, '0', null, '0');

-- ----------------------------
-- Table structure for `dictionary`
-- ----------------------------
DROP TABLE IF EXISTS `dictionary`;
CREATE TABLE `dictionary` (
  `ID` int(11) NOT NULL auto_increment,
  `CATEGORY` varchar(50) NOT NULL,
  `ITEM` varchar(50) NOT NULL,
  `DESCRIPTION` varchar(255) default NULL,
  `VALUE_ORDER` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `CATEGORY` (`CATEGORY`,`ITEM`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1393 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of dictionary
-- ----------------------------
INSERT INTO `dictionary` VALUES ('1', 'Frame', 'F1', 'Loose Frame', '1');
INSERT INTO `dictionary` VALUES ('2', 'Frame', 'F2', 'Damaged Frame', '2');
INSERT INTO `dictionary` VALUES ('3', 'Frame', 'F3', 'Rust-though on Frame', '3');
INSERT INTO `dictionary` VALUES ('4', 'Frame', 'F4', 'Missing Label', '4');
INSERT INTO `dictionary` VALUES ('5', 'Frame', 'F5', 'Frame is Out of alignment', '5');
INSERT INTO `dictionary` VALUES ('6', 'Frame', 'F6', 'Incorrect Glass in Sidelight or Transom-light', '6');
INSERT INTO `dictionary` VALUES ('7', 'Frame', 'F7', 'Broken Glass in Sidelight or Transom-light', '7');
INSERT INTO `dictionary` VALUES ('8', 'Frame', 'F8', 'Missing Glazing Bead at Light(s)', '8');
INSERT INTO `dictionary` VALUES ('9', 'Frame', 'F9', 'Missing Glazing Bead Screws', '9');
INSERT INTO `dictionary` VALUES ('10', 'Frame', 'F10', 'Improper Field Modification', '10');
INSERT INTO `dictionary` VALUES ('11', 'Frame', 'F11', 'Incorrect Hardware Preparation (Explain)', '11');
INSERT INTO `dictionary` VALUES ('12', 'Frame', 'F12', 'Unused Fastener Hole(s) in Frame', '12');
INSERT INTO `dictionary` VALUES ('13', 'Frame', 'F13', 'Other ', '13');
INSERT INTO `dictionary` VALUES ('14', 'Frame', 'F14', 'Other', '14');
INSERT INTO `dictionary` VALUES ('15', 'Frame', 'F15', 'Other', '15');
INSERT INTO `dictionary` VALUES ('16', 'Frame', 'F16', 'Other', '16');
INSERT INTO `dictionary` VALUES ('17', 'Door', 'D1', 'Missing Door(s)', '1');
INSERT INTO `dictionary` VALUES ('18', 'Door', 'D2', 'Missing Label', '2');
INSERT INTO `dictionary` VALUES ('19', 'Door', 'D3', 'Damaged Door(s)', '3');
INSERT INTO `dictionary` VALUES ('20', 'Door', 'D4', 'Rust-through on Door(s)', '4');
INSERT INTO `dictionary` VALUES ('21', 'Door', 'D5', 'Delamination of Door Skin or Face', '5');
INSERT INTO `dictionary` VALUES ('22', 'Door', 'D6', 'Incorrect Glass in Light(s)', '6');
INSERT INTO `dictionary` VALUES ('23', 'Door', 'D7', 'Broken Glass in Light(s)', '7');
INSERT INTO `dictionary` VALUES ('24', 'Door', 'D8', 'Light(s) is/are Too Large', '8');
INSERT INTO `dictionary` VALUES ('25', 'Door', 'D9', 'Loose Light Kits', '9');
INSERT INTO `dictionary` VALUES ('26', 'Door', 'D10', 'Missing Light Kit Screw(s)', '10');
INSERT INTO `dictionary` VALUES ('27', 'Door', 'D11', 'Improper Field Modification (Explain Modification)', '11');
INSERT INTO `dictionary` VALUES ('28', 'Door', 'D12', 'Incorrect Hardware Preparation (Explain)', '12');
INSERT INTO `dictionary` VALUES ('29', 'Door', 'D13', 'Unused Fastener Hole(s)', '13');
INSERT INTO `dictionary` VALUES ('30', 'Door', 'D14', 'Improper Plant-ons', '14');
INSERT INTO `dictionary` VALUES ('31', 'Door', 'D15', 'Replace Door', '15');
INSERT INTO `dictionary` VALUES ('32', 'Door', 'D16', 'Other', '16');
INSERT INTO `dictionary` VALUES ('33', 'Door', 'D17', 'Other', '17');
INSERT INTO `dictionary` VALUES ('34', 'Door', 'D18', 'Other', '18');
INSERT INTO `dictionary` VALUES ('35', 'Door', 'D19', 'Other', '19');
INSERT INTO `dictionary` VALUES ('36', 'Operational Test', 'T1', 'Door Does NOT Swing Freely', null);
INSERT INTO `dictionary` VALUES ('37', 'Operational Test', 'T2', 'Door Does NOT Close Completely', null);
INSERT INTO `dictionary` VALUES ('38', 'Operational Test', 'T3', 'Door Does NOT Securely Latch', null);
INSERT INTO `dictionary` VALUES ('39', 'Operational Test', 'T4', 'Electric Door Release Does NOT Allow Door to Close', null);
INSERT INTO `dictionary` VALUES ('40', 'Operational Test', 'T5', 'Door Bottom Drags Against Floor Material', null);
INSERT INTO `dictionary` VALUES ('41', 'Operational Test', 'T6', 'Door Rubs Against Frame', null);
INSERT INTO `dictionary` VALUES ('42', 'Operational Test', 'T7', 'Edges of Paired Doors Overlap', null);
INSERT INTO `dictionary` VALUES ('43', 'Operational Test', 'T8', 'Coordinator Does NOT Function Properly', null);
INSERT INTO `dictionary` VALUES ('44', 'Operational Test', 'T9', 'Other', null);
INSERT INTO `dictionary` VALUES ('45', 'Operational Test', 'T10', 'Other', null);
INSERT INTO `dictionary` VALUES ('46', 'Operational Test', 'T11', 'Other', null);
INSERT INTO `dictionary` VALUES ('47', 'Operational Test', 'T12', 'Other', null);
INSERT INTO `dictionary` VALUES ('48', 'Hinges/Pivots', 'H1', 'Missing Hinge(s)', '1');
INSERT INTO `dictionary` VALUES ('49', 'Hinges/Pivots', 'H2', 'Incorrect Hinge(s)', '2');
INSERT INTO `dictionary` VALUES ('50', 'Hinges/Pivots', 'H3', 'Loose Hinge(s)', '3');
INSERT INTO `dictionary` VALUES ('51', 'Hinges/Pivots', 'H4', 'Missing Screw(s)', '4');
INSERT INTO `dictionary` VALUES ('52', 'Hinges/Pivots', 'H5', 'Replace Hinge(s)', '5');
INSERT INTO `dictionary` VALUES ('53', 'Hinges/Pivots', 'H6', 'Other', '6');
INSERT INTO `dictionary` VALUES ('54', 'Hinges/Pivots', 'H7', 'Other', '7');
INSERT INTO `dictionary` VALUES ('55', 'Hinges/Pivots', 'H8', 'Other', '8');
INSERT INTO `dictionary` VALUES ('56', 'Hinges/Pivots', 'H9', 'Other', '9');
INSERT INTO `dictionary` VALUES ('57', 'Door Bolts', 'B1', 'Missing Top Flush Bolt', '1');
INSERT INTO `dictionary` VALUES ('58', 'Door Bolts', 'B2', 'Missing Bottom Flush Bolt', '2');
INSERT INTO `dictionary` VALUES ('59', 'Door Bolts', 'B3', 'Missing Strike (Top Bolt)', '3');
INSERT INTO `dictionary` VALUES ('60', 'Door Bolts', 'B4', 'Missing Strike (Bottom Bolt)', '4');
INSERT INTO `dictionary` VALUES ('61', 'Door Bolts', 'B5', 'Bottom Bolt does NOT Engage Strike', '5');
INSERT INTO `dictionary` VALUES ('62', 'Door Bolts', 'B6', 'Missing Bolt Head (Top)', '6');
INSERT INTO `dictionary` VALUES ('63', 'Door Bolts', 'B7', 'Missing Bolt Head (Bottom)', '7');
INSERT INTO `dictionary` VALUES ('64', 'Door Bolts', 'B8', 'Missing Rub Plate(s)', '8');
INSERT INTO `dictionary` VALUES ('65', 'Door Bolts', 'B9', 'Incorrect Type of Flush Bolt(s)', '9');
INSERT INTO `dictionary` VALUES ('66', 'Door Bolts', 'B10', 'Other', '10');
INSERT INTO `dictionary` VALUES ('67', 'Door Bolts', 'B11', 'Other', '11');
INSERT INTO `dictionary` VALUES ('68', 'Door Bolts', 'B12', 'Other', '12');
INSERT INTO `dictionary` VALUES ('69', 'Door Bolts', 'B13', 'Other', '13');
INSERT INTO `dictionary` VALUES ('70', 'Locks', 'L1', 'Missing Lock', '1');
INSERT INTO `dictionary` VALUES ('71', 'Locks', 'L2', 'Incorrect Latch Bolt Throw', '2');
INSERT INTO `dictionary` VALUES ('72', 'Locks', 'L3', 'Non-fire Rated Latch Bolt', '3');
INSERT INTO `dictionary` VALUES ('73', 'Locks', 'L4', 'Latch Bolt Binds', '4');
INSERT INTO `dictionary` VALUES ('74', 'Locks', 'L5', 'Latch Bolt Missing', '5');
INSERT INTO `dictionary` VALUES ('75', 'Locks', 'L6', 'Loose Lever(s) or Knob(s)', '6');
INSERT INTO `dictionary` VALUES ('76', 'Locks', 'L7', 'Latch Bolt Does NOT Engage Strike', '7');
INSERT INTO `dictionary` VALUES ('77', 'Locks', 'L8', 'Missing Strike Plate', '8');
INSERT INTO `dictionary` VALUES ('78', 'Locks', 'L9', 'Missing Screw(s)', '9');
INSERT INTO `dictionary` VALUES ('79', 'Locks', 'L10', 'Missing Flush Bolt', '10');
INSERT INTO `dictionary` VALUES ('80', 'Locks', 'L11', 'Missing Flush Bolt Strike', '11');
INSERT INTO `dictionary` VALUES ('81', 'Locks', 'L12', 'Other', '12');
INSERT INTO `dictionary` VALUES ('82', 'Locks', 'L13', 'Other', '13');
INSERT INTO `dictionary` VALUES ('83', 'Locks', 'L14', 'Other', '14');
INSERT INTO `dictionary` VALUES ('84', 'Locks', 'L15', 'Other', '15');
INSERT INTO `dictionary` VALUES ('85', 'Fire Exit Hardware', 'E1', 'Missing Fire Exit Device', '1');
INSERT INTO `dictionary` VALUES ('86', 'Fire Exit Hardware', 'E2', 'Missing Latch Bolt Assembly (top)', '2');
INSERT INTO `dictionary` VALUES ('87', 'Fire Exit Hardware', 'E3', 'Missing Latch Bolt Assembly (Bottom)', '3');
INSERT INTO `dictionary` VALUES ('88', 'Fire Exit Hardware', 'E4', 'Missing Strike(s)', '4');
INSERT INTO `dictionary` VALUES ('89', 'Fire Exit Hardware', 'E5', 'Missing Vertical Rod (Top)', '5');
INSERT INTO `dictionary` VALUES ('90', 'Fire Exit Hardware', 'E6', 'Missing Vertical Rod (Bottom)', '6');
INSERT INTO `dictionary` VALUES ('91', 'Fire Exit Hardware', 'E7', 'Push Bar Does NOT Extend Halfway Across Door Width', '7');
INSERT INTO `dictionary` VALUES ('92', 'Fire Exit Hardware', 'E8', 'Non-fire Rated Panic Hardware (Dogging)', '8');
INSERT INTO `dictionary` VALUES ('93', 'Fire Exit Hardware', 'E9', 'Missing Lever or Knob', '9');
INSERT INTO `dictionary` VALUES ('94', 'Fire Exit Hardware', 'E10', 'Other', '10');
INSERT INTO `dictionary` VALUES ('95', 'Fire Exit Hardware', 'E11', 'Other', '11');
INSERT INTO `dictionary` VALUES ('96', 'Fire Exit Hardware', 'E12', 'Other', '12');
INSERT INTO `dictionary` VALUES ('97', 'Fire Exit Hardware', 'E13', 'Other', '13');
INSERT INTO `dictionary` VALUES ('98', 'Door Closers', 'C1', 'Missing Door Closer(s)', '1');
INSERT INTO `dictionary` VALUES ('99', 'Door Closers', 'C2', 'Leaking Door Closer(s)', '2');
INSERT INTO `dictionary` VALUES ('100', 'Door Closers', 'C3', 'Missing Arm(s)', '3');
INSERT INTO `dictionary` VALUES ('101', 'Door Closers', 'C4', 'Broken Arm(s)', '4');
INSERT INTO `dictionary` VALUES ('102', 'Door Closers', 'C5', 'Missing Closer(s)', '5');
INSERT INTO `dictionary` VALUES ('103', 'Door Closers', 'C6', 'Does NOT Close Door Completely', '6');
INSERT INTO `dictionary` VALUES ('104', 'Door Closers', 'C7', 'Missing Screw(s)', '7');
INSERT INTO `dictionary` VALUES ('105', 'Door Closers', 'C8', 'Missing Drop and/or Adapter Plate(s)', '8');
INSERT INTO `dictionary` VALUES ('106', 'Door Closers', 'C9', 'Hold-open Arm(s)', '9');
INSERT INTO `dictionary` VALUES ('107', 'Door Closers', 'C10', 'Missing Coordinator', '10');
INSERT INTO `dictionary` VALUES ('108', 'Door Closers', 'C11', 'Missing Carry Bar', '11');
INSERT INTO `dictionary` VALUES ('109', 'Door Closers', 'C12', 'Broken Coordinator', '12');
INSERT INTO `dictionary` VALUES ('110', 'Door Closers', 'C13', 'Broken Carry Bar', '13');
INSERT INTO `dictionary` VALUES ('111', 'Door Closers', 'C14', 'Overhead Hold-open (Surface or Concealed)', '14');
INSERT INTO `dictionary` VALUES ('112', 'Door Closers', 'C15', 'Other', '16');
INSERT INTO `dictionary` VALUES ('113', 'Door Closers', 'C16', 'Other', '17');
INSERT INTO `dictionary` VALUES ('114', 'Door Closers', 'C17', 'Other', '18');
INSERT INTO `dictionary` VALUES ('115', 'Door Closers', 'C18', 'Other', '18');
INSERT INTO `dictionary` VALUES ('116', 'Miscellaneous', 'M1', 'Missing Threshold/Saddle', '1');
INSERT INTO `dictionary` VALUES ('117', 'Miscellaneous', 'M2', 'Incorrect Clearance (Top of Door to Frame)', '2');
INSERT INTO `dictionary` VALUES ('118', 'Miscellaneous', 'M3', 'Incorrect Clearance (Hinge Edge to Frame)', '3');
INSERT INTO `dictionary` VALUES ('119', 'Miscellaneous', 'M4', 'Incorrect Clearance (Lock Edge to Frame)', '4');
INSERT INTO `dictionary` VALUES ('120', 'Miscellaneous', 'M5', 'Incorrect Clearance (Door Bottom to Floor)', '5');
INSERT INTO `dictionary` VALUES ('121', 'Miscellaneous', 'M6', 'Incorrect Clearance (Between Doors)', '6');
INSERT INTO `dictionary` VALUES ('122', 'Miscellaneous', 'M7', 'Missing Astragal', '7');
INSERT INTO `dictionary` VALUES ('123', 'Miscellaneous', 'M8', 'Missing or Damaged Gasketing/Smoke Seal', '8');
INSERT INTO `dictionary` VALUES ('124', 'Miscellaneous', 'M9', 'Kick-down Door Holder', '9');
INSERT INTO `dictionary` VALUES ('125', 'Miscellaneous', 'M10', 'Door Wedge', '10');
INSERT INTO `dictionary` VALUES ('126', 'Miscellaneous', 'M11', 'Door Stop with Hold Open (Manual)', '11');
INSERT INTO `dictionary` VALUES ('127', 'Miscellaneous', 'M12', 'Protection Plate(s) too Large', '12');
INSERT INTO `dictionary` VALUES ('128', 'Miscellaneous', 'M13', 'Protection Plate(s) Missing Screw(s)', '13');
INSERT INTO `dictionary` VALUES ('129', 'Miscellaneous', 'M14', 'Signage Too Large', '14');
INSERT INTO `dictionary` VALUES ('130', 'Miscellaneous', 'M15', 'Signage Screwed/Nailed to Door', '15');
INSERT INTO `dictionary` VALUES ('131', 'Miscellaneous', 'M16', 'Other', '16');
INSERT INTO `dictionary` VALUES ('132', 'Miscellaneous', 'M17', 'Other', '17');
INSERT INTO `dictionary` VALUES ('133', 'Miscellaneous', 'M18', 'Other', '18');
INSERT INTO `dictionary` VALUES ('134', 'Miscellaneous', 'M19', 'Other', '19');
INSERT INTO `dictionary` VALUES ('135', 'Logical', 'Yes', 'Yes', '1');
INSERT INTO `dictionary` VALUES ('136', 'Logical', 'No', 'No', '2');
INSERT INTO `dictionary` VALUES ('150', 'Code Category', 'Frame', null, null);
INSERT INTO `dictionary` VALUES ('151', 'Code Category', 'Door', null, null);
INSERT INTO `dictionary` VALUES ('152', 'Code Category', 'Operational Test', null, null);
INSERT INTO `dictionary` VALUES ('153', 'Code Category', 'Hinges/Pivots', null, null);
INSERT INTO `dictionary` VALUES ('154', 'Code Category', 'Door Bolts', null, null);
INSERT INTO `dictionary` VALUES ('155', 'Code Category', 'Locks', null, null);
INSERT INTO `dictionary` VALUES ('156', 'Code Category', 'Fire Exit Hardware', null, null);
INSERT INTO `dictionary` VALUES ('157', 'Code Category', 'Door Closers', null, null);
INSERT INTO `dictionary` VALUES ('158', 'Code Category', 'Miscellaneous', null, null);
INSERT INTO `dictionary` VALUES ('948', 'State', 'AK', null, null);
INSERT INTO `dictionary` VALUES ('949', 'State', 'AL', null, null);
INSERT INTO `dictionary` VALUES ('950', 'State', 'AR', null, null);
INSERT INTO `dictionary` VALUES ('951', 'State', 'AZ', null, null);
INSERT INTO `dictionary` VALUES ('952', 'State', 'CA', null, null);
INSERT INTO `dictionary` VALUES ('953', 'State', 'CO', null, null);
INSERT INTO `dictionary` VALUES ('954', 'State', 'CT', null, null);
INSERT INTO `dictionary` VALUES ('955', 'State', 'DC', null, null);
INSERT INTO `dictionary` VALUES ('956', 'State', 'DE', null, null);
INSERT INTO `dictionary` VALUES ('957', 'State', 'FL', null, null);
INSERT INTO `dictionary` VALUES ('958', 'State', 'GA', null, null);
INSERT INTO `dictionary` VALUES ('959', 'State', 'HI', null, null);
INSERT INTO `dictionary` VALUES ('960', 'State', 'IA', null, null);
INSERT INTO `dictionary` VALUES ('961', 'State', 'ID', null, null);
INSERT INTO `dictionary` VALUES ('962', 'State', 'IL', null, null);
INSERT INTO `dictionary` VALUES ('963', 'State', 'IN', null, null);
INSERT INTO `dictionary` VALUES ('964', 'State', 'KS', null, null);
INSERT INTO `dictionary` VALUES ('965', 'State', 'KY', null, null);
INSERT INTO `dictionary` VALUES ('966', 'State', 'LA', null, null);
INSERT INTO `dictionary` VALUES ('967', 'State', 'MA', null, null);
INSERT INTO `dictionary` VALUES ('968', 'State', 'MD', null, null);
INSERT INTO `dictionary` VALUES ('969', 'State', 'ME', null, null);
INSERT INTO `dictionary` VALUES ('970', 'State', 'MI', null, null);
INSERT INTO `dictionary` VALUES ('971', 'State', 'MN', null, null);
INSERT INTO `dictionary` VALUES ('972', 'State', 'MO', null, null);
INSERT INTO `dictionary` VALUES ('973', 'State', 'MS', null, null);
INSERT INTO `dictionary` VALUES ('974', 'State', 'MT', null, null);
INSERT INTO `dictionary` VALUES ('975', 'State', 'NC', null, null);
INSERT INTO `dictionary` VALUES ('976', 'State', 'ND', null, null);
INSERT INTO `dictionary` VALUES ('977', 'State', 'NE', null, null);
INSERT INTO `dictionary` VALUES ('978', 'State', 'NH', null, null);
INSERT INTO `dictionary` VALUES ('979', 'State', 'NJ', null, null);
INSERT INTO `dictionary` VALUES ('980', 'State', 'NM', null, null);
INSERT INTO `dictionary` VALUES ('981', 'State', 'NV', null, null);
INSERT INTO `dictionary` VALUES ('982', 'State', 'NY', null, null);
INSERT INTO `dictionary` VALUES ('983', 'State', 'OH', null, null);
INSERT INTO `dictionary` VALUES ('984', 'State', 'OK', null, null);
INSERT INTO `dictionary` VALUES ('985', 'State', 'OR', null, null);
INSERT INTO `dictionary` VALUES ('986', 'State', 'PA', null, null);
INSERT INTO `dictionary` VALUES ('987', 'State', 'RI', null, null);
INSERT INTO `dictionary` VALUES ('988', 'State', 'SC', null, null);
INSERT INTO `dictionary` VALUES ('989', 'State', 'SD', null, null);
INSERT INTO `dictionary` VALUES ('990', 'State', 'TN', null, null);
INSERT INTO `dictionary` VALUES ('991', 'State', 'TX', null, null);
INSERT INTO `dictionary` VALUES ('992', 'State', 'UT', null, null);
INSERT INTO `dictionary` VALUES ('993', 'State', 'VA', null, null);
INSERT INTO `dictionary` VALUES ('994', 'State', 'VT', null, null);
INSERT INTO `dictionary` VALUES ('995', 'State', 'WA', null, null);
INSERT INTO `dictionary` VALUES ('996', 'State', 'WI', null, null);
INSERT INTO `dictionary` VALUES ('997', 'State', 'WV', null, null);
INSERT INTO `dictionary` VALUES ('998', 'State', 'WY', null, null);
INSERT INTO `dictionary` VALUES ('999', 'Country', 'USA', null, null);
INSERT INTO `dictionary` VALUES ('1000', 'Country', 'Canada', null, null);
INSERT INTO `dictionary` VALUES ('1001', 'Company Type', 'Building Owner', null, null);
INSERT INTO `dictionary` VALUES ('1002', 'Company Type', 'Inspection Company', null, null);
INSERT INTO `dictionary` VALUES ('1003', 'Door Style', 'Single Door', null, '1');
INSERT INTO `dictionary` VALUES ('1004', 'Door Style', 'Pair of Doors', null, '2');
INSERT INTO `dictionary` VALUES ('1005', 'Door Style', 'Double Egress', null, '3');
INSERT INTO `dictionary` VALUES ('1006', 'Door Type', 'Swinging', null, '1');
INSERT INTO `dictionary` VALUES ('1007', 'Door Type', 'Low Energy Automatic', null, '3');
INSERT INTO `dictionary` VALUES ('1008', 'Door Type', 'Horizontal Slide', null, '5');
INSERT INTO `dictionary` VALUES ('1009', 'Door Type', 'Vertical Sliding', null, '7');
INSERT INTO `dictionary` VALUES ('1010', 'Door Type', 'Egress', null, '2');
INSERT INTO `dictionary` VALUES ('1011', 'Door Type', 'Access Door', null, '4');
INSERT INTO `dictionary` VALUES ('1012', 'Door Type', 'Rolling Steel', null, '6');
INSERT INTO `dictionary` VALUES ('1013', 'Door Type', 'Other', null, '8');
INSERT INTO `dictionary` VALUES ('1014', 'Door Material', 'Wood', null, '1');
INSERT INTO `dictionary` VALUES ('1015', 'Door Material', 'Hollow Metal', null, '2');
INSERT INTO `dictionary` VALUES ('1016', 'Door Material', 'Stile and Rail', null, '3');
INSERT INTO `dictionary` VALUES ('1017', 'Door Material', 'Aluminum', null, '4');
INSERT INTO `dictionary` VALUES ('1018', 'Door Material', 'Stainless Steel', null, '5');
INSERT INTO `dictionary` VALUES ('1019', 'Door Elevation', 'Flush', null, '1');
INSERT INTO `dictionary` VALUES ('1020', 'Door Elevation', 'Vision Lite', null, '3');
INSERT INTO `dictionary` VALUES ('1021', 'Door Elevation', 'Narrow Lite', null, '5');
INSERT INTO `dictionary` VALUES ('1022', 'Door Elevation', 'Full Lite', null, '7');
INSERT INTO `dictionary` VALUES ('1023', 'Door Elevation', 'Louver', null, '2');
INSERT INTO `dictionary` VALUES ('1024', 'Door Elevation', 'Transom', null, '4');
INSERT INTO `dictionary` VALUES ('1025', 'Door Elevation', 'Half Glass', null, '6');
INSERT INTO `dictionary` VALUES ('1026', 'Door Elevation', 'Dutch', null, '8');
INSERT INTO `dictionary` VALUES ('1027', 'Frame Material', 'Wood', null, '1');
INSERT INTO `dictionary` VALUES ('1028', 'Frame Material', 'Hollow Metal', null, '2');
INSERT INTO `dictionary` VALUES ('1029', 'Frame Material', 'Aluminum', null, '3');
INSERT INTO `dictionary` VALUES ('1030', 'Frame Elevation', '3 Sided Frame', null, '1');
INSERT INTO `dictionary` VALUES ('1031', 'Frame Elevation', 'Side Lite Frame', null, '2');
INSERT INTO `dictionary` VALUES ('1032', 'Frame Elevation', 'Transom Frame', null, '3');
INSERT INTO `dictionary` VALUES ('1033', 'Frame Elevation', 'Side Lite Transom Frame', null, '4');
INSERT INTO `dictionary` VALUES ('1034', 'Frame Elevation', 'Other', null, '5');
INSERT INTO `dictionary` VALUES ('1035', 'Fire-Rating 1', '20', null, '1');
INSERT INTO `dictionary` VALUES ('1037', 'Fire-Rating 1', '45', null, '2');
INSERT INTO `dictionary` VALUES ('1038', 'Fire-Rating 1', '60', null, '3');
INSERT INTO `dictionary` VALUES ('1039', 'Fire-Rating 1', '90', null, '4');
INSERT INTO `dictionary` VALUES ('1040', 'Fire-Rating 1', '180', null, '5');
INSERT INTO `dictionary` VALUES ('1041', 'Fire-Rating 2', 'A', null, '4');
INSERT INTO `dictionary` VALUES ('1042', 'Fire-Rating 2', 'B', null, '3');
INSERT INTO `dictionary` VALUES ('1043', 'Fire-Rating 2', 'C', null, '1');
INSERT INTO `dictionary` VALUES ('1044', 'Fire-Rating 2', 'D', null, '2');
INSERT INTO `dictionary` VALUES ('1045', 'Fire-Rating 2', 'E', null, '5');
INSERT INTO `dictionary` VALUES ('1046', 'Fire-Rating 3', 'S', null, '1');
INSERT INTO `dictionary` VALUES ('1047', 'Fire-Rating 4', 'UL10C', null, '1');
INSERT INTO `dictionary` VALUES ('1048', 'Door Temperature Rise', '250 T.R.', null, '1');
INSERT INTO `dictionary` VALUES ('1049', 'Door Temperature Rise', '450 T.R.', null, '2');
INSERT INTO `dictionary` VALUES ('1050', 'Door Temperature Rise', '650 T.R.', null, '3');
INSERT INTO `dictionary` VALUES ('1051', 'Door Temperature Rise', '>650 T.R.', null, '4');
INSERT INTO `dictionary` VALUES ('1052', 'Door Listing Agency', 'FM', null, '1');
INSERT INTO `dictionary` VALUES ('1053', 'Door Listing Agency', 'WH', null, '2');
INSERT INTO `dictionary` VALUES ('1054', 'Door Listing Agency', 'UL', null, '3');
INSERT INTO `dictionary` VALUES ('1055', 'Door Listing Agency', 'Other', null, '4');
INSERT INTO `dictionary` VALUES ('1058', 'Door Material', 'Other', null, '6');
INSERT INTO `dictionary` VALUES ('1059', 'Door Elevation', 'Other', null, '9');
INSERT INTO `dictionary` VALUES ('1060', 'Frame Material', 'Other', null, '5');
INSERT INTO `dictionary` VALUES ('1062', 'Frame Material', 'Stainless Steel', null, '4');
INSERT INTO `dictionary` VALUES ('1063', 'Handing', 'RH', null, null);
INSERT INTO `dictionary` VALUES ('1064', 'Handing', 'LH', null, null);
INSERT INTO `dictionary` VALUES ('1065', 'Handing', 'RHR', null, null);
INSERT INTO `dictionary` VALUES ('1066', 'Handing', 'LHR', null, null);
INSERT INTO `dictionary` VALUES ('1067', 'Handing', 'RHRA', null, null);
INSERT INTO `dictionary` VALUES ('1068', 'Handing', 'LHRA', null, null);
INSERT INTO `dictionary` VALUES ('1069', 'Handing', 'RHR/RHR', null, null);
INSERT INTO `dictionary` VALUES ('1070', 'Handing', 'LHR/LHR', null, null);
INSERT INTO `dictionary` VALUES ('1071', 'Integration Type', 'Inspection service', null, null);
INSERT INTO `dictionary` VALUES ('1072', 'Integration Type', 'Mico service', null, null);
INSERT INTO `dictionary` VALUES ('1073', 'Integration Type', 'PDF service', null, null);
INSERT INTO `dictionary` VALUES ('1074', 'Integration Status', 'failed', null, null);
INSERT INTO `dictionary` VALUES ('1075', 'Integration Status', 'succeeded', null, null);
INSERT INTO `dictionary` VALUES ('1076', 'Integration Status', 'waiting', null, null);
INSERT INTO `dictionary` VALUES ('1077', 'Inspection Status', 'Completed', null, null);
INSERT INTO `dictionary` VALUES ('1078', 'Inspection Status', 'Assigned', null, null);
INSERT INTO `dictionary` VALUES ('1079', 'Inspection Status', 'Assigning', null, null);
INSERT INTO `dictionary` VALUES ('1080', 'Inspection Status', 'New', null, null);
INSERT INTO `dictionary` VALUES ('1081', 'Hinge Height', '3.5\"', null, '1');
INSERT INTO `dictionary` VALUES ('1082', 'Hinge Height', '4.0\"', null, '2');
INSERT INTO `dictionary` VALUES ('1083', 'Hinge Height', '4.5\"', null, '3');
INSERT INTO `dictionary` VALUES ('1084', 'Hinge Height', '5.0\"', null, '4');
INSERT INTO `dictionary` VALUES ('1085', 'Hinge Height', '6.0\"', null, '5');
INSERT INTO `dictionary` VALUES ('1086', 'Hinge Height', '7.0\"', null, '6');
INSERT INTO `dictionary` VALUES ('1087', 'Hinge Thickness', '.134', null, '1');
INSERT INTO `dictionary` VALUES ('1088', 'Hinge Thickness', '.180', null, '2');
INSERT INTO `dictionary` VALUES ('1089', 'Hinge Thickness', '.225', null, '3');
INSERT INTO `dictionary` VALUES ('1090', 'Hinge Fraction1', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1091', 'Hinge Fraction1', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1092', 'Hinge Fraction1', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1093', 'Hinge Fraction1', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1094', 'Hinge Fraction1', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1095', 'Hinge Fraction1', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1096', 'Hinge Fraction1', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1097', 'Hinge Fraction1', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1098', 'Hinge Fraction1', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1099', 'Hinge Fraction1', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1100', 'Hinge Fraction1', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1101', 'Hinge Fraction1', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1102', 'Hinge Fraction1', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1103', 'Hinge Fraction1', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1104', 'Hinge Fraction2', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1105', 'Hinge Fraction2', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1106', 'Hinge Fraction2', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1107', 'Hinge Fraction2', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1108', 'Hinge Fraction2', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1109', 'Hinge Fraction2', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1110', 'Hinge Fraction2', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1111', 'Hinge Fraction2', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1112', 'Hinge Fraction2', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1113', 'Hinge Fraction2', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1114', 'Hinge Fraction2', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1115', 'Hinge Fraction2', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1116', 'Hinge Fraction2', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1117', 'Hinge Fraction2', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1118', 'Hinge Fraction3', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1119', 'Hinge Fraction3', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1120', 'Hinge Fraction3', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1121', 'Hinge Fraction3', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1122', 'Hinge Fraction3', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1123', 'Hinge Fraction3', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1124', 'Hinge Fraction3', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1125', 'Hinge Fraction3', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1126', 'Hinge Fraction3', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1127', 'Hinge Fraction3', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1128', 'Hinge Fraction3', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1129', 'Hinge Fraction3', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1130', 'Hinge Fraction3', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1131', 'Hinge Fraction3', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1132', 'Hinge Fraction4', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1133', 'Hinge Fraction4', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1134', 'Hinge Fraction4', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1135', 'Hinge Fraction4', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1136', 'Hinge Fraction4', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1137', 'Hinge Fraction4', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1138', 'Hinge Fraction4', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1139', 'Hinge Fraction4', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1140', 'Hinge Fraction4', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1141', 'Hinge Fraction4', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1142', 'Hinge Fraction4', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1143', 'Hinge Fraction4', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1144', 'Hinge Fraction4', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1145', 'Hinge Fraction4', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1146', 'Hinge Backset', '1/4\"', null, null);
INSERT INTO `dictionary` VALUES ('1147', 'Hinge Backset', '5/16\"', null, null);
INSERT INTO `dictionary` VALUES ('1148', 'Hinge Backset', 'Other', null, null);
INSERT INTO `dictionary` VALUES ('1149', 'Top To Centerline Fraction', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1150', 'Top To Centerline Fraction', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1151', 'Top To Centerline Fraction', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1152', 'Top To Centerline Fraction', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1153', 'Top To Centerline Fraction', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1154', 'Top To Centerline Fraction', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1155', 'Top To Centerline Fraction', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1156', 'Top To Centerline Fraction', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1157', 'Lock Backset', '2-3/8\"', null, null);
INSERT INTO `dictionary` VALUES ('1158', 'Lock Backset', '2-3/4\"', null, null);
INSERT INTO `dictionary` VALUES ('1159', 'Lock Backset', '3-3/4\"', null, null);
INSERT INTO `dictionary` VALUES ('1160', 'Lock Backset', '5.0\"', null, null);
INSERT INTO `dictionary` VALUES ('1161', 'Lock Backset', '<Add New>', null, null);
INSERT INTO `dictionary` VALUES ('1162', 'Strike Height', '2-3/4\"', null, null);
INSERT INTO `dictionary` VALUES ('1163', 'Strike Height', '4-7/8\"', null, null);
INSERT INTO `dictionary` VALUES ('1164', 'Strike Height', '<Add New>', null, null);
INSERT INTO `dictionary` VALUES ('1165', 'Prefit Fraction X', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1166', 'Prefit Fraction X', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1167', 'Prefit Fraction X', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1168', 'Prefit Fraction X', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1169', 'Prefit Fraction X', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1170', 'Prefit Fraction X', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1171', 'Prefit Fraction X', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1172', 'Prefit Fraction X', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1173', 'Prefit Fraction X', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1174', 'Prefit Fraction X', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1175', 'Prefit Fraction X', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1176', 'Prefit Fraction X', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1177', 'Prefit Fraction X', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1178', 'Prefit Fraction X', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1179', 'Prefit Fraction X', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1180', 'Prefit Fraction X', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1181', 'Prefit Fraction X', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1182', 'Prefit Fraction X', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1183', 'Prefit Fraction X', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1184', 'Prefit Fraction X', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1185', 'Prefit Fraction X', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1186', 'Prefit Fraction X', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1187', 'Prefit Fraction X', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1188', 'Prefit Fraction X', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1189', 'Prefit Fraction X', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1190', 'Prefit Fraction X', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1191', 'Prefit Fraction X', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1192', 'Prefit Fraction X', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1193', 'Prefit Fraction X', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1194', 'Prefit Fraction Y', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1195', 'Prefit Fraction Y', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1196', 'Prefit Fraction Y', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1197', 'Prefit Fraction Y', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1198', 'Prefit Fraction Y', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1199', 'Prefit Fraction Y', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1200', 'Prefit Fraction Y', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1201', 'Prefit Fraction Y', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1202', 'Prefit Fraction Y', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1203', 'Prefit Fraction Y', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1204', 'Prefit Fraction Y', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1205', 'Prefit Fraction Y', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1206', 'Prefit Fraction Y', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1207', 'Prefit Fraction Y', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1208', 'Prefit Fraction Y', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1209', 'Prefit Fraction Y', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1210', 'Prefit Fraction Y', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1211', 'Prefit Fraction Y', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1212', 'Prefit Fraction Y', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1213', 'Prefit Fraction Y', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1214', 'Prefit Fraction Y', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1215', 'Prefit Fraction Y', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1216', 'Prefit Fraction Y', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1217', 'Prefit Fraction Y', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1218', 'Prefit Fraction Y', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1219', 'Prefit Fraction Y', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1220', 'Prefit Fraction Y', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1221', 'Prefit Fraction Y', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1222', 'Prefit Fraction Y', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1223', 'Frame Opening Fraction X', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1224', 'Frame Opening Fraction X', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1225', 'Frame Opening Fraction X', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1226', 'Frame Opening Fraction X', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1227', 'Frame Opening Fraction X', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1228', 'Frame Opening Fraction X', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1229', 'Frame Opening Fraction X', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1230', 'Frame Opening Fraction X', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1231', 'Frame Opening Fraction X', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1232', 'Frame Opening Fraction X', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1233', 'Frame Opening Fraction X', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1234', 'Frame Opening Fraction X', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1235', 'Frame Opening Fraction X', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1236', 'Frame Opening Fraction X', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1237', 'Frame Opening Fraction X', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1238', 'Frame Opening Fraction X', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1239', 'Frame Opening Fraction X', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1240', 'Frame Opening Fraction X', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1241', 'Frame Opening Fraction X', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1242', 'Frame Opening Fraction X', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1243', 'Frame Opening Fraction X', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1244', 'Frame Opening Fraction X', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1245', 'Frame Opening Fraction X', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1246', 'Frame Opening Fraction X', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1247', 'Frame Opening Fraction X', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1248', 'Frame Opening Fraction X', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1249', 'Frame Opening Fraction X', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1250', 'Frame Opening Fraction X', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1251', 'Frame Opening Fraction X', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1252', 'Frame Opening Fraction Y', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1253', 'Frame Opening Fraction Y', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1254', 'Frame Opening Fraction Y', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1255', 'Frame Opening Fraction Y', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1256', 'Frame Opening Fraction Y', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1257', 'Frame Opening Fraction Y', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1258', 'Frame Opening Fraction Y', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1259', 'Frame Opening Fraction Y', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1260', 'Frame Opening Fraction Y', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1261', 'Frame Opening Fraction Y', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1262', 'Frame Opening Fraction Y', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1263', 'Frame Opening Fraction Y', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1264', 'Frame Opening Fraction Y', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1265', 'Frame Opening Fraction Y', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1266', 'Frame Opening Fraction Y', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1267', 'Frame Opening Fraction Y', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1268', 'Frame Opening Fraction Y', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1269', 'Frame Opening Fraction Y', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1270', 'Frame Opening Fraction Y', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1271', 'Frame Opening Fraction Y', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1272', 'Frame Opening Fraction Y', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1273', 'Frame Opening Fraction Y', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1274', 'Frame Opening Fraction Y', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1275', 'Frame Opening Fraction Y', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1276', 'Frame Opening Fraction Y', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1277', 'Frame Opening Fraction Y', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1278', 'Frame Opening Fraction Y', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1279', 'Frame Opening Fraction Y', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1280', 'Frame Opening Fraction Y', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1281', 'Lite Cutout Fraction X', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1282', 'Lite Cutout Fraction X', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1283', 'Lite Cutout Fraction X', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1284', 'Lite Cutout Fraction X', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1285', 'Lite Cutout Fraction X', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1286', 'Lite Cutout Fraction X', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1287', 'Lite Cutout Fraction X', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1288', 'Lite Cutout Fraction X', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1289', 'Lite Cutout Fraction X', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1290', 'Lite Cutout Fraction X', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1291', 'Lite Cutout Fraction X', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1292', 'Lite Cutout Fraction X', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1293', 'Lite Cutout Fraction X', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1294', 'Lite Cutout Fraction X', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1295', 'Lite Cutout Fraction X', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1296', 'Lite Cutout Fraction X', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1297', 'Lite Cutout Fraction X', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1298', 'Lite Cutout Fraction X', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1299', 'Lite Cutout Fraction X', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1300', 'Lite Cutout Fraction X', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1301', 'Lite Cutout Fraction X', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1302', 'Lite Cutout Fraction X', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1303', 'Lite Cutout Fraction X', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1304', 'Lite Cutout Fraction X', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1305', 'Lite Cutout Fraction X', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1306', 'Lite Cutout Fraction X', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1307', 'Lite Cutout Fraction X', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1308', 'Lite Cutout Fraction X', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1309', 'Lite Cutout Fraction X', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1310', 'Lite Cutout Fraction Y', '1/32\"', null, '1');
INSERT INTO `dictionary` VALUES ('1311', 'Lite Cutout Fraction Y', '1/16\"', null, '2');
INSERT INTO `dictionary` VALUES ('1312', 'Lite Cutout Fraction Y', '3/32\"', null, '3');
INSERT INTO `dictionary` VALUES ('1313', 'Lite Cutout Fraction Y', '1/8\"', null, '4');
INSERT INTO `dictionary` VALUES ('1314', 'Lite Cutout Fraction Y', '5/32\"', null, '5');
INSERT INTO `dictionary` VALUES ('1315', 'Lite Cutout Fraction Y', '3/16\"', null, '6');
INSERT INTO `dictionary` VALUES ('1316', 'Lite Cutout Fraction Y', '7/32\"', null, '7');
INSERT INTO `dictionary` VALUES ('1317', 'Lite Cutout Fraction Y', '1/4\"', null, '8');
INSERT INTO `dictionary` VALUES ('1318', 'Lite Cutout Fraction Y', '9/32\"', null, '9');
INSERT INTO `dictionary` VALUES ('1319', 'Lite Cutout Fraction Y', '5/16\"', null, '10');
INSERT INTO `dictionary` VALUES ('1320', 'Lite Cutout Fraction Y', '11/32\"', null, '11');
INSERT INTO `dictionary` VALUES ('1321', 'Lite Cutout Fraction Y', '7/16\"', null, '12');
INSERT INTO `dictionary` VALUES ('1322', 'Lite Cutout Fraction Y', '15/32\"', null, '13');
INSERT INTO `dictionary` VALUES ('1323', 'Lite Cutout Fraction Y', '1/2\"', null, '14');
INSERT INTO `dictionary` VALUES ('1324', 'Lite Cutout Fraction Y', '17/32\"', null, '15');
INSERT INTO `dictionary` VALUES ('1325', 'Lite Cutout Fraction Y', '9/16\"', null, '16');
INSERT INTO `dictionary` VALUES ('1326', 'Lite Cutout Fraction Y', '19/32\"', null, '17');
INSERT INTO `dictionary` VALUES ('1327', 'Lite Cutout Fraction Y', '5/8\"', null, '18');
INSERT INTO `dictionary` VALUES ('1328', 'Lite Cutout Fraction Y', '21/32\"', null, '19');
INSERT INTO `dictionary` VALUES ('1329', 'Lite Cutout Fraction Y', '11/16\"', null, '20');
INSERT INTO `dictionary` VALUES ('1330', 'Lite Cutout Fraction Y', '23/32\"', null, '21');
INSERT INTO `dictionary` VALUES ('1331', 'Lite Cutout Fraction Y', '3/4\"', null, '22');
INSERT INTO `dictionary` VALUES ('1332', 'Lite Cutout Fraction Y', '25/32\"', null, '23');
INSERT INTO `dictionary` VALUES ('1333', 'Lite Cutout Fraction Y', '13/16\"', null, '24');
INSERT INTO `dictionary` VALUES ('1334', 'Lite Cutout Fraction Y', '27/32\"', null, '25');
INSERT INTO `dictionary` VALUES ('1335', 'Lite Cutout Fraction Y', '7/8\"', null, '26');
INSERT INTO `dictionary` VALUES ('1336', 'Lite Cutout Fraction Y', '29/32\"', null, '27');
INSERT INTO `dictionary` VALUES ('1337', 'Lite Cutout Fraction Y', '15/16\"', null, '28');
INSERT INTO `dictionary` VALUES ('1338', 'Lite Cutout Fraction Y', '31/32\"', null, '29');
INSERT INTO `dictionary` VALUES ('1339', 'Lockstile Fraction', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1340', 'Lockstile Fraction', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1341', 'Lockstile Fraction', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1342', 'Lockstile Fraction', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1343', 'Lockstile Fraction', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1344', 'Lockstile Fraction', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1345', 'Lockstile Fraction', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1346', 'Lockstile Fraction', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1347', 'Lockstile Fraction', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1348', 'Lockstile Fraction', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1349', 'Lockstile Fraction', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1350', 'Lockstile Fraction', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1351', 'Lockstile Fraction', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1352', 'Lockstile Fraction', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1353', 'Top Rail Fraction', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1354', 'Top Rail Fraction', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1355', 'Top Rail Fraction', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1356', 'Top Rail Fraction', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1357', 'Top Rail Fraction', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1358', 'Top Rail Fraction', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1359', 'Top Rail Fraction', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1360', 'Top Rail Fraction', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1361', 'Top Rail Fraction', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1362', 'Top Rail Fraction', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1363', 'Top Rail Fraction', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1364', 'Top Rail Fraction', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1365', 'Top Rail Fraction', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1366', 'Top Rail Fraction', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1367', 'Template', 'Mobile DOORDATA v2.1', '521001', '1');
INSERT INTO `dictionary` VALUES ('1369', 'Frame Fraction', '1/2\"', null, '8');
INSERT INTO `dictionary` VALUES ('1370', 'Frame Fraction', '1/4\"', null, '4');
INSERT INTO `dictionary` VALUES ('1371', 'Frame Fraction', '1/8\"', null, '2');
INSERT INTO `dictionary` VALUES ('1372', 'Frame Fraction', '11/16\"', null, '11');
INSERT INTO `dictionary` VALUES ('1373', 'Frame Fraction', '13/16\"', null, '13');
INSERT INTO `dictionary` VALUES ('1374', 'Frame Fraction', '3/16\"', null, '3');
INSERT INTO `dictionary` VALUES ('1375', 'Frame Fraction', '3/4\"', null, '12');
INSERT INTO `dictionary` VALUES ('1376', 'Frame Fraction', '3/8\"', null, '6');
INSERT INTO `dictionary` VALUES ('1377', 'Frame Fraction', '5/16\"', null, '5');
INSERT INTO `dictionary` VALUES ('1378', 'Frame Fraction', '5/8\"', null, '10');
INSERT INTO `dictionary` VALUES ('1379', 'Frame Fraction', '7/16\"', null, '7');
INSERT INTO `dictionary` VALUES ('1380', 'Frame Fraction', '7/8\"', null, '14');
INSERT INTO `dictionary` VALUES ('1381', 'Frame Fraction', '9/16\"', null, '9');
INSERT INTO `dictionary` VALUES ('1382', 'Frame Fraction', '1/16\"', null, '1');
INSERT INTO `dictionary` VALUES ('1383', 'Inspection Status', 'Incomplete', null, null);
INSERT INTO `dictionary` VALUES ('1384', 'Handing', 'Select Hand', null, null);
INSERT INTO `dictionary` VALUES ('1385', 'Handing', 'LHRA/RHRA', null, null);
INSERT INTO `dictionary` VALUES ('1386', 'Handing', 'LH-DA', null, null);
INSERT INTO `dictionary` VALUES ('1387', 'Handing', 'BIFOLD', null, null);
INSERT INTO `dictionary` VALUES ('1388', 'Handing', 'LHR-LHR', null, null);
INSERT INTO `dictionary` VALUES ('1389', 'Handing', 'RH-DA', null, null);
INSERT INTO `dictionary` VALUES ('1390', 'Handing', 'CO', null, null);
INSERT INTO `dictionary` VALUES ('1391', 'Handing', 'RHA', null, null);
INSERT INTO `dictionary` VALUES ('1392', 'Handing', 'LHA', null, null);

-- ----------------------------
-- Table structure for `door`
-- ----------------------------
DROP TABLE IF EXISTS `door`;
CREATE TABLE `door` (
  `ID` int(11) NOT NULL auto_increment,
  `BUILDING_ID` int(11) default NULL,
  `INSPECTION_ID` int(11) default NULL,
  `INSPECTOR_ID` int(11) default NULL,
  `NUMBER` varchar(20) NOT NULL,
  `DOOR_BARCODE` varchar(50) default NULL,
  `TYPE_OTHER` text,
  `STYLE` int(11) default NULL,
  `MATERIAL` int(11) default NULL,
  `MATERIAL_OTHER` text,
  `ELEVATION` int(11) default NULL,
  `ELEVATION_OTHER` text,
  `FRAME_MATERIAL` int(11) default NULL,
  `FRAME_MATERIAL_OTHER` text,
  `FRAME_ELEVATION` int(11) default NULL,
  `FRAME_ELEVATION_OTHER` text,
  `LOCATION` text,
  `FIRE_RATING_1` int(11) default NULL,
  `FIRE_RATING_2` int(11) default NULL,
  `FIRE_RATING_3` int(11) default NULL,
  `FIRE_RATING_4` int(11) default NULL,
  `TEMP_RISE` int(11) default NULL,
  `MANUFACTURER` varchar(100) default NULL,
  `BARCODE` varchar(50) default NULL,
  `REMARKS` text,
  `COMPLIANT` int(11) default NULL,
  `MODEL` varchar(12) default NULL,
  `FRAME_MANUFACTURER` varchar(100) default NULL,
  `RFID` varchar(10) default NULL,
  `LISTING_AGENCY` int(11) default NULL,
  `LISTING_AGENCY_OTHER` text,
  `GAUGE` varchar(10) default NULL,
  `HANDING` int(11) default NULL,
  `INK_STROKES` longtext,
  `HINGE_HEIGHT` int(11) default NULL,
  `HINGE_THICKNESS` int(11) default NULL,
  `HINGE_HEIGHT1` varchar(10) default NULL,
  `HINGE_HEIGHT2` varchar(10) default NULL,
  `HINGE_HEIGHT3` varchar(10) default NULL,
  `HINGE_HEIGHT4` varchar(10) default NULL,
  `HINGE_FRACTION1` int(11) default NULL,
  `HINGE_FRACTION2` int(11) default NULL,
  `HINGE_FRACTION3` int(11) default NULL,
  `HINGE_FRACTION4` int(11) default NULL,
  `HINGE_BACKSET` int(11) default NULL,
  `HINGE_MANUFACTURER` varchar(50) default NULL,
  `HINGE_MANUFACTURER_NO` varchar(10) default NULL,
  `TOP_TO_CENTERLINE` varchar(10) default NULL,
  `TOP_TO_CENTERLINE_FRACTION` int(11) default NULL,
  `LOCK_BACKSET` int(11) default NULL,
  `FRAME_BOTTOM_TO_CENTER` varchar(10) default NULL,
  `STRIKE_HEIGHT` int(11) default NULL,
  `PREFIT_DOOR_SIZE_X` varchar(10) default NULL,
  `PREFIT_FRACTION_X` int(11) default NULL,
  `PREFIT_DOOR_SIZE_Y` varchar(10) default NULL,
  `PREFIT_FRACTION_Y` int(11) default NULL,
  `FRAME_OPENING_SIZE_X` varchar(10) default NULL,
  `FRAME_OPENING_FRACTION_X` int(11) default NULL,
  `FRAME_OPENING_SIZE_Y` varchar(10) default NULL,
  `FRAME_OPENING_FRACTION_Y` int(11) default NULL,
  `LITE_CUTOUT_SIZE_X` varchar(10) default NULL,
  `LITE_CUTOUT_FRACTION_X` int(11) default NULL,
  `LITE_CUTOUT_SIZE_Y` varchar(10) default NULL,
  `LITE_CUTOUT_FRACTION_Y` int(11) default NULL,
  `LOCKSTILE_SIZE` varchar(10) default NULL,
  `LOCKSTILE_FRACTION` int(11) default NULL,
  `TOPRAIL_SIZE` varchar(10) default NULL,
  `TOPRAIL_FRACTION` int(11) default NULL,
  `FRAME_INK_STROKES` text,
  `A` varchar(10) default NULL,
  `A_FRACTION` int(11) default NULL,
  `B` varchar(10) default NULL,
  `B_FRACTION` int(11) default NULL,
  `C` varchar(10) default NULL,
  `C_FRACTION` int(11) default NULL,
  `D` varchar(10) default NULL,
  `D_FRACTION` int(11) default NULL,
  `E` varchar(10) default NULL,
  `E_FRACTION` int(11) default NULL,
  `F` varchar(10) default NULL,
  `F_FRACTION` int(11) default NULL,
  `G` varchar(10) default NULL,
  `G_FRACTION` int(11) default NULL,
  `H` varchar(10) default NULL,
  `H_FRACTION` int(11) default NULL,
  `I` varchar(10) default NULL,
  `I_FRACTION` int(11) default NULL,
  `J` varchar(10) default NULL,
  `J_FRACTION` int(11) default NULL,
  `K` varchar(10) default NULL,
  `K_FRACTION` int(11) default NULL,
  `L` varchar(10) default NULL,
  `L_FRACTION` int(11) default NULL,
  `M` varchar(10) default NULL,
  `M_FRACTION` int(11) default NULL,
  `N` varchar(10) default NULL,
  `N_FRACTION` int(11) default NULL,
  `O` varchar(10) default NULL,
  `O_FRACTION` int(11) default NULL,
  `P` varchar(10) default NULL,
  `P_FRACTION` int(11) default NULL,
  `Q` varchar(10) default NULL,
  `Q_FRACTION` int(11) default NULL,
  `R` varchar(10) default NULL,
  `R_FRACTION` int(11) default NULL,
  `S` varchar(10) default NULL,
  `S_FRACTION` int(11) default NULL,
  `T` varchar(10) default NULL,
  `T_FRACTION` int(11) default NULL,
  `U` varchar(10) default NULL,
  `U_FRACTION` int(11) default NULL,
  `V` varchar(10) default NULL,
  `V_FRACTION` int(11) default NULL,
  `HARDWARE_GROUP` varchar(12) default NULL,
  `HARDWARE_SET` varchar(8) default NULL,
  PRIMARY KEY  (`ID`),
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
  CONSTRAINT `door_ibfk_29` FOREIGN KEY (`FIRE_RATING_2`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_30` FOREIGN KEY (`FIRE_RATING_1`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_31` FOREIGN KEY (`FIRE_RATING_3`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_32` FOREIGN KEY (`FIRE_RATING_4`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_34` FOREIGN KEY (`MATERIAL`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_35` FOREIGN KEY (`ELEVATION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_36` FOREIGN KEY (`FRAME_ELEVATION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_37` FOREIGN KEY (`STYLE`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_38` FOREIGN KEY (`TEMP_RISE`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_39` FOREIGN KEY (`FRAME_MATERIAL`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_42` FOREIGN KEY (`LISTING_AGENCY`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_44` FOREIGN KEY (`INSPECTOR_ID`) REFERENCES `employee` (`ID`),
  CONSTRAINT `door_ibfk_45` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `door_ibfk_46` FOREIGN KEY (`BUILDING_ID`) REFERENCES `building` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `door_ibfk_47` FOREIGN KEY (`HANDING`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_48` FOREIGN KEY (`HINGE_HEIGHT`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_49` FOREIGN KEY (`HINGE_THICKNESS`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_50` FOREIGN KEY (`HINGE_FRACTION1`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_51` FOREIGN KEY (`HINGE_FRACTION2`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_52` FOREIGN KEY (`HINGE_FRACTION3`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_53` FOREIGN KEY (`HINGE_FRACTION4`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_54` FOREIGN KEY (`HINGE_BACKSET`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_55` FOREIGN KEY (`TOP_TO_CENTERLINE_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_56` FOREIGN KEY (`STRIKE_HEIGHT`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_57` FOREIGN KEY (`PREFIT_FRACTION_X`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_58` FOREIGN KEY (`PREFIT_FRACTION_Y`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_59` FOREIGN KEY (`FRAME_OPENING_FRACTION_X`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_60` FOREIGN KEY (`FRAME_OPENING_FRACTION_Y`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_61` FOREIGN KEY (`LITE_CUTOUT_FRACTION_X`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_62` FOREIGN KEY (`LITE_CUTOUT_FRACTION_Y`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_63` FOREIGN KEY (`LOCKSTILE_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_64` FOREIGN KEY (`TOPRAIL_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_65` FOREIGN KEY (`LOCK_BACKSET`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_66` FOREIGN KEY (`COMPLIANT`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_67` FOREIGN KEY (`A_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_68` FOREIGN KEY (`B_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_69` FOREIGN KEY (`C_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_70` FOREIGN KEY (`D_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_71` FOREIGN KEY (`E_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_72` FOREIGN KEY (`F_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_73` FOREIGN KEY (`G_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_74` FOREIGN KEY (`H_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_75` FOREIGN KEY (`I_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_76` FOREIGN KEY (`J_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_77` FOREIGN KEY (`K_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_78` FOREIGN KEY (`L_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_79` FOREIGN KEY (`M_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_80` FOREIGN KEY (`N_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_82` FOREIGN KEY (`O_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_83` FOREIGN KEY (`P_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_84` FOREIGN KEY (`Q_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_85` FOREIGN KEY (`R_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_86` FOREIGN KEY (`S_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_87` FOREIGN KEY (`T_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_88` FOREIGN KEY (`U_FRACTION`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `door_ibfk_89` FOREIGN KEY (`V_FRACTION`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of door
-- ----------------------------
INSERT INTO `door` VALUES ('1', '5', '5', '5', '155', null, null, null, '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', null, null, null, null, null, null, null, '136', null, null, null, '1052', null, '20', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, null, null, '12', '12', '100', '1100', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', null, '12', null, null, null, null, null, null, null, null, null, null, '1380', null, '1373', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', '1372', '12', null, null, '1377', null, null, null, null);
INSERT INTO `door` VALUES ('2', '11', '13', '1', '123', '73645', null, '1005', '1015', null, '1019', null, '1028', null, '1031', null, 'Balcony', '1035', '1042', '1046', '1047', '1048', 'door manufactur', '82376482', 'Remarks for door', '136', '765', 'frame maker', null, '1052', null, '12', '1066', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"&gt;&lt;Stroke&gt;&lt;Points&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.536734675813236&lt;/X&gt;&lt;Y&gt;10.037748954933287&lt;/Y&gt;&lt;Pressure&gt;40&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.536734675813236&lt;/X&gt;&lt;Y&gt;10.037748954933287&lt;/Y&gt;&lt;Pressure&gt;56&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.536734675813236&lt;/X&gt;&lt;Y&gt;10.037748954933287&lt;/Y&gt;&lt;Pressure&gt;73&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;91&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;97&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;101&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;105&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;106&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;106&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;101&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.521895994689093&lt;/X&gt;&lt;Y&gt;10.038985283170131&lt;/Y&gt;&lt;Pressure&gt;88&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.515713210887366&lt;/X&gt;&lt;Y&gt;10.053821222012255&lt;/Y&gt;&lt;Pressure&gt;70&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.515713210887366&lt;/X&gt;&lt;Y&gt;10.053821222012255&lt;/Y&gt;&lt;Pressure&gt;52&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.515713210887366&lt;/X&gt;&lt;Y&gt;10.053821222012255&lt;/Y&gt;&lt;Pressure&gt;35&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;20.515713210887366&lt;/X&gt;&lt;Y&gt;10.053821222012255&lt;/Y&gt;&lt;Pressure&gt;17&lt;/Pressure&gt;&lt;/anyType&gt;&lt;/Points&gt;&lt;Color&gt;&lt;Red&gt;0&lt;/Red&gt;&lt;Green&gt;0&lt;/Green&gt;&lt;Blue&gt;0&lt;/Blue&gt;&lt;/Color&gt;&lt;Width&gt;53&lt;/Width&gt;&lt;Transparency&gt;0&lt;/Transparency&gt;&lt;Timestamp&gt;2009-06-26T13:59:53.046875&lt;/Timestamp&gt;&lt;/Stroke&gt;&lt;/ArrayOfStroke&gt;', '1082', '1088', '1', '12', '12', '100', '1090', '1108', '1127', '1136', '1147', '1234567890', '12345678', '12', '1154', '1159', '12', '1162', '12', '1176', '123', '1222', '12', '1240', '123', '1272', '12', '1302', '123', '1312', '12', '1348', '23', '1353', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('3', '11', '13', null, '144', null, null, '1004', '1016', null, '1019', 'other', '1062', null, '1031', null, null, '1035', '1043', '1046', '1047', '1050', null, null, null, '135', null, null, null, '1054', null, null, '1068', null, '1083', '1088', null, null, null, null, '1093', '1116', '1127', '1141', '1147', null, null, null, '1151', '1157', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('4', '5', '5', null, '222', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', '1042', null, null, null, null, null, null, null, null, null, null, null, null, null, '1387', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('7', '5', '5', null, '434', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, '1046', '1047', null, null, null, null, '136', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('11', '5', '5', null, '123', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('17', '5', '21', '5', '155', null, null, null, '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', null, null, null, null, null, null, null, null, null, null, null, '1052', null, '20', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, null, null, '12', '12', '100', '1100', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', null, '12', null, null, null, null, null, null, null, null, null, null, '1380', null, '1373', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', '1372', '12', null, null, '1377', null, null, null, null);
INSERT INTO `door` VALUES ('18', '5', '21', null, '222', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', '1042', null, null, null, null, null, null, null, null, null, null, null, null, null, '1387', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('19', '5', '21', null, '434', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, '1046', '1047', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('20', '5', '21', null, '123', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('21', '5', '22', '5', '155', null, null, null, '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', null, null, null, null, null, null, null, null, null, null, null, '1052', null, '20', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, null, null, '12', '12', '100', '1100', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', null, '12', null, null, null, null, null, null, null, null, null, null, '1380', null, '1373', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', '1372', '12', null, null, '1377', null, null, null, null);
INSERT INTO `door` VALUES ('22', '5', '22', null, '222', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', '1042', null, null, null, null, null, null, null, null, null, null, null, null, null, '1387', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('23', '5', '22', null, '434', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, '1046', '1047', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('24', '5', '22', null, '123', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('25', '5', '23', '5', '155', null, null, null, '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', null, null, null, null, null, null, null, '136', null, null, null, '1052', null, '20', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, null, null, '12', '12', '100', '1100', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', null, '12', null, null, null, null, null, null, null, null, null, null, '1380', null, '1373', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', '1372', '12', null, null, '1377', null, null, null, null);
INSERT INTO `door` VALUES ('26', '5', '23', null, '434', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, '1046', '1047', null, null, null, null, '136', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `door` VALUES ('27', '5', '24', '5', '155', null, null, null, '1015', null, '1019', null, '1028', null, '1030', null, null, '1035', null, null, null, null, null, null, null, '136', null, null, null, '1052', null, '20', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, null, null, '12', '12', '100', '1100', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', null, '12', null, null, null, null, null, null, null, null, null, null, '1380', null, '1373', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '12', '1372', '12', null, null, '1377', null, null, null, null);
INSERT INTO `door` VALUES ('28', '5', '24', null, '434', null, null, '1003', '1015', null, '1019', null, '1028', null, '1030', null, null, null, null, '1046', '1047', null, null, null, null, '136', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);

-- ----------------------------
-- Table structure for `door_code`
-- ----------------------------
DROP TABLE IF EXISTS `door_code`;
CREATE TABLE `door_code` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `CODE_ID` int(11) NOT NULL,
  `ACTIVE` bit(1) default NULL,
  `CONTROL_NAME` varchar(50) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `DOOR_ID` (`DOOR_ID`,`CODE_ID`),
  KEY `CODE_ID` (`CODE_ID`),
  CONSTRAINT `door_code_ibfk_5` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `door_code_ibfk_6` FOREIGN KEY (`CODE_ID`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of door_code
-- ----------------------------
INSERT INTO `door_code` VALUES ('106', '2', '17', null, 'DoorChecks_1_000');
INSERT INTO `door_code` VALUES ('107', '2', '59', null, 'BoltsChecks_002');
INSERT INTO `door_code` VALUES ('108', '2', '60', null, 'BoltsChecks_003');
INSERT INTO `door_code` VALUES ('109', '3', '21', null, null);
INSERT INTO `door_code` VALUES ('110', '3', '58', null, null);
INSERT INTO `door_code` VALUES ('174', '7', '1', null, null);
INSERT INTO `door_code` VALUES ('285', '1', '1', null, null);
INSERT INTO `door_code` VALUES ('286', '1', '8', null, null);
INSERT INTO `door_code` VALUES ('287', '1', '10', null, null);
INSERT INTO `door_code` VALUES ('288', '1', '19', null, null);
INSERT INTO `door_code` VALUES ('289', '1', '23', null, null);
INSERT INTO `door_code` VALUES ('290', '1', '24', null, null);
INSERT INTO `door_code` VALUES ('291', '1', '29', null, null);
INSERT INTO `door_code` VALUES ('292', '1', '39', null, null);
INSERT INTO `door_code` VALUES ('293', '1', '49', null, null);
INSERT INTO `door_code` VALUES ('294', '1', '54', null, null);
INSERT INTO `door_code` VALUES ('295', '1', '64', null, null);
INSERT INTO `door_code` VALUES ('296', '1', '73', null, null);
INSERT INTO `door_code` VALUES ('297', '1', '80', null, null);
INSERT INTO `door_code` VALUES ('298', '1', '82', null, null);
INSERT INTO `door_code` VALUES ('299', '1', '87', null, null);
INSERT INTO `door_code` VALUES ('300', '1', '99', null, null);
INSERT INTO `door_code` VALUES ('301', '1', '104', null, null);
INSERT INTO `door_code` VALUES ('302', '1', '116', null, null);
INSERT INTO `door_code` VALUES ('303', '1', '119', null, null);
INSERT INTO `door_code` VALUES ('304', '1', '125', null, null);
INSERT INTO `door_code` VALUES ('305', '1', '129', null, null);
INSERT INTO `door_code` VALUES ('306', '1', '132', null, null);
INSERT INTO `door_code` VALUES ('307', '25', '1', null, null);
INSERT INTO `door_code` VALUES ('308', '25', '8', null, null);
INSERT INTO `door_code` VALUES ('309', '25', '10', null, null);
INSERT INTO `door_code` VALUES ('310', '25', '19', null, null);
INSERT INTO `door_code` VALUES ('311', '25', '23', null, null);
INSERT INTO `door_code` VALUES ('312', '25', '24', null, null);
INSERT INTO `door_code` VALUES ('313', '25', '29', null, null);
INSERT INTO `door_code` VALUES ('314', '25', '39', null, null);
INSERT INTO `door_code` VALUES ('315', '25', '49', null, null);
INSERT INTO `door_code` VALUES ('316', '25', '54', null, null);
INSERT INTO `door_code` VALUES ('317', '25', '64', null, null);
INSERT INTO `door_code` VALUES ('318', '25', '73', null, null);
INSERT INTO `door_code` VALUES ('319', '25', '80', null, null);
INSERT INTO `door_code` VALUES ('320', '25', '82', null, null);
INSERT INTO `door_code` VALUES ('321', '25', '87', null, null);
INSERT INTO `door_code` VALUES ('322', '25', '99', null, null);
INSERT INTO `door_code` VALUES ('323', '25', '104', null, null);
INSERT INTO `door_code` VALUES ('324', '25', '116', null, null);
INSERT INTO `door_code` VALUES ('325', '25', '119', null, null);
INSERT INTO `door_code` VALUES ('326', '25', '125', null, null);
INSERT INTO `door_code` VALUES ('327', '25', '129', null, null);
INSERT INTO `door_code` VALUES ('328', '25', '132', null, null);
INSERT INTO `door_code` VALUES ('329', '26', '1', null, null);
INSERT INTO `door_code` VALUES ('330', '27', '1', null, null);
INSERT INTO `door_code` VALUES ('331', '27', '8', null, null);
INSERT INTO `door_code` VALUES ('332', '27', '10', null, null);
INSERT INTO `door_code` VALUES ('333', '27', '19', null, null);
INSERT INTO `door_code` VALUES ('334', '27', '23', null, null);
INSERT INTO `door_code` VALUES ('335', '27', '24', null, null);
INSERT INTO `door_code` VALUES ('336', '27', '29', null, null);
INSERT INTO `door_code` VALUES ('337', '27', '39', null, null);
INSERT INTO `door_code` VALUES ('338', '27', '49', null, null);
INSERT INTO `door_code` VALUES ('339', '27', '54', null, null);
INSERT INTO `door_code` VALUES ('340', '27', '64', null, null);
INSERT INTO `door_code` VALUES ('341', '27', '73', null, null);
INSERT INTO `door_code` VALUES ('342', '27', '80', null, null);
INSERT INTO `door_code` VALUES ('343', '27', '82', null, null);
INSERT INTO `door_code` VALUES ('344', '27', '87', null, null);
INSERT INTO `door_code` VALUES ('345', '27', '99', null, null);
INSERT INTO `door_code` VALUES ('346', '27', '104', null, null);
INSERT INTO `door_code` VALUES ('347', '27', '116', null, null);
INSERT INTO `door_code` VALUES ('348', '27', '119', null, null);
INSERT INTO `door_code` VALUES ('349', '27', '125', null, null);
INSERT INTO `door_code` VALUES ('350', '27', '129', null, null);
INSERT INTO `door_code` VALUES ('351', '27', '132', null, null);
INSERT INTO `door_code` VALUES ('352', '28', '1', null, null);

-- ----------------------------
-- Table structure for `door_note`
-- ----------------------------
DROP TABLE IF EXISTS `door_note`;
CREATE TABLE `door_note` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `NOTE` longtext,
  `CONTROL_NAME` varchar(50) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `door_note_ibfk_3` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of door_note
-- ----------------------------

-- ----------------------------
-- Table structure for `door_type`
-- ----------------------------
DROP TABLE IF EXISTS `door_type`;
CREATE TABLE `door_type` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `TYPE_ID` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  KEY `TYPE_ID` (`TYPE_ID`),
  CONSTRAINT `door_type_ibfk_3` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `door_type_ibfk_4` FOREIGN KEY (`TYPE_ID`) REFERENCES `dictionary` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of door_type
-- ----------------------------
INSERT INTO `door_type` VALUES ('40', '2', '1011');
INSERT INTO `door_type` VALUES ('41', '2', '1012');
INSERT INTO `door_type` VALUES ('42', '3', '1008');
INSERT INTO `door_type` VALUES ('43', '3', '1012');
INSERT INTO `door_type` VALUES ('52', '7', '1006');
INSERT INTO `door_type` VALUES ('54', '11', '1006');
INSERT INTO `door_type` VALUES ('65', '1', '1006');
INSERT INTO `door_type` VALUES ('66', '1', '1007');
INSERT INTO `door_type` VALUES ('67', '17', '1006');
INSERT INTO `door_type` VALUES ('68', '17', '1007');
INSERT INTO `door_type` VALUES ('69', '19', '1006');
INSERT INTO `door_type` VALUES ('70', '20', '1006');
INSERT INTO `door_type` VALUES ('71', '21', '1006');
INSERT INTO `door_type` VALUES ('72', '21', '1007');
INSERT INTO `door_type` VALUES ('73', '23', '1006');
INSERT INTO `door_type` VALUES ('74', '24', '1006');
INSERT INTO `door_type` VALUES ('75', '25', '1006');
INSERT INTO `door_type` VALUES ('76', '25', '1007');
INSERT INTO `door_type` VALUES ('77', '26', '1006');
INSERT INTO `door_type` VALUES ('78', '27', '1006');
INSERT INTO `door_type` VALUES ('79', '27', '1007');
INSERT INTO `door_type` VALUES ('80', '28', '1006');

-- ----------------------------
-- Table structure for `download`
-- ----------------------------
DROP TABLE IF EXISTS `download`;
CREATE TABLE `download` (
  `ID` int(11) NOT NULL auto_increment,
  `EMAIL` varchar(100) NOT NULL,
  `HASH` varchar(32) NOT NULL,
  `CREATE_DATE` datetime NOT NULL,
  `DOWNLOADED` int(11) NOT NULL default '136',
  PRIMARY KEY  (`ID`),
  KEY `DOWNLOADED` (`DOWNLOADED`),
  CONSTRAINT `download_ibfk_1` FOREIGN KEY (`DOWNLOADED`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of download
-- ----------------------------

-- ----------------------------
-- Table structure for `email`
-- ----------------------------
DROP TABLE IF EXISTS `email`;
CREATE TABLE `email` (
  `id` int(11) NOT NULL auto_increment,
  `identity` varchar(20) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of email
-- ----------------------------
INSERT INTO `email` VALUES ('1', 'password generation', 'Your Online DOORDATA account', 'Hi [==NAME==],\n\nWelcome to the Online DOORDATA service! To login to the system please use the following credentials:\n      application link: http://onlinedoordata.com\n      login: [==LOGIN==]\n      password: [==PASSWORD==]\n\nThank you,\nOnline DOORDATA');
INSERT INTO `email` VALUES ('2', 'client download link', 'Doordata client download link', 'To download the Doordata client software to your computer, click the following link\r\n[==LINK==].\r\nThis link is available for 24 hours and only for one download.');

-- ----------------------------
-- Table structure for `employee`
-- ----------------------------
DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee` (
  `ID` int(11) NOT NULL auto_increment,
  `FIRST_NAME` varchar(50) default NULL,
  `LAST_NAME` varchar(50) NOT NULL,
  `LAST_LOGIN` date default NULL,
  `LICENSE_NUMBER` varchar(50) default NULL,
  `EXPIRATION_DATE` date default NULL,
  `USER_ID` int(11) default NULL,
  `COMPANY_ID` int(11) default NULL,
  `EMAIL` varchar(255) default NULL,
  `PHONE` varchar(50) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `USER_ID` (`USER_ID`),
  KEY `COMPANY_ID` (`COMPANY_ID`),
  KEY `ID_2` (`ID`),
  KEY `ID_3` (`ID`),
  CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `employee_ibfk_4` FOREIGN KEY (`COMPANY_ID`) REFERENCES `company` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of employee
-- ----------------------------
INSERT INTO `employee` VALUES ('1', 'System', 'Adminstrator', '2012-11-15', '123456', '2010-06-01', '1', '1', null, '999-999');
INSERT INTO `employee` VALUES ('5', 'webservice', 'Webservice', '2009-07-08', '123456', '2010-05-01', null, '5', null, '999-999');
INSERT INTO `employee` VALUES ('7', 'Inspector', 'Inspector', '2009-08-12', null, null, '6', '7', null, '999-999');
INSERT INTO `employee` VALUES ('36', 'Donald', 'Duck', null, null, null, '19', '6', null, '999-999');
INSERT INTO `employee` VALUES ('37', 'Panda', 'Medvedeva', '2009-08-12', null, null, '20', '6', null, '999-999');
INSERT INTO `employee` VALUES ('38', 'Mike', 'Malkovich', null, null, null, '21', '5', null, null);
INSERT INTO `employee` VALUES ('39', 'Tim', 'Robbins', null, null, null, '22', '7', null, null);
INSERT INTO `employee` VALUES ('40', 'Igor', 'Kaynov', '2012-11-16', null, null, '24', '7', null, null);
INSERT INTO `employee` VALUES ('41', 'test', 'test', '2012-11-12', null, null, '26', '10', null, null);
INSERT INTO `employee` VALUES ('42', 'Igor', 'Kaynov', '2012-11-12', null, null, '27', '10', null, null);

-- ----------------------------
-- Table structure for `floorplan`
-- ----------------------------
DROP TABLE IF EXISTS `floorplan`;
CREATE TABLE `floorplan` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `INK_STROKES` longtext,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `floorplan_ibfk_2` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of floorplan
-- ----------------------------
INSERT INTO `floorplan` VALUES ('3', '1', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;');
INSERT INTO `floorplan` VALUES ('4', '2', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;');
INSERT INTO `floorplan` VALUES ('5', '17', null);
INSERT INTO `floorplan` VALUES ('6', '21', null);
INSERT INTO `floorplan` VALUES ('7', '25', null);
INSERT INTO `floorplan` VALUES ('8', '27', null);

-- ----------------------------
-- Table structure for `framedetail`
-- ----------------------------
DROP TABLE IF EXISTS `framedetail`;
CREATE TABLE `framedetail` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `INK_STROKES` longtext,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `framedetail_ibfk_2` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of framedetail
-- ----------------------------
INSERT INTO `framedetail` VALUES ('3', '1', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;');
INSERT INTO `framedetail` VALUES ('4', '2', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;');

-- ----------------------------
-- Table structure for `hardware`
-- ----------------------------
DROP TABLE IF EXISTS `hardware`;
CREATE TABLE `hardware` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `ITEM_ID` int(11) default NULL,
  `VERIFY` varchar(20) default NULL,
  `QTY` varchar(4) default NULL,
  `ITEM` varchar(12) default NULL,
  `PRODUCT` varchar(16) default NULL,
  `MFG` varchar(3) default NULL,
  `FINISH` varchar(3) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `hardware_ibfk_2` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of hardware
-- ----------------------------
INSERT INTO `hardware` VALUES ('2', '1', null, '0', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('3', '1', null, '0', '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('4', '1', null, '0', '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('5', '1', null, '0', '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('7', '1', null, '0', '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('13', '2', null, '1', '1', null, null, null, null);
INSERT INTO `hardware` VALUES ('14', '2', null, '1', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('15', '2', null, null, '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('16', '2', null, null, '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('17', '2', null, null, '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('18', '2', null, null, '6', null, null, null, null);
INSERT INTO `hardware` VALUES ('19', '2', null, null, '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('20', '2', null, null, '8', null, null, null, null);
INSERT INTO `hardware` VALUES ('21', '2', null, null, '9', null, null, null, null);
INSERT INTO `hardware` VALUES ('22', '2', null, null, '10', null, null, null, null);
INSERT INTO `hardware` VALUES ('23', '2', null, null, '11', null, null, null, null);
INSERT INTO `hardware` VALUES ('24', '2', null, null, '12', null, null, null, null);
INSERT INTO `hardware` VALUES ('37', '1', null, null, '8', '123', null, null, null);
INSERT INTO `hardware` VALUES ('38', '17', null, '0', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('39', '17', null, '0', '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('40', '17', null, '0', '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('41', '17', null, '0', '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('42', '17', null, '0', '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('43', '17', null, null, '8', '123', null, null, null);
INSERT INTO `hardware` VALUES ('44', '21', null, '0', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('45', '21', null, '0', '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('46', '21', null, '0', '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('47', '21', null, '0', '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('48', '21', null, '0', '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('49', '21', null, null, '8', '123', null, null, null);
INSERT INTO `hardware` VALUES ('50', '25', null, '0', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('51', '25', null, '0', '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('52', '25', null, '0', '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('53', '25', null, '0', '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('54', '25', null, '0', '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('55', '25', null, null, '8', '123', null, null, null);
INSERT INTO `hardware` VALUES ('56', '27', null, '0', '2', null, null, null, null);
INSERT INTO `hardware` VALUES ('57', '27', null, '0', '3', null, null, null, null);
INSERT INTO `hardware` VALUES ('58', '27', null, '0', '4', null, null, null, null);
INSERT INTO `hardware` VALUES ('59', '27', null, '0', '5', null, null, null, null);
INSERT INTO `hardware` VALUES ('60', '27', null, '0', '7', null, null, null, null);
INSERT INTO `hardware` VALUES ('61', '27', null, null, '8', '123', null, null, null);

-- ----------------------------
-- Table structure for `ink`
-- ----------------------------
DROP TABLE IF EXISTS `ink`;
CREATE TABLE `ink` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) NOT NULL,
  `INK_STROKE` longtext,
  `FORM_NUM` int(11) default NULL,
  `CONTROL_NAME` varchar(50) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `ink_ibfk_2` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of ink
-- ----------------------------
INSERT INTO `ink` VALUES ('1', '2', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '3', 'InspectionNotes4_1');
INSERT INTO `ink` VALUES ('2', '2', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '3', 'InspectionNotes4_2');

-- ----------------------------
-- Table structure for `inspect`
-- ----------------------------
DROP TABLE IF EXISTS `inspect`;
CREATE TABLE `inspect` (
  `ID` int(11) NOT NULL auto_increment,
  `INSPECTOR_ID` int(11) NOT NULL,
  `INSPECTION_ID` int(11) NOT NULL,
  `ASSIGNED_DATE` date default NULL,
  `COMMENTS` text,
  PRIMARY KEY  (`ID`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  KEY `INSPECTOR_ID` (`INSPECTOR_ID`,`INSPECTION_ID`),
  CONSTRAINT `inspect_ibfk_3` FOREIGN KEY (`INSPECTOR_ID`) REFERENCES `employee` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `inspect_ibfk_4` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of inspect
-- ----------------------------
INSERT INTO `inspect` VALUES ('9', '5', '5', null, null);
INSERT INTO `inspect` VALUES ('10', '1', '13', null, null);
INSERT INTO `inspect` VALUES ('11', '7', '20', null, null);
INSERT INTO `inspect` VALUES ('12', '7', '13', null, null);
INSERT INTO `inspect` VALUES ('14', '1', '5', '2009-10-12', null);
INSERT INTO `inspect` VALUES ('15', '1', '15', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('16', '5', '5', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('18', '5', '15', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('19', '1', '5', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('20', '5', '5', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('21', '1', '5', '2009-10-13', null);
INSERT INTO `inspect` VALUES ('22', '1', '12', '2012-11-06', null);
INSERT INTO `inspect` VALUES ('24', '1', '5', '2012-11-13', null);
INSERT INTO `inspect` VALUES ('25', '1', '5', '2012-11-14', null);
INSERT INTO `inspect` VALUES ('26', '1', '21', '2012-11-14', null);
INSERT INTO `inspect` VALUES ('27', '1', '22', '2012-11-14', null);
INSERT INTO `inspect` VALUES ('28', '1', '23', '2012-11-14', null);
INSERT INTO `inspect` VALUES ('29', '1', '24', '2012-11-14', null);
INSERT INTO `inspect` VALUES ('30', '40', '13', '2012-11-16', null);
INSERT INTO `inspect` VALUES ('31', '40', '17', '2012-11-16', null);
INSERT INTO `inspect` VALUES ('32', '40', '20', '2012-11-16', null);

-- ----------------------------
-- Table structure for `inspection`
-- ----------------------------
DROP TABLE IF EXISTS `inspection`;
CREATE TABLE `inspection` (
  `ID` int(11) NOT NULL auto_increment,
  `INSPECTION_DATE` date default NULL,
  `INSPECTION_COMPLETE_DATE` date default NULL,
  `REINSPECT_DATE` date default NULL,
  `BUILDING_ID` int(11) NOT NULL,
  `COMPANY_ID` int(11) NOT NULL,
  `SIGNATURE_INSPECTOR` varchar(100) default NULL,
  `SIGNATURE_STROKES_INSPECTOR` longtext,
  `SIGNATURE_BUILDING` varchar(100) default NULL,
  `SIGNATURE_STROKES_BUILDING` longtext,
  `STATUS` int(11) default NULL,
  `SUMMARY` text,
  `PDF` text,
  `INSPECTOR_ID` int(11) default NULL,
  `TEMPLATE_ID` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `UI_ID` (`ID`),
  KEY `FK_BUILDING_ID` (`BUILDING_ID`),
  KEY `STATUS` (`STATUS`),
  KEY `COMPANY_ID` (`COMPANY_ID`),
  KEY `TEMPLATE_ID` (`TEMPLATE_ID`),
  CONSTRAINT `inspection_ibfk_2` FOREIGN KEY (`STATUS`) REFERENCES `dictionary` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_4` FOREIGN KEY (`BUILDING_ID`) REFERENCES `building` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_6` FOREIGN KEY (`COMPANY_ID`) REFERENCES `company` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `inspection_ibfk_7` FOREIGN KEY (`TEMPLATE_ID`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of inspection
-- ----------------------------
INSERT INTO `inspection` VALUES ('5', null, null, null, '5', '5', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', null, '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '1077', 'Summary of Chris Building dfg dfg dgd fgdfgdfgdfgdfgdfgdfgd gd gdfgdfg dfgdfgdfgdfgdgdgdg dg dg dfgdfg dg dfffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff ddddddddddddddddddddddg dfg df gdfg dfgfddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddfg dfg dfs f sf sf sd', '/public/reports/report20091014034653.pdf', '38', '1367');
INSERT INTO `inspection` VALUES ('12', '2009-06-11', '2009-06-28', '2009-06-30', '10', '5', null, null, null, null, null, null, '/public/reports/report20090706132216.pdf', null, null);
INSERT INTO `inspection` VALUES ('13', '2009-06-10', '2009-06-26', '2010-06-26', '11', '7', '/public/pictures/sign_inspector_6.jpeg', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"&gt;&lt;Stroke&gt;&lt;Points&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.2724169063952202&lt;/X&gt;&lt;Y&gt;19.487005669129015&lt;/Y&gt;&lt;Pressure&gt;55&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.2724169063952202&lt;/X&gt;&lt;Y&gt;19.487005669129015&lt;/Y&gt;&lt;Pressure&gt;77&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.2724169063952202&lt;/X&gt;&lt;Y&gt;19.487005669129015&lt;/Y&gt;&lt;Pressure&gt;101&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3119867227262669&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;125&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3119867227262669&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;137&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3218791768090286&lt;/X&gt;&lt;Y&gt;19.318865028918285&lt;/Y&gt;&lt;Pressure&gt;150&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3218791768090286&lt;/X&gt;&lt;Y&gt;19.318865028918285&lt;/Y&gt;&lt;Pressure&gt;161&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3317716308917902&lt;/X&gt;&lt;Y&gt;19.215013457023421&lt;/Y&gt;&lt;Pressure&gt;168&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3317716308917902&lt;/X&gt;&lt;Y&gt;19.215013457023421&lt;/Y&gt;&lt;Pressure&gt;175&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3342447444124808&lt;/X&gt;&lt;Y&gt;19.101271259233808&lt;/Y&gt;&lt;Pressure&gt;180&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3342447444124808&lt;/X&gt;&lt;Y&gt;19.101271259233808&lt;/Y&gt;&lt;Pressure&gt;185&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3292985173710998&lt;/X&gt;&lt;Y&gt;18.981347420259979&lt;/Y&gt;&lt;Pressure&gt;189&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3292985173710998&lt;/X&gt;&lt;Y&gt;18.981347420259979&lt;/Y&gt;&lt;Pressure&gt;193&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3206426200486834&lt;/X&gt;&lt;Y&gt;18.865132565996678&lt;/Y&gt;&lt;Pressure&gt;197&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3206426200486834&lt;/X&gt;&lt;Y&gt;18.865132565996678&lt;/Y&gt;&lt;Pressure&gt;200&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;203&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;205&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;208&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;210&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;211&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;212&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;213&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3107501659659218&lt;/X&gt;&lt;Y&gt;18.778589589417624&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.2983845983624696&lt;/X&gt;&lt;Y&gt;18.910876710759894&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3095136092055766&lt;/X&gt;&lt;Y&gt;18.997419687338947&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3169329497676479&lt;/X&gt;&lt;Y&gt;19.092616961575903&lt;/Y&gt;&lt;Pressure&gt;215&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3330081876521356&lt;/X&gt;&lt;Y&gt;19.191523220523393&lt;/Y&gt;&lt;Pressure&gt;215&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3490834255366231&lt;/X&gt;&lt;Y&gt;19.289193151234038&lt;/Y&gt;&lt;Pressure&gt;216&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3676317769418014&lt;/X&gt;&lt;Y&gt;19.376972456049934&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3849435715866343&lt;/X&gt;&lt;Y&gt;19.457333791444771&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.3849435715866343&lt;/X&gt;&lt;Y&gt;19.457333791444771&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.4319327284797521&lt;/X&gt;&lt;Y&gt;19.569839660997538&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.4319327284797521&lt;/X&gt;&lt;Y&gt;19.569839660997538&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.4319327284797521&lt;/X&gt;&lt;Y&gt;19.569839660997538&lt;/Y&gt;&lt;Pressure&gt;216&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.5147820314228813&lt;/X&gt;&lt;Y&gt;19.653909981102903&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.5147820314228813&lt;/X&gt;&lt;Y&gt;19.653909981102903&lt;/Y&gt;&lt;Pressure&gt;212&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.5147820314228813&lt;/X&gt;&lt;Y&gt;19.653909981102903&lt;/Y&gt;&lt;Pressure&gt;209&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6038141181677363&lt;/X&gt;&lt;Y&gt;19.651437324629217&lt;/Y&gt;&lt;Pressure&gt;207&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6038141181677363&lt;/X&gt;&lt;Y&gt;19.651437324629217&lt;/Y&gt;&lt;Pressure&gt;206&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6718247399867228&lt;/X&gt;&lt;Y&gt;19.599511538681785&lt;/Y&gt;&lt;Pressure&gt;204&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6718247399867228&lt;/X&gt;&lt;Y&gt;19.599511538681785&lt;/Y&gt;&lt;Pressure&gt;203&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.7361256915246737&lt;/X&gt;&lt;Y&gt;19.51544121857642&lt;/Y&gt;&lt;Pressure&gt;203&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.7361256915246737&lt;/X&gt;&lt;Y&gt;19.51544121857642&lt;/Y&gt;&lt;Pressure&gt;203&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.7954804160212439&lt;/X&gt;&lt;Y&gt;19.420243944339461&lt;/Y&gt;&lt;Pressure&gt;204&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.7954804160212439&lt;/X&gt;&lt;Y&gt;19.420243944339461&lt;/Y&gt;&lt;Pressure&gt;205&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.8362867891126355&lt;/X&gt;&lt;Y&gt;19.327519326576191&lt;/Y&gt;&lt;Pressure&gt;207&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.8362867891126355&lt;/X&gt;&lt;Y&gt;19.327519326576191&lt;/Y&gt;&lt;Pressure&gt;208&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;210&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;211&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;213&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;215&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.856071697278159&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;216&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.24221267823398&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.24221267823398&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.24221267823398&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.715104226598805&lt;/X&gt;&lt;Y&gt;19.336173624234096&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.715104226598805&lt;/X&gt;&lt;Y&gt;19.336173624234096&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6953193184332818&lt;/X&gt;&lt;Y&gt;19.410353318444709&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6953193184332818&lt;/X&gt;&lt;Y&gt;19.410353318444709&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6953193184332818&lt;/X&gt;&lt;Y&gt;19.410353318444709&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6990289887143175&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;218&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6990289887143175&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;219&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6990289887143175&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;219&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.6990289887143175&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;220&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.527804500944853&lt;/Y&gt;&lt;Pressure&gt;221&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.527804500944853&lt;/Y&gt;&lt;Pressure&gt;222&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.774458951095375&lt;/X&gt;&lt;Y&gt;19.527804500944853&lt;/Y&gt;&lt;Pressure&gt;223&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.8597813675591945&lt;/X&gt;&lt;Y&gt;19.477115043234267&lt;/Y&gt;&lt;Pressure&gt;224&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.8597813675591945&lt;/X&gt;&lt;Y&gt;19.477115043234267&lt;/Y&gt;&lt;Pressure&gt;225&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.8597813675591945&lt;/X&gt;&lt;Y&gt;19.477115043234267&lt;/Y&gt;&lt;Pressure&gt;226&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;227&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;228&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;229&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;230&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;232&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;1.9302651028988715&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;233&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0032219517592389&lt;/X&gt;&lt;Y&gt;19.386863081944682&lt;/Y&gt;&lt;Pressure&gt;235&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0032219517592389&lt;/X&gt;&lt;Y&gt;19.386863081944682&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0032219517592389&lt;/X&gt;&lt;Y&gt;19.386863081944682&lt;/Y&gt;&lt;Pressure&gt;238&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0427917680902858&lt;/X&gt;&lt;Y&gt;19.468460745576362&lt;/Y&gt;&lt;Pressure&gt;239&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0613401194954637&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;239&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.0835981411816773&lt;/X&gt;&lt;Y&gt;19.537695126839605&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.1058561628678913&lt;/X&gt;&lt;Y&gt;19.569839660997538&lt;/Y&gt;&lt;Pressure&gt;231&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.135533525116176&lt;/X&gt;&lt;Y&gt;19.601984195155474&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.1676840008851519&lt;/X&gt;&lt;Y&gt;19.62794708812919&lt;/Y&gt;&lt;Pressure&gt;181&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.2023075901748173&lt;/X&gt;&lt;Y&gt;19.642783026971312&lt;/Y&gt;&lt;Pressure&gt;147&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.2369311794644831&lt;/X&gt;&lt;Y&gt;19.647728339918686&lt;/Y&gt;&lt;Pressure&gt;113&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;2.2715547687541493&lt;/X&gt;&lt;Y&gt;19.65267365286606&lt;/Y&gt;&lt;Pressure&gt;78&lt;/Pressure&gt;&lt;/anyType&gt;&lt;/Points&gt;&lt;Color&gt;&lt;Red&gt;0&lt;/Red&gt;&lt;Green&gt;0&lt;/Green&gt;&lt;Blue&gt;0&lt;/Blue&gt;&lt;/Color&gt;&lt;Width&gt;53&lt;/Width&gt;&lt;Transparency&gt;0&lt;/Transparency&gt;&lt;Timestamp&gt;2009-06-26T13:43:00.640625&lt;/Timestamp&gt;&lt;/Stroke&gt;&lt;/ArrayOfStroke&gt;', '/public/pictures/sign_building_13.jpeg', '&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"&gt;&lt;Stroke&gt;&lt;Points&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.904706350962602&lt;/X&gt;&lt;Y&gt;19.761470537708295&lt;/Y&gt;&lt;Pressure&gt;60&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.882448329276389&lt;/X&gt;&lt;Y&gt;19.755288896524082&lt;/Y&gt;&lt;Pressure&gt;86&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.868846204912593&lt;/X&gt;&lt;Y&gt;19.735507644734579&lt;/Y&gt;&lt;Pressure&gt;110&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.85895375082983&lt;/X&gt;&lt;Y&gt;19.705835767050335&lt;/Y&gt;&lt;Pressure&gt;136&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.847824739986724&lt;/X&gt;&lt;Y&gt;19.672454904655556&lt;/Y&gt;&lt;Pressure&gt;160&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.836695729143617&lt;/X&gt;&lt;Y&gt;19.623001775181812&lt;/Y&gt;&lt;Pressure&gt;172&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.837932285903962&lt;/X&gt;&lt;Y&gt;19.567367004523852&lt;/Y&gt;&lt;Pressure&gt;181&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.835459172383272&lt;/X&gt;&lt;Y&gt;19.506786920918515&lt;/Y&gt;&lt;Pressure&gt;189&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.846588183226377&lt;/X&gt;&lt;Y&gt;19.446206837313177&lt;/Y&gt;&lt;Pressure&gt;192&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.852770967028105&lt;/X&gt;&lt;Y&gt;19.390572066655213&lt;/Y&gt;&lt;Pressure&gt;198&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.870082761672938&lt;/X&gt;&lt;Y&gt;19.338646280707781&lt;/Y&gt;&lt;Pressure&gt;201&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.886157999557424&lt;/X&gt;&lt;Y&gt;19.295374792418258&lt;/Y&gt;&lt;Pressure&gt;205&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.910889134764329&lt;/X&gt;&lt;Y&gt;19.266939242970853&lt;/Y&gt;&lt;Pressure&gt;208&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.934383713210888&lt;/X&gt;&lt;Y&gt;19.252103304128731&lt;/Y&gt;&lt;Pressure&gt;211&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.961587961938482&lt;/X&gt;&lt;Y&gt;19.253339632365574&lt;/Y&gt;&lt;Pressure&gt;214&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;12.986319097145387&lt;/X&gt;&lt;Y&gt;19.265702914734007&lt;/Y&gt;&lt;Pressure&gt;217&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.018469572914363&lt;/X&gt;&lt;Y&gt;19.289193151234038&lt;/Y&gt;&lt;Pressure&gt;220&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.046910378402302&lt;/X&gt;&lt;Y&gt;19.321337685391974&lt;/Y&gt;&lt;Pressure&gt;222&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.080297410931623&lt;/X&gt;&lt;Y&gt;19.358427532497281&lt;/Y&gt;&lt;Pressure&gt;224&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.111211329940252&lt;/X&gt;&lt;Y&gt;19.394281051365745&lt;/Y&gt;&lt;Pressure&gt;226&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.144598362469573&lt;/X&gt;&lt;Y&gt;19.432607226707898&lt;/Y&gt;&lt;Pressure&gt;228&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.177985394998894&lt;/X&gt;&lt;Y&gt;19.462279104392142&lt;/Y&gt;&lt;Pressure&gt;229&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.21013587076787&lt;/X&gt;&lt;Y&gt;19.490714653839547&lt;/Y&gt;&lt;Pressure&gt;230&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.252178800619607&lt;/X&gt;&lt;Y&gt;19.501841607971141&lt;/Y&gt;&lt;Pressure&gt;231&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.291748616950654&lt;/X&gt;&lt;Y&gt;19.508023249155357&lt;/Y&gt;&lt;Pressure&gt;232&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.330081876521355&lt;/X&gt;&lt;Y&gt;19.504314264444826&lt;/Y&gt;&lt;Pressure&gt;233&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.373361363133437&lt;/X&gt;&lt;Y&gt;19.493187310313235&lt;/Y&gt;&lt;Pressure&gt;234&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.415404292985174&lt;/X&gt;&lt;Y&gt;19.473406058523736&lt;/Y&gt;&lt;Pressure&gt;235&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.458683779597257&lt;/X&gt;&lt;Y&gt;19.443734180839488&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.501963266209339&lt;/X&gt;&lt;Y&gt;19.400462692549961&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.541533082540386&lt;/X&gt;&lt;Y&gt;19.351009563076218&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.578629785350742&lt;/X&gt;&lt;Y&gt;19.305265418313006&lt;/Y&gt;&lt;Pressure&gt;238&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.612016817880063&lt;/X&gt;&lt;Y&gt;19.254575960602416&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.637984509847312&lt;/X&gt;&lt;Y&gt;19.211304472312889&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.666425315335252&lt;/X&gt;&lt;Y&gt;19.172978296970737&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.688683337021466&lt;/X&gt;&lt;Y&gt;19.143306419286493&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.707231688426644&lt;/X&gt;&lt;Y&gt;19.121052511023304&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.724543483071477&lt;/X&gt;&lt;Y&gt;19.113634541602245&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.74185527771631&lt;/X&gt;&lt;Y&gt;19.12228883926015&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.761640185881834&lt;/X&gt;&lt;Y&gt;19.144542747523335&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.780188537287012&lt;/X&gt;&lt;Y&gt;19.170505640497051&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.804919672493915&lt;/X&gt;&lt;Y&gt;19.196468533470767&lt;/Y&gt;&lt;Pressure&gt;236&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.829650807700819&lt;/X&gt;&lt;Y&gt;19.224904082918169&lt;/Y&gt;&lt;Pressure&gt;237&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.859328169949105&lt;/X&gt;&lt;Y&gt;19.253339632365574&lt;/Y&gt;&lt;Pressure&gt;238&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.890242088957734&lt;/X&gt;&lt;Y&gt;19.281775181812975&lt;/Y&gt;&lt;Pressure&gt;239&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.92239256472671&lt;/X&gt;&lt;Y&gt;19.305265418313006&lt;/Y&gt;&lt;Pressure&gt;239&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.955779597256031&lt;/X&gt;&lt;Y&gt;19.318865028918285&lt;/Y&gt;&lt;Pressure&gt;240&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;13.991639743306044&lt;/X&gt;&lt;Y&gt;19.325046670102502&lt;/Y&gt;&lt;Pressure&gt;241&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.031209559637089&lt;/X&gt;&lt;Y&gt;19.328755654813033&lt;/Y&gt;&lt;Pressure&gt;241&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.070779375968135&lt;/X&gt;&lt;Y&gt;19.322574013628817&lt;/Y&gt;&lt;Pressure&gt;242&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.111585749059529&lt;/X&gt;&lt;Y&gt;19.311447059497223&lt;/Y&gt;&lt;Pressure&gt;243&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.148682451869885&lt;/X&gt;&lt;Y&gt;19.294138464181412&lt;/Y&gt;&lt;Pressure&gt;244&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.18825226820093&lt;/X&gt;&lt;Y&gt;19.270648227681384&lt;/Y&gt;&lt;Pressure&gt;244&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.232768311573356&lt;/X&gt;&lt;Y&gt;19.240976349997137&lt;/Y&gt;&lt;Pressure&gt;245&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.27110157114406&lt;/X&gt;&lt;Y&gt;19.213777128786578&lt;/Y&gt;&lt;Pressure&gt;245&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.305725160433726&lt;/X&gt;&lt;Y&gt;19.187814235812862&lt;/Y&gt;&lt;Pressure&gt;246&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.334165965921667&lt;/X&gt;&lt;Y&gt;19.171741968733894&lt;/Y&gt;&lt;Pressure&gt;246&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.358897101128568&lt;/X&gt;&lt;Y&gt;19.161851342839146&lt;/Y&gt;&lt;Pressure&gt;247&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.377445452533747&lt;/X&gt;&lt;Y&gt;19.165560327549677&lt;/Y&gt;&lt;Pressure&gt;248&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.377445452533747&lt;/X&gt;&lt;Y&gt;19.165560327549677&lt;/Y&gt;&lt;Pressure&gt;248&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.397230360699272&lt;/X&gt;&lt;Y&gt;19.205122831128673&lt;/Y&gt;&lt;Pressure&gt;249&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.398466917459617&lt;/X&gt;&lt;Y&gt;19.237267365286606&lt;/Y&gt;&lt;Pressure&gt;250&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.398466917459617&lt;/X&gt;&lt;Y&gt;19.278066197102447&lt;/Y&gt;&lt;Pressure&gt;251&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.398466917459617&lt;/X&gt;&lt;Y&gt;19.322574013628817&lt;/Y&gt;&lt;Pressure&gt;250&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.397230360699272&lt;/X&gt;&lt;Y&gt;19.36584550191834&lt;/Y&gt;&lt;Pressure&gt;249&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.402176587740652&lt;/X&gt;&lt;Y&gt;19.409116990207867&lt;/Y&gt;&lt;Pressure&gt;245&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.399703474219962&lt;/X&gt;&lt;Y&gt;19.442497852602646&lt;/Y&gt;&lt;Pressure&gt;235&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.409595928302723&lt;/X&gt;&lt;Y&gt;19.47835137147111&lt;/Y&gt;&lt;Pressure&gt;201&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.419488382385483&lt;/X&gt;&lt;Y&gt;19.504314264444826&lt;/Y&gt;&lt;Pressure&gt;166&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.430617393228593&lt;/X&gt;&lt;Y&gt;19.525331844471168&lt;/Y&gt;&lt;Pressure&gt;131&lt;/Pressure&gt;&lt;/anyType&gt;&lt;anyType xsi:type=\"Point\"&gt;&lt;X&gt;14.442982960832042&lt;/X&gt;&lt;Y&gt;19.536458798602759&lt;/Y&gt;&lt;Pressure&gt;95&lt;/Pressure&gt;&lt;/anyType&gt;&lt;/Points&gt;&lt;Color&gt;&lt;Red&gt;0&lt;/Red&gt;&lt;Green&gt;0&lt;/Green&gt;&lt;Blue&gt;0&lt;/Blue&gt;&lt;/Color&gt;&lt;Width&gt;53&lt;/Width&gt;&lt;Transparency&gt;0&lt;/Transparency&gt;&lt;Timestamp&gt;2009-06-26T13:43:01.9375&lt;/Timestamp&gt;&lt;/Stroke&gt;&lt;/ArrayOfStroke&gt;', null, 'inspection summary', 'http://72.18.206.134:81/SessionBroker/SessionBroke', null, null);
INSERT INTO `inspection` VALUES ('15', null, null, null, '5', '5', null, null, null, null, '1080', null, '/public/reports/report20091013024946.pdf', '7', '1367');
INSERT INTO `inspection` VALUES ('17', null, null, null, '15', '7', null, null, null, null, null, null, null, null, null);
INSERT INTO `inspection` VALUES ('18', null, null, null, '16', '5', null, null, null, null, null, null, null, null, null);
INSERT INTO `inspection` VALUES ('19', null, null, null, '11', '7', null, null, null, null, '1080', 'sdsd', null, null, null);
INSERT INTO `inspection` VALUES ('20', '2009-08-13', '2009-08-13', '2009-08-13', '15', '7', null, null, null, null, '1080', 'New inspection', null, null, null);
INSERT INTO `inspection` VALUES ('21', '2012-11-14', null, null, '5', '5', null, null, null, null, '1080', null, null, null, '1367');
INSERT INTO `inspection` VALUES ('22', '2012-11-14', null, null, '5', '5', null, null, null, null, '1080', null, null, null, '1367');
INSERT INTO `inspection` VALUES ('23', '2012-11-14', null, null, '5', '5', null, null, null, null, '1080', null, null, null, '1367');
INSERT INTO `inspection` VALUES ('24', '2012-11-14', null, null, '5', '5', null, null, null, null, '1080', null, null, null, '1367');
INSERT INTO `inspection` VALUES ('25', null, null, null, '16', '10', null, null, null, null, '1080', null, null, null, '1367');

-- ----------------------------
-- Table structure for `inspection_other`
-- ----------------------------
DROP TABLE IF EXISTS `inspection_other`;
CREATE TABLE `inspection_other` (
  `ID` int(11) NOT NULL auto_increment,
  `INSPECTION_ID` int(11) NOT NULL,
  `OTHER_ID` int(11) default NULL,
  `OTHER_VALUE` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `inspection_id` (`INSPECTION_ID`),
  KEY `other_id` (`OTHER_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=106 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of inspection_other
-- ----------------------------

-- ----------------------------
-- Table structure for `integration`
-- ----------------------------
DROP TABLE IF EXISTS `integration`;
CREATE TABLE `integration` (
  `ID` int(11) NOT NULL auto_increment,
  `DATE` date default NULL,
  `TIME` time default NULL,
  `INSPECTION_ID` int(11) NOT NULL,
  `TYPE` int(11) default NULL,
  `REQUEST` varchar(50) default NULL,
  `RESPONSE` longtext,
  `STATUS` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `TYPE` (`TYPE`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  KEY `STATUS` (`STATUS`),
  CONSTRAINT `integration_ibfk_5` FOREIGN KEY (`TYPE`) REFERENCES `dictionary` (`ID`),
  CONSTRAINT `integration_ibfk_6` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `integration_ibfk_7` FOREIGN KEY (`STATUS`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of integration
-- ----------------------------
INSERT INTO `integration` VALUES ('13', '2009-10-13', '02:48:19', '5', '1073', '/logs/integration/insp20091013024819.xml', null, '1074');
INSERT INTO `integration` VALUES ('14', '2009-10-13', '02:48:41', '5', '1073', '/logs/integration/insp20091013024841.xml', null, '1075');
INSERT INTO `integration` VALUES ('15', '2009-10-13', '02:49:31', '15', '1073', '/logs/integration/insp20091013024931.xml', null, '1075');
INSERT INTO `integration` VALUES ('16', '2009-10-13', '02:50:07', '5', '1073', '/logs/integration/insp20091013025007.xml', null, '1075');
INSERT INTO `integration` VALUES ('17', '2009-10-13', '02:53:41', '5', '1073', '/logs/integration/insp20091013025341.xml', null, '1075');
INSERT INTO `integration` VALUES ('19', '2009-10-13', '03:28:26', '5', '1073', '/logs/integration/insp20091013032826.xml', null, '1075');
INSERT INTO `integration` VALUES ('20', '2009-10-13', '03:29:14', '5', '1073', '/logs/integration/insp20091013032914.xml', null, '1075');
INSERT INTO `integration` VALUES ('21', '2009-10-13', '03:29:42', '5', '1073', '/logs/integration/insp20091013032942.xml', null, '1075');
INSERT INTO `integration` VALUES ('22', '2009-10-13', '03:31:32', '5', '1073', '/logs/integration/insp20091013033132.xml', null, '1075');
INSERT INTO `integration` VALUES ('23', '2009-10-13', '15:33:50', '5', '1073', '/logs/integration/insp20091013153350.xml', null, '1075');
INSERT INTO `integration` VALUES ('24', '2009-10-13', '15:36:40', '5', '1073', '/logs/integration/insp20091013153640.xml', null, '1075');
INSERT INTO `integration` VALUES ('25', '2009-10-14', '03:37:56', '5', '1073', '/logs/integration/insp20091014033756.xml', null, '1075');
INSERT INTO `integration` VALUES ('26', '2009-10-14', '03:39:28', '5', '1073', '/logs/integration/insp20091014033928.xml', null, '1075');
INSERT INTO `integration` VALUES ('27', '2009-10-14', '03:41:13', '5', '1073', '/logs/integration/insp20091014034113.xml', null, '1075');
INSERT INTO `integration` VALUES ('28', '2009-10-14', '03:43:43', '5', '1073', '/logs/integration/insp20091014034343.xml', null, '1075');
INSERT INTO `integration` VALUES ('29', '2009-10-14', '03:46:37', '5', '1073', '/logs/integration/insp20091014034637.xml', null, '1075');
INSERT INTO `integration` VALUES ('30', '2009-10-16', '08:05:48', '5', '1073', '/logs/integration/insp20091016080548.xml', null, '1074');
INSERT INTO `integration` VALUES ('31', '2009-10-16', '08:06:28', '5', '1073', '/logs/integration/insp20091016080628.xml', null, '1074');
INSERT INTO `integration` VALUES ('32', '2009-10-16', '08:06:49', '5', '1073', '/logs/integration/insp20091016080649.xml', null, '1074');
INSERT INTO `integration` VALUES ('33', '2009-10-16', '08:07:15', '5', '1073', '/logs/integration/insp20091016080715.xml', null, '1074');
INSERT INTO `integration` VALUES ('34', '2009-10-16', '08:12:05', '5', '1073', '/logs/integration/insp20091016081205.xml', null, '1074');
INSERT INTO `integration` VALUES ('35', '2009-10-16', '14:45:40', '5', '1073', '/logs/integration/insp20091016144540.xml', null, '1074');
INSERT INTO `integration` VALUES ('36', '2009-10-16', '14:53:02', '5', '1073', '/logs/integration/insp20091016145302.xml', null, '1074');

-- ----------------------------
-- Table structure for `photobucket`
-- ----------------------------
DROP TABLE IF EXISTS `photobucket`;
CREATE TABLE `photobucket` (
  `ID` int(11) NOT NULL auto_increment,
  `INSPECTION_ID` int(11) NOT NULL,
  `URL` text NOT NULL,
  `IMAGE` text,
  `NAME` varchar(50) NOT NULL,
  `DESCRIPTION` text,
  PRIMARY KEY  (`ID`),
  KEY `INSPECTION_ID` (`INSPECTION_ID`),
  CONSTRAINT `photobucket_ibfk_2` FOREIGN KEY (`INSPECTION_ID`) REFERENCES `inspection` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of photobucket
-- ----------------------------
INSERT INTO `photobucket` VALUES ('1', '5', '<a href=\"http://s599.photobucket.com/albums/tt80/KaneKanovich/?action=view&current=2_8573.gif\" target=\"_blank\"><img src=\"http://i599.photobucket.com/albums/tt80/KaneKanovich/2_8573.gif\" border=\"0\" alt=\"Photobucket\"></a>', 'http://i599.photobucket.com/albums/tt80/KaneKanovich/2_8573.gif', 'p1', null);
INSERT INTO `photobucket` VALUES ('2', '5', '<a href=\"http://s599.photobucket.com/albums/tt80/KaneKanovich/?action=view&current=S5000133.jpg\" target=\"_blank\"><img src=\"http://i599.photobucket.com/albums/tt80/KaneKanovich/S5000133.jpg\" border=\"0\" alt=\"Photobucket\"></a>', 'http://i599.photobucket.com/albums/tt80/KaneKanovich/S5000133.jpg', 'Picture 2', null);
INSERT INTO `photobucket` VALUES ('3', '5', '<a href=\"http://s599.photobucket.com/albums/tt80/KaneKanovich/?action=view&current=4_5755.gif\" target=\"_blank\"><img src=\"http://i599.photobucket.com/albums/tt80/KaneKanovich/4_5755.gif\" border=\"0\" alt=\"Photobucket\"></a>', 'http://i599.photobucket.com/albums/tt80/KaneKanovich/4_5755.gif', 'picture 3', null);

-- ----------------------------
-- Table structure for `picture`
-- ----------------------------
DROP TABLE IF EXISTS `picture`;
CREATE TABLE `picture` (
  `ID` int(11) NOT NULL auto_increment,
  `DOOR_ID` int(11) default NULL,
  `PICTURE_FILE` varchar(100) default NULL,
  `CONTROL_NAME` varchar(50) default NULL,
  `ROTATION` int(11) default NULL,
  `INK_STROKES` longtext,
  `NOTE` text,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `UI_ID` (`ID`),
  UNIQUE KEY `DOOR_ID` (`DOOR_ID`,`PICTURE_FILE`,`CONTROL_NAME`),
  KEY `FK_DOOR_ID` (`DOOR_ID`),
  CONSTRAINT `picture_ibfk_2` FOREIGN KEY (`DOOR_ID`) REFERENCES `door` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=cp1251;

-- ----------------------------
-- Records of picture
-- ----------------------------
INSERT INTO `picture` VALUES ('9', '1', '/public/pictures/pict_door_1_20091013032741.jpg', 'Camera1_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('10', '1', '/public/pictures/pict_door_1_20091013032742.png', 'Camera2_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('11', '7', '/public/pictures/pict_door_7_20091014034540.png', null, null, null, '');
INSERT INTO `picture` VALUES ('12', '17', 'pict_door_17_20121114193633_1.jpg', 'Camera1_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('13', '17', 'pict_door_17_20121114193633_2.png', 'Camera2_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('14', '19', 'pict_door_19_20121114193633_1.png', null, null, null, '');
INSERT INTO `picture` VALUES ('15', '21', 'pict_door_21_20121114193640_1.jpg', 'Camera1_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('16', '21', 'pict_door_21_20121114193640_2.png', 'Camera2_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('17', '23', 'pict_door_23_20121114193640_1.png', null, null, null, '');
INSERT INTO `picture` VALUES ('18', '25', 'pict_door_25_20121114193830_1.jpg', 'Camera1_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('19', '25', 'pict_door_25_20121114193830_2.png', 'Camera2_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('20', '26', 'pict_door_26_20121114193830_1.png', null, null, null, '');
INSERT INTO `picture` VALUES ('21', '27', 'pict_door_27_20121114193843_1.jpg', 'Camera1_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('22', '27', 'pict_door_27_20121114193843_2.png', 'Camera2_1', '0', '&lt;?xml version=\"1.0\" encoding=\"utf-16\"?&gt;\n&lt;ArrayOfStroke xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" /&gt;', '');
INSERT INTO `picture` VALUES ('23', '28', 'pict_door_28_20121114193843_1.png', null, null, null, '');

-- ----------------------------
-- Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `ID` int(19) NOT NULL auto_increment,
  `NAME` varchar(40) NOT NULL,
  `PARENT_ROLE_ID` int(19) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `PARENT_ROLE_ID` (`PARENT_ROLE_ID`),
  CONSTRAINT `role_ibfk_1` FOREIGN KEY (`PARENT_ROLE_ID`) REFERENCES `role` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=cp1250;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', 'Administrators', null);
INSERT INTO `role` VALUES ('2', 'Guests', null);
INSERT INTO `role` VALUES ('3', 'Inspectors', '8');
INSERT INTO `role` VALUES ('4', 'Building Owner Employees', '2');
INSERT INTO `role` VALUES ('5', 'Building Owners', '4');
INSERT INTO `role` VALUES ('6', 'Web Users', '8');
INSERT INTO `role` VALUES ('7', 'Inspection Company Admins', '8');
INSERT INTO `role` VALUES ('8', 'Inspection Company Employees', '2');

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `ID` int(19) NOT NULL auto_increment,
  `LOGIN` varbinary(40) NOT NULL,
  `PASSWORD` varchar(40) NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `id` (`ID`),
  UNIQUE KEY `login` (`LOGIN`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=cp1250;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 0x61646D696E, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('5', 0x6775657374, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('6', 0x6D6172706C65, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('19', 0x646F6E616C64, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('20', 0x70616E6461, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('21', 0x6D696B65, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('22', 0x74696D, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('24', 0x69676F72, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('26', 0x74657374, 'e10adc3949ba59abbe56e057f20f883e');
INSERT INTO `user` VALUES ('27', 0x49676F72204B61796E6F76, 'e10adc3949ba59abbe56e057f20f883e');

-- ----------------------------
-- Table structure for `user_file`
-- ----------------------------
DROP TABLE IF EXISTS `user_file`;
CREATE TABLE `user_file` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) default NULL,
  `FILE_NAME` varchar(100) NOT NULL,
  `FILE_SIZE` varchar(10) default NULL,
  `ADDED_ON` date default NULL,
  `DESCRIPTION` text,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `USER_ID_2` (`USER_ID`,`FILE_NAME`),
  KEY `USER_ID` (`USER_ID`),
  CONSTRAINT `user_file_ibfk_2` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of user_file
-- ----------------------------
INSERT INTO `user_file` VALUES ('8', '1', 'TODO.xls', null, null, 'What is needed to do');
INSERT INTO `user_file` VALUES ('9', '1', 'SampleData.xml', null, null, '');
INSERT INTO `user_file` VALUES ('10', '1', 'PrintOptions.pdf', null, null, '');
INSERT INTO `user_file` VALUES ('12', '24', '2_8458.gif', '10 Kb', '2012-11-13', '');

-- ----------------------------
-- Table structure for `user_role`
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `ID` int(19) NOT NULL auto_increment,
  `USER_ID` int(19) NOT NULL,
  `ROLE_ID` int(19) NOT NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `user_id` (`USER_ID`,`ROLE_ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `role_id` (`ROLE_ID`),
  KEY `user_role_ibfk_3` (`USER_ID`),
  KEY `user_role_ibfk_4` (`ROLE_ID`),
  CONSTRAINT `user_role_ibfk_3` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_role_ibfk_4` FOREIGN KEY (`ROLE_ID`) REFERENCES `role` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=cp1250;

-- ----------------------------
-- Records of user_role
-- ----------------------------
INSERT INTO `user_role` VALUES ('40', '1', '1');
INSERT INTO `user_role` VALUES ('29', '5', '2');
INSERT INTO `user_role` VALUES ('30', '6', '3');
INSERT INTO `user_role` VALUES ('51', '19', '4');
INSERT INTO `user_role` VALUES ('52', '20', '5');
INSERT INTO `user_role` VALUES ('53', '21', '3');
INSERT INTO `user_role` VALUES ('54', '22', '3');
INSERT INTO `user_role` VALUES ('62', '24', '7');
INSERT INTO `user_role` VALUES ('59', '26', '7');
INSERT INTO `user_role` VALUES ('60', '27', '3');
