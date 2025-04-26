CREATE DATABASE IF NOT EXISTS lifepharmacy_assessment;
CREATE USER 'lifepharmacy'@'localhost' IDENTIFIED BY 'l1f3Ph@rm@cy';
GRANT ALL ON lifepharmacy_assessment.* TO 'lifepharmacy'@'%';

SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
/* Make sure the privileges are installed */
FLUSH PRIVILEGES;

USE lifepharmacy_assessment;
