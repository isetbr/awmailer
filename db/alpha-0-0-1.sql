# Adding campaign key
ALTER TABLE `m4a1`.`campaign` 
ADD COLUMN `key` VARCHAR(128) NOT NULL AFTER `idservice`;

# Updating size of service token
ALTER TABLE `m4a1`.`service` 
CHANGE COLUMN `token` `token` CHAR(80) NOT NULL ;

