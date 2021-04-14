create user 'doordatapdf'@'23.253.41.113' identified by '8mHNWUTymfyr8aV';


GRANT ALL PRIVILEGES ON doordata.* to 'doordatapdf'@'23.253.41.113';
GRANT ALL PRIVILEGES ON pdfexport.* to 'doordatapdf'@'23.253.41.113';

set password for 'doordatapdf'@'23.253.41.113' = password('8mHNWUTymfyr8aV');
