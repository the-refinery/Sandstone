SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `core_DBversion` VALUES ('4', '0', '3', '2009-04-22 17:39:43');

ALTER TABLE core_CreditCardTransactionMaster
DROP FOREIGN KEY FK_CreditCardTransactionMaster_CreditCardMaster;

ALTER TABLE core_CreditCardTransactionMaster
DROP INDEX IDX_CreditCardTransactionMaster_CreditCardID;

ALTER TABLE core_CreditCardTransactionMaster
DROP COLUMN CreditCardID;

ALTER TABLE core_CreditCardTransactionMaster
ADD COLUMN CIMpaymentProfileID int(10) unsigned NULL;

ALTER TABLE core_CreditCardTransactionMaster
ADD COLUMN PartC int(4) unsigned NULL;

DROP TABLE core_CreditCardMaster;
