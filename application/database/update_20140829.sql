ALTER TABLE `company` ADD COLUMN `COUNTRY` int(11) AFTER `ADDRESS_2`, CHANGE COLUMN `CITY` `CITY` varchar(50) CHARACTER SET cp1251 DEFAULT NULL AFTER `COUNTRY`, CHANGE COLUMN `STATE` `STATE` int(11) DEFAULT NULL AFTER `CITY`, CHANGE COLUMN `ZIP` `ZIP` varchar(10) CHARACTER SET cp1251 DEFAULT NULL AFTER `STATE`, CHANGE COLUMN `TYPE` `TYPE` int(11) DEFAULT NULL AFTER `ZIP`, CHANGE COLUMN `INSPECTION_COMPANY` `INSPECTION_COMPANY` int(11) DEFAULT NULL AFTER `TYPE`, CHANGE COLUMN `PRIMARY_CONTACT` `PRIMARY_CONTACT` int(11) DEFAULT NULL AFTER `INSPECTION_COMPANY`, CHANGE COLUMN `BRANDING` `BRANDING` int(1) DEFAULT '0' AFTER `PRIMARY_CONTACT`, CHANGE COLUMN `LOGO_FILE` `LOGO_FILE` varchar(100) DEFAULT NULL AFTER `BRANDING`, CHANGE COLUMN `COLOR_THEME` `COLOR_THEME` int(1) DEFAULT '0' AFTER `LOGO_FILE`;

ALTER TABLE `company` ADD FOREIGN KEY (`COUNTRY`) REFERENCES `dictionary` (`ID`)   ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE `dictionary` ADD COLUMN `PARENT_ID` int(11) AFTER `ID`, CHANGE COLUMN `CATEGORY` `CATEGORY` varchar(50) CHARACTER SET cp1251 NOT NULL AFTER `PARENT_ID`, CHANGE COLUMN `ITEM` `ITEM` varchar(50) CHARACTER SET cp1251 NOT NULL AFTER `CATEGORY`, CHANGE COLUMN `DESCRIPTION` `DESCRIPTION` varchar(255) CHARACTER SET cp1251 DEFAULT NULL AFTER `ITEM`, CHANGE COLUMN `VALUE_ORDER` `VALUE_ORDER` int(11) DEFAULT NULL AFTER `DESCRIPTION`;

ALTER TABLE `dictionary` ADD FOREIGN KEY (`PARENT_ID`) REFERENCES `dictionary` (`ID`)   ON UPDATE CASCADE ON DELETE SET NULL;

update dictionary set parent_id = 999 where category = 'State';

update company set country = 999;

update building set country = 999;

insert into dictionary(parent_id, category, item, description) values
(1000, 'State', 'AB', 'Alberta'),
(1000, 'State', 'BC', 'British Columbia'),
(1000, 'State', 'MB', 'Manitoba'),
(1000, 'State', 'NB', 'New Brunswick'),
(1000, 'State', 'NL', 'Newfoundland and Labrador'),
(1000, 'State', 'NS', 'Nova Scotia'),
(1000, 'State', 'NT', 'Northwest Territories'),
(1000, 'State', 'NU', 'Ninavut'),
(1000, 'State', 'ON', 'Ontario'),
(1000, 'State', 'PE', 'Prince Edward Island'),
(1000, 'State', 'QC', 'Quebec'),
(1000, 'State', 'SK', 'Saskatchewan'),
(1000, 'State', 'YT', 'Yukon');

update `doordata`.`dictionary` set `VALUE_ORDER`='10' where `ID`='948';
update `doordata`.`dictionary` set `VALUE_ORDER`='20' where `ID`='949';
update `doordata`.`dictionary` set `VALUE_ORDER`='30' where `ID`='950';
update `doordata`.`dictionary` set `VALUE_ORDER`='40' where `ID`='951';
update `doordata`.`dictionary` set `VALUE_ORDER`='50' where `ID`='952';
update `doordata`.`dictionary` set `VALUE_ORDER`='60' where `ID`='953';
update `doordata`.`dictionary` set `VALUE_ORDER`='70' where `ID`='954'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='80' where `ID`='955'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='90' where `ID`='956'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='100' where `ID`='957'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='110' where `ID`='958'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='120' where `ID`='959'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='130' where `ID`='960'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='140' where `ID`='961'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='150' where `ID`='962'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='160' where `ID`='963'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='170' where `ID`='964'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='180' where `ID`='965'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='190' where `ID`='966'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='200' where `ID`='967'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='210' where `ID`='968'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='220' where `ID`='969'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='230' where `ID`='970'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='240' where `ID`='971'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='250' where `ID`='972'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='260' where `ID`='973'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='270' where `ID`='974'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='280' where `ID`='975'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='290' where `ID`='976'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='300' where `ID`='977'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='310' where `ID`='978'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='320' where `ID`='979'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='330' where `ID`='980'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='340' where `ID`='981'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='350' where `ID`='982'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='360' where `ID`='983'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='370' where `ID`='984'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='380' where `ID`='985'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='390' where `ID`='986'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='400' where `ID`='987'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='410' where `ID`='988'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='420' where `ID`='989'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='430' where `ID`='990'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='440' where `ID`='991'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='450' where `ID`='992'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='460' where `ID`='993'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='470' where `ID`='994'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='480' where `ID`='995'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='490' where `ID`='996'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='500' where `ID`='997'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='510' where `ID`='998'; 


update `doordata`.`dictionary` set `VALUE_ORDER`='520' where `item`='AB' and category = 'State'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='530' where `item`='BC' and category = 'State'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='540' where `item`='MB' and category = 'State'; 
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='NB' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='NL' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='NS' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='NT' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='NU' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='ON' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='PE' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='QC' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='SK' and category = 'State';
update `doordata`.`dictionary` set `VALUE_ORDER`='550' where `item`='YT' and category = 'State';