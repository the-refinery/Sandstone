INSERT INTO `core_DBversion` VALUES ('4', '0', '1', '2008-11-26 11:49:27');

ALTER TABLE `core_CreditCardTransactionMaster` ADD COLUMN `AccountID` int(10) unsigned NULL;
ALTER TABLE `core_CreditCardTransactionMaster` ADD INDEX `IDX_CreditCardTransactionMaster_AccountID` (`AccountID`);
ALTER TABLE `core_CreditCardTransactionMaster` ADD CONSTRAINT `FK_CreditCardTransactionMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`);

UPDATE 	core_CreditCardTransactionMaster a,
		core_CreditCardMaster b
SET		a.AccountID = b.AccountID
WHERE 	a.CreditCardID = b.CreditCardID;

ALTER TABLE `core_CreditCardTransactionMaster` MODIFY COLUMN `AccountID` int(10) unsigned NOT NULL;