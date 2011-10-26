ALTER TABLE `account` 
 ADD `onetime_password` BOOL NOT NULL DEFAULT FALSE AFTER `online` ,
 ADD `valid_password` BOOL NOT NULL DEFAULT TRUE AFTER `onetime_password`;
 
