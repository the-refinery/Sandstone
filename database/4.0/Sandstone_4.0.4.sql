SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `core_DBversion` VALUES ('4', '0', '4', '2009-05-01 14:39:43');

ALTER TABLE core_CreditCardTransactionMaster
ADD COLUMN CIMcustomerProfileID int(10) unsigned NULL;
