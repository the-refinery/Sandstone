SET FOREIGN_KEY_CHECKS=0;

INSERT INTO core_DBversion VALUES ('4', '0', '5', '2009-08-28 15:30');

CREATE TABLE core_PayPalTransactionMaster (
	TransactionID int(10) unsigned NOT NULL auto_increment,
	AccountID int(10) unsigned NOT NULL,
	Token varchar(75) NOT NULL,
	CreateTimestamp datetime NOT NULL,
	GetDetailsTimestamp datetime NULL,
	ProcessTimestamp datetime NULL,
	Amount decimal(15,4) NOT NULL,
	IsSuccessful tinyint(4) NOT NULL default '0',
	PRIMARY KEY (TransactionID),
	KEY IDX_PayPalTransactionMaster_AccountID (AccountID),
	CONSTRAINT FK_PayPalTransactionMaster_AccountMaster FOREIGN KEY (AccountID) REFERENCES core_AccountMaster (AccountID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
