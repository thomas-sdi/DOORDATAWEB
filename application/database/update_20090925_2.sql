CREATE TABLE `download` (
  `ID` int(11) NOT NULL auto_increment,
  `EMAIL` varchar(100) NOT NULL,
  `HASH` varchar(32) NOT NULL,
  `CREATE_DATE` date NOT NULL,
  `DOWNLOADED` int(11) NOT NULL default '136',
  PRIMARY KEY  (`ID`),
  KEY `DOWNLOADED` (`DOWNLOADED`),
  CONSTRAINT `download_ibfk_1` FOREIGN KEY (`DOWNLOADED`) REFERENCES `dictionary` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;