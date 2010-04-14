SET FOREIGN_KEY_CHECKS=0;

INSERT INTO core_DBversion VALUES ('4', '0', '8', '2010-04-14 16:58');

CREATE TABLE core_AccountAPIkey (
	AccountID int(10) unsigned NOT NULL,
	APIkey text NOT NULL,
	PRIMARY KEY (AccountID),
	CONSTRAINT FK_AccountAPIkey_AccountMaster FOREIGN KEY (AccountID) REFERENCES core_AccountMaster (AccountID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
