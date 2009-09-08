SET FOREIGN_KEY_CHECKS=0;

INSERT INTO core_DBversion VALUES ('4', '0', '6', '2009-09-07 19:44');

ALTER TABLE core_PayPalTransactionMaster
ADD COLUMN CorrelationID varchar(75),
ADD COLUMN FeeAmount decimal(15,4),
ADD COLUMN PaymentStatus varchar(75),
ADD COLUMN PendingReason text,
ADD COLUMN ReasonCode varchar(75), 
ADD COLUMN IsCancelled tinyint(4) NOT NULL default '0';
