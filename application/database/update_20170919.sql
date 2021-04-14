insert into `role` ( `ID`, `NAME`) values ( '9', 'Field Inspectors');
update `role` set `PARENT_ROLE_ID`='2' where `ID`='9';

ALTER TABLE `role` ADD COLUMN `ORDER_BY` int DEFAULT NULL AFTER `PARENT_ROLE_ID`;

update `role` set `ORDER_BY`='10' where `ID`='2'; 
update `role` set `ORDER_BY`='15' where `ID`='8'; 
update `role` set `ORDER_BY`='20' where `ID`='9';
update `role` set `ORDER_BY`='30' where `ID`='3';
update `role` set `ORDER_BY`='40' where `ID`='6';
update `role` set `ORDER_BY`='50' where `ID`='7';    
