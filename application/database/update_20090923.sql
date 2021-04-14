alter table door modify column NUMBER varchar(20) not null;
alter table door add unique NUMBER_INSPECTION (NUMBER, INSPECTION_ID);