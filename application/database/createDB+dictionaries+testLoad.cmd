mysql -u root -pcachalot08 -e "DROP DATABASE IF EXISTS doordata;CREATE DATABASE doordata"
mysql -u root -pcachalot08 doordata < schema.sql
mysql -u root -pcachalot08 doordata < dictionaries.sql
mysql -u root -pcachalot08 doordata < testLoad.sql
pause