/*
MySQL Data Transfer
Source Host: designinginteractive.dev
Source Database: designin_sandstonecurrent
Target Host: designinginteractive.dev
Target Database: designin_sandstonecurrent
Date: 11/26/2008 11:50:52 AM
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for core_AccountMaster
-- ----------------------------
CREATE TABLE `core_AccountMaster` (
  `AccountID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(75) NOT NULL,
  PRIMARY KEY  (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ActionHistory
-- ----------------------------
CREATE TABLE `core_ActionHistory` (
  `AccountID` int(10) unsigned NOT NULL,
  `ActionID` int(10) unsigned NOT NULL default '0',
  `Timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `AssociatedEntityID` int(10) unsigned default NULL,
  `Details` mediumtext NOT NULL,
  `UserID` int(10) unsigned default '0',
  `RoutingAction` varchar(75) default NULL,
  `AssociatedEntityType` varchar(75) default NULL,
  KEY `UserID` (`UserID`),
  KEY `ActionID` (`ActionID`),
  KEY `Timestamp` (`Timestamp`),
  KEY `IDX_ActionHistory_AccountID` (`AccountID`),
  CONSTRAINT `FK_ActionHistory_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ActionHistory_ActionMaster` FOREIGN KEY (`ActionID`) REFERENCES `core_ActionMaster` (`ActionID`),
  CONSTRAINT `FK_ActionHistory_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ActionLogging
-- ----------------------------
CREATE TABLE `core_ActionLogging` (
  `AccountID` int(10) unsigned NOT NULL,
  `ActionID` int(10) unsigned NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`AccountID`,`ActionID`,`AssociatedEntityID`),
  KEY `IDX_ActionLogging_ActionID` (`ActionID`),
  KEY `IDX_ActionLogging_AccountID` (`AccountID`),
  CONSTRAINT `FK_ActionLogging_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ActionLogging_ActionMaster` FOREIGN KEY (`ActionID`) REFERENCES `core_ActionMaster` (`ActionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ActionMaster
-- ----------------------------
CREATE TABLE `core_ActionMaster` (
  `ActionID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(75) NOT NULL default '',
  `Description` mediumtext,
  `RoutingAction` varchar(75) default NULL,
  `AssociatedEntityType` varchar(75) default NULL,
  PRIMARY KEY  (`ActionID`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ActionNotification
-- ----------------------------
CREATE TABLE `core_ActionNotification` (
  `AccountID` int(10) unsigned NOT NULL,
  `ActionID` int(10) unsigned NOT NULL default '0',
  `EmailID` int(10) unsigned NOT NULL default '0',
  `AssociatedEntityID` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`AccountID`,`ActionID`,`EmailID`,`AssociatedEntityID`),
  KEY `EmailID` (`EmailID`),
  KEY `FK_ActionNotification_ActionMaster` (`ActionID`),
  CONSTRAINT `FK_ActionNotification_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ActionNotification_ActionMaster` FOREIGN KEY (`ActionID`) REFERENCES `core_ActionMaster` (`ActionID`),
  CONSTRAINT `FK_ActionNotification_EmailMaster` FOREIGN KEY (`EmailID`) REFERENCES `core_EmailMaster` (`EmailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ActiveMerchantAccount
-- ----------------------------
CREATE TABLE `core_ActiveMerchantAccount` (
  `AccountID` int(10) unsigned NOT NULL,
  `MerchantAccountID` int(10) unsigned NOT NULL,
  `TransactionFee` decimal(15,4) default NULL,
  `DiscountPercent` decimal(15,4) default NULL,
  PRIMARY KEY  (`AccountID`,`MerchantAccountID`),
  KEY `IDX_ActiveMerchantAccount_MerchantAccountID` (`MerchantAccountID`),
  CONSTRAINT `FK_ActiveMerchantAccount_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ActiveMerchantAccount_MerchantAccountMaster` FOREIGN KEY (`MerchantAccountID`) REFERENCES `core_MerchantAccountMaster` (`MerchantAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_AddressMaster
-- ----------------------------
CREATE TABLE `core_AddressMaster` (
  `AddressID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `Street` text NOT NULL,
  `City` text NOT NULL,
  `ProvinceCode` varchar(10) NOT NULL,
  `PostalCode` varchar(10) NOT NULL,
  `CountryCode` char(2) NOT NULL,
  PRIMARY KEY  (`AddressID`),
  KEY `IDX_AddressMaster_AccountID` (`AccountID`),
  KEY `IDX_AddressMaster_ProvinceCode` (`ProvinceCode`),
  KEY `IDX_AddressMaster_PostalCode` (`PostalCode`),
  KEY `IDX_AddressMaster_CountryCode` (`CountryCode`),
  CONSTRAINT `FK_AddressMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_CreditCardMaster
-- ----------------------------
CREATE TABLE `core_CreditCardMaster` (
  `CreditCardID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `PartA` varchar(4) NOT NULL default '',
  `PartC` varchar(4) NOT NULL default '',
  `NumberLength` int(10) unsigned NOT NULL default '0',
  `CardTypeID` int(10) unsigned NOT NULL default '0',
  `CVV` char(3) NOT NULL default '',
  `NameOnCard` varchar(75) NOT NULL default '',
  `AddressID` int(10) unsigned default NULL,
  `ExpirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`CreditCardID`),
  KEY `IDX_CreditCardMaster_CardTypeID` (`CardTypeID`),
  KEY `IDX_CreditCardMaster_AddressID` (`AddressID`),
  KEY `IDX_CreditCardMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_CreditCardMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_CreditCardMaster_AddressMaster` FOREIGN KEY (`AddressID`) REFERENCES `core_AddressMaster` (`AddressID`),
  CONSTRAINT `FK_CreditCardMaster_CreditCardTypeMaster` FOREIGN KEY (`CardTypeID`) REFERENCES `core_CreditCardTypeMaster` (`CardTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_CreditCardTransactionMaster
-- ----------------------------
CREATE TABLE `core_CreditCardTransactionMaster` (
  `TransactionID` int(10) unsigned NOT NULL auto_increment,
  `MerchantAccountID` int(10) unsigned NOT NULL,
  `CreditCardID` int(10) unsigned NOT NULL default '0',
  `CreditCardTransactionTypeID` int(10) unsigned NOT NULL,
  `Timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `Amount` decimal(15,4) NOT NULL default '0.0000',
  `MerchantTransactionID` mediumtext,
  `IsSuccessful` tinyint(1) NOT NULL default '0',
  `TransactionFee` decimal(15,4) default NULL,
  `DiscountPercent` decimal(15,4) default NULL,
  `RelatedTransactionID` int(10) unsigned default NULL,
  PRIMARY KEY  (`TransactionID`),
  KEY `IDX_CreditCardTransactionMaster_CreditCardID` (`CreditCardID`),
  KEY `IDX_CreditCardTransactionMaster_MerchantAccountID` (`MerchantAccountID`),
  KEY `IDX_CreditCardTransactionMaster_CreditCardTransactionTypeID` (`CreditCardTransactionTypeID`),
  KEY `IDX_CreditCardTransactionMaster_RelatedTransactionID` (`RelatedTransactionID`),
  CONSTRAINT `FK_CreditCardTransactionMaster_CreditCardMaster` FOREIGN KEY (`CreditCardID`) REFERENCES `core_CreditCardMaster` (`CreditCardID`),
  CONSTRAINT `FK_CreditCardTransactionMaster_CreditCardTransactionMaster` FOREIGN KEY (`RelatedTransactionID`) REFERENCES `core_CreditCardTransactionMaster` (`TransactionID`),
  CONSTRAINT `FK_CreditCardTransactionMaster_CreditCardTransactionTypeMaster` FOREIGN KEY (`CreditCardTransactionTypeID`) REFERENCES `core_CreditCardTransactionTypeMaster` (`CreditCardTransactionTypeID`),
  CONSTRAINT `FK_CreditCardTransactionMaster_MerchantAccountMaster` FOREIGN KEY (`MerchantAccountID`) REFERENCES `core_MerchantAccountMaster` (`MerchantAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_CreditCardTransactionMessage
-- ----------------------------
CREATE TABLE `core_CreditCardTransactionMessage` (
  `MessageID` int(10) unsigned NOT NULL auto_increment,
  `TransactionID` int(10) unsigned NOT NULL default '0',
  `MessageText` mediumtext NOT NULL,
  PRIMARY KEY  (`MessageID`),
  KEY `IDX_CreditCardTransactionMessage_TransactionID` (`TransactionID`),
  CONSTRAINT `FK_CreditCardTransactionMessage_CreditCardTransactionMaster` FOREIGN KEY (`TransactionID`) REFERENCES `core_CreditCardTransactionMaster` (`TransactionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_CreditCardTransactionTypeMaster
-- ----------------------------
CREATE TABLE `core_CreditCardTransactionTypeMaster` (
  `CreditCardTransactionTypeID` int(10) unsigned NOT NULL auto_increment,
  `Description` varchar(75) NOT NULL,
  PRIMARY KEY  (`CreditCardTransactionTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_CreditCardTypeMaster
-- ----------------------------
CREATE TABLE `core_CreditCardTypeMaster` (
  `CardTypeID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(75) NOT NULL default '',
  `IsAccepted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`CardTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_DBversion
-- ----------------------------
CREATE TABLE `core_DBversion` (
  `Major` int(10) NOT NULL,
  `Minor` int(10) NOT NULL,
  `Revision` int(10) NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY  (`Major`,`Minor`,`Revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EmailMaster
-- ----------------------------
CREATE TABLE `core_EmailMaster` (
  `EmailID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `Address` varchar(80) default NULL,
  PRIMARY KEY  (`EmailID`),
  KEY `IDX_EmailMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_EmailMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EmailTypeMaster
-- ----------------------------
CREATE TABLE `core_EmailTypeMaster` (
  `EmailTypeID` int(10) unsigned NOT NULL auto_increment,
  `Description` varchar(75) default NULL,
  PRIMARY KEY  (`EmailTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EntityEmail
-- ----------------------------
CREATE TABLE `core_EntityEmail` (
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `EmailID` int(10) unsigned NOT NULL,
  `EmailTypeID` int(10) unsigned NOT NULL,
  `IsPrimary` tinyint(1) NOT NULL,
  PRIMARY KEY  (`AssociatedEntityType`,`AssociatedEntityID`,`EmailID`),
  KEY `IDX_EntityEmail_EmailID` (`EmailID`),
  KEY `IDX_EntityEmail_EmailTypeID` (`EmailTypeID`),
  CONSTRAINT `FK_EntityEmail_EmailMaster` FOREIGN KEY (`EmailID`) REFERENCES `core_EmailMaster` (`EmailID`),
  CONSTRAINT `FK_EntityEmail_EmailTypeMaster` FOREIGN KEY (`EmailTypeID`) REFERENCES `core_EmailTypeMaster` (`EmailTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EntityFile
-- ----------------------------
CREATE TABLE `core_EntityFile` (
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `FileID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`AssociatedEntityType`,`AssociatedEntityID`,`FileID`),
  KEY `IDX_EntityFile_FileID` (`FileID`),
  CONSTRAINT `FK_EntityFile_FileMaster` FOREIGN KEY (`FileID`) REFERENCES `core_FileMaster` (`FileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EntityImage
-- ----------------------------
CREATE TABLE `core_EntityImage` (
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `ImageID` int(10) unsigned NOT NULL,
  `IsPrimary` tinyint(4) NOT NULL,
  PRIMARY KEY  (`AssociatedEntityType`,`AssociatedEntityID`,`ImageID`),
  KEY `IDX_EntityImage_ImageID` (`ImageID`),
  CONSTRAINT `FK_EntityImage_ImageMaster` FOREIGN KEY (`ImageID`) REFERENCES `core_ImageMaster` (`ImageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EntityPhone
-- ----------------------------
CREATE TABLE `core_EntityPhone` (
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `PhoneID` int(10) unsigned NOT NULL,
  `PhoneTypeID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`AssociatedEntityType`,`AssociatedEntityID`,`PhoneID`),
  KEY `IDX_EntityPhone_PhoneID` (`PhoneID`),
  KEY `IDX_EntityPhone_PhoneTypeID` (`PhoneTypeID`),
  CONSTRAINT `FK_EntityPhone_PhoneMaster` FOREIGN KEY (`PhoneID`) REFERENCES `core_PhoneMaster` (`PhoneID`),
  CONSTRAINT `FK_EntityPhone_PhoneTypeMaster` FOREIGN KEY (`PhoneTypeID`) REFERENCES `core_PhoneTypeMaster` (`PhoneTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_EntityTag
-- ----------------------------
CREATE TABLE `core_EntityTag` (
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `TagID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `AddTimestamp` datetime NOT NULL,
  PRIMARY KEY  (`AssociatedEntityType`,`AssociatedEntityID`,`TagID`),
  KEY `IDX_EntityTag_TagID` (`TagID`),
  KEY `IDX_EntityTag_UserID` (`UserID`),
  CONSTRAINT `FK_EntityTag_TagMaster` FOREIGN KEY (`TagID`) REFERENCES `core_TagMaster` (`TagID`),
  CONSTRAINT `FK_EntityTag_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_FileDownloadLog
-- ----------------------------
CREATE TABLE `core_FileDownloadLog` (
  `FileDownloadID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `Timestamp` datetime NOT NULL,
  `FileID` int(10) unsigned NOT NULL,
  `Version` int(10) unsigned NOT NULL,
  `FileSpec` mediumtext NOT NULL,
  `FileSize` int(10) default NULL,
  `UserID` int(10) unsigned default NULL,
  `UserIPaddress` varchar(75) default NULL,
  PRIMARY KEY  (`FileDownloadID`),
  KEY `IDX_FileDownloadLog_FileID_Version` (`FileID`,`Version`),
  KEY `IDX_FileDownloadLog_UserID` (`UserID`),
  KEY `IDX_FileDownloadLog_AccountID` (`AccountID`),
  CONSTRAINT `FK_FileDownloadLog_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_FileMaster
-- ----------------------------
CREATE TABLE `core_FileMaster` (
  `FileID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `FileName` varchar(75) NOT NULL default '',
  `FileType` varchar(75) NOT NULL,
  `Description` mediumtext,
  `DownloadCount` int(10) NOT NULL default '0',
  `PhysicalFileName` varchar(75) NOT NULL,
  PRIMARY KEY  (`FileID`),
  KEY `IDX_FileMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_FileMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_FileVersions
-- ----------------------------
CREATE TABLE `core_FileVersions` (
  `FileID` int(10) unsigned NOT NULL,
  `Version` int(10) unsigned NOT NULL,
  `FileSpec` mediumtext NOT NULL,
  `FileSize` int(10) NOT NULL,
  `UploadTimestamp` datetime default NULL,
  `UploadUserID` int(10) unsigned default NULL,
  `DownloadCount` int(10) NOT NULL default '0',
  PRIMARY KEY  (`FileID`,`Version`),
  KEY `IDX_FileVersion_UploadUserID` (`UploadUserID`),
  CONSTRAINT `FK_FileVersions_FileMaster` FOREIGN KEY (`FileID`) REFERENCES `core_FileMaster` (`FileID`),
  CONSTRAINT `FK_FileVersions_UserMaster` FOREIGN KEY (`UploadUserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ImageMaster
-- ----------------------------
CREATE TABLE `core_ImageMaster` (
  `ImageID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `FileID` int(10) unsigned NOT NULL default '0',
  `AlternateText` varchar(75) default NULL,
  `Width` int(10) unsigned NOT NULL default '0',
  `Height` int(10) unsigned NOT NULL default '0',
  `Description` mediumtext,
  PRIMARY KEY  (`ImageID`),
  KEY `IDX_ImageMaster_FileID` (`FileID`),
  KEY `IDX_ImageMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_ImageMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ImageMaster_FileMaster` FOREIGN KEY (`FileID`) REFERENCES `core_FileMaster` (`FileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_KnowledgebaseArticleMaster
-- ----------------------------
CREATE TABLE `core_KnowledgebaseArticleMaster` (
  `ArticleID` int(10) unsigned NOT NULL auto_increment,
  `SectionID` int(10) unsigned NOT NULL,
  `Title` varchar(75) NOT NULL,
  `ShortDescription` mediumtext,
  `HTML` mediumtext NOT NULL,
  `SearchContent` mediumtext NOT NULL,
  `IsPublished` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ArticleID`),
  KEY `IDX_KnowledgebaseArticleMaster_SectionID` (`SectionID`),
  CONSTRAINT `FK_KnowledgebaseArticleMaster_KnowledgebaseSectionMaster` FOREIGN KEY (`SectionID`) REFERENCES `core_KnowledgebaseSectionMaster` (`SectionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_KnowledgebaseSectionMaster
-- ----------------------------
CREATE TABLE `core_KnowledgebaseSectionMaster` (
  `SectionID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(75) NOT NULL,
  `Description` mediumtext,
  PRIMARY KEY  (`SectionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_MerchantAccountMaster
-- ----------------------------
CREATE TABLE `core_MerchantAccountMaster` (
  `MerchantAccountID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(75) NOT NULL default '',
  `ProcessorClassName` varchar(100) NOT NULL default '',
  `IsAvailable` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`MerchantAccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_MerchantAccountParameters
-- ----------------------------
CREATE TABLE `core_MerchantAccountParameters` (
  `AccountID` int(10) unsigned NOT NULL,
  `MerchantAccountID` int(10) unsigned NOT NULL default '0',
  `ParameterName` varchar(75) NOT NULL default '',
  `ParameterValue` mediumtext NOT NULL,
  PRIMARY KEY  (`AccountID`,`MerchantAccountID`,`ParameterName`),
  KEY `IDX_MerchantAccountParameters_MerchantAccountID` (`MerchantAccountID`),
  CONSTRAINT `FK_MerchantAccountParameters_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_MerchantAccountParameters_MerchantAccountMaster` FOREIGN KEY (`MerchantAccountID`) REFERENCES `core_MerchantAccountMaster` (`MerchantAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_MessageCommentMaster
-- ----------------------------
CREATE TABLE `core_MessageCommentMaster` (
  `CommentID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `MessageID` int(10) unsigned NOT NULL default '0',
  `UserID` int(10) unsigned NOT NULL default '0',
  `Timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `Content` mediumtext NOT NULL,
  PRIMARY KEY  (`CommentID`),
  KEY `IDX_MessageCommentMaster_MessageID` (`MessageID`),
  KEY `IDX_MessageCommentMaster_UserID` (`UserID`),
  KEY `IDX_MessageCommentMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_MessageCommentMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_MessageCommentMaster_MessageMaster` FOREIGN KEY (`MessageID`) REFERENCES `core_MessageMaster` (`MessageID`),
  CONSTRAINT `FK_MessageCommentMaster_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_MessageMaster
-- ----------------------------
CREATE TABLE `core_MessageMaster` (
  `MessageID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `AssociatedEntityType` varchar(75) NOT NULL default '',
  `AssociatedEntityID` int(10) unsigned NOT NULL default '0',
  `UserID` int(10) unsigned NOT NULL default '0',
  `Timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `Subject` varchar(75) NOT NULL default '',
  `Content` mediumtext NOT NULL,
  PRIMARY KEY  (`MessageID`),
  KEY `IDX_MessageMaster_AssociatedEntityType` (`AssociatedEntityType`,`AssociatedEntityID`),
  KEY `IDX_MessageMaster_UserID` (`UserID`),
  KEY `IDX_MessageMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_MessageMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_MessageMaster_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_PhoneMaster
-- ----------------------------
CREATE TABLE `core_PhoneMaster` (
  `PhoneID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `CountryCode` varchar(5) default '1',
  `AreaCode` varchar(10) default NULL,
  `LocalNumber` varchar(25) default NULL,
  PRIMARY KEY  (`PhoneID`),
  KEY `IDX_PhoneMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_PhoneMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_PhoneTypeMaster
-- ----------------------------
CREATE TABLE `core_PhoneTypeMaster` (
  `PhoneTypeID` int(10) unsigned NOT NULL auto_increment,
  `Description` varchar(75) default NULL,
  PRIMARY KEY  (`PhoneTypeID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ReportFieldMaster
-- ----------------------------
CREATE TABLE `core_ReportFieldMaster` (
  `FieldID` int(10) unsigned NOT NULL auto_increment,
  `ReportID` int(10) unsigned NOT NULL,
  `Name` varchar(75) NOT NULL,
  `Description` text,
  `FieldAlias` varchar(75) default NULL,
  `DataType` varchar(75) NOT NULL,
  `IsSortable` tinyint(4) NOT NULL,
  `IsTotalable` tinyint(4) NOT NULL,
  `IsGroupable` tinyint(4) NOT NULL,
  `IsFilterable` tinyint(4) NOT NULL,
  `FilterClause` text,
  `IsHavingFilter` tinyint(4) NOT NULL,
  `IsReturned` tinyint(4) NOT NULL,
  PRIMARY KEY  (`FieldID`),
  KEY `IDX_ReportFieldMaster_ReportID` (`ReportID`),
  CONSTRAINT `FK_ReportFieldMaster_ReportMaster` FOREIGN KEY (`ReportID`) REFERENCES `core_ReportMaster` (`ReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ReportMaster
-- ----------------------------
CREATE TABLE `core_ReportMaster` (
  `ReportID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned default NULL,
  `Name` varchar(75) NOT NULL,
  `Description` text,
  `AssociatedEntityType` varchar(75) default NULL,
  `TemplateName` varchar(75) NOT NULL,
  `IsActive` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ReportID`),
  KEY `IDX_ReportMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_ReportMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_RoleMaster
-- ----------------------------
CREATE TABLE `core_RoleMaster` (
  `RoleID` int(10) unsigned NOT NULL auto_increment,
  `Description` varchar(75) NOT NULL default '',
  PRIMARY KEY  (`RoleID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SearchEntityMaster
-- ----------------------------
CREATE TABLE `core_SearchEntityMaster` (
  `TopLevelNamespace` varchar(75) NOT NULL default '',
  `ClassName` varchar(75) NOT NULL default '',
  `RequiredNamespace` mediumtext NOT NULL,
  `EntityWeight` int(10) unsigned NOT NULL default '0',
  `TagMatchWeight` int(10) unsigned NOT NULL default '0',
  `TagWildcardWeight` int(10) unsigned NOT NULL default '0',
  `MessageWildcardWeight` int(10) unsigned NOT NULL default '0',
  `IsActive` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`TopLevelNamespace`,`ClassName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SearchEntityProperty
-- ----------------------------
CREATE TABLE `core_SearchEntityProperty` (
  `TopLevelNamespace` varchar(75) NOT NULL default '',
  `ClassName` varchar(75) NOT NULL default '',
  `PropertyName` varchar(75) NOT NULL default '',
  `MatchWeight` int(10) unsigned NOT NULL default '0',
  `WildcardWeight` int(10) unsigned NOT NULL default '0',
  `IsUsedInCombinedSearch` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`TopLevelNamespace`,`ClassName`,`PropertyName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SEOkeywordMaster
-- ----------------------------
CREATE TABLE `core_SEOkeywordMaster` (
  `KeywordID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `SEOpageID` int(10) unsigned default NULL,
  `Keyword` mediumtext NOT NULL,
  `SortOrder` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`KeywordID`),
  KEY `IDX_SEOkeywordMaster_AccountID` (`AccountID`),
  KEY `IDX_SEOkeywordMaster_SEOpageID` (`SEOpageID`),
  CONSTRAINT `FK_SEOkeywordMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_SEOkeywordMaster_SEOpageMaster` FOREIGN KEY (`SEOpageID`) REFERENCES `core_SEOpageMaster` (`SEOpageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SEOpageMaster
-- ----------------------------
CREATE TABLE `core_SEOpageMaster` (
  `SEOpageID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `Name` varchar(75) NOT NULL default '',
  `AssociatedEntityType` varchar(75) NOT NULL,
  `AssociatedEntityID` int(10) unsigned NOT NULL,
  `RoutingRuleName` varchar(75) NOT NULL,
  PRIMARY KEY  (`SEOpageID`),
  KEY `IDX_SEOpageMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_SEOpageMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SystemMessageMaster
-- ----------------------------
CREATE TABLE `core_SystemMessageMaster` (
  `MessageID` int(10) unsigned NOT NULL auto_increment,
  `Title` varchar(75) default NULL,
  `Content` text NOT NULL,
  `StartDate` datetime default NULL,
  `EndDate` datetime default NULL,
  `IsAdminOnly` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`MessageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_SystemMessageReadLog
-- ----------------------------
CREATE TABLE `core_SystemMessageReadLog` (
  `MessageID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`MessageID`,`UserID`),
  KEY `IDX_SystemMessageReadLog_UserID` (`UserID`),
  CONSTRAINT `FK_SystemMessageReadLog_SystemMessageMaster` FOREIGN KEY (`MessageID`) REFERENCES `core_SystemMessageMaster` (`MessageID`),
  CONSTRAINT `FK_SystemMessageReadLog_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_TagMaster
-- ----------------------------
CREATE TABLE `core_TagMaster` (
  `TagID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `TagText` mediumtext NOT NULL,
  PRIMARY KEY  (`TagID`),
  KEY `IDX_TagMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_TagMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_ThumbnailMaster
-- ----------------------------
CREATE TABLE `core_ThumbnailMaster` (
  `ThumbnailID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `ImageID` int(10) unsigned NOT NULL default '0',
  `Height` int(10) unsigned NOT NULL default '0',
  `Width` int(10) unsigned NOT NULL default '0',
  `FileID` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ThumbnailID`),
  KEY `IDX_ThumbnailMaster_ImageID` (`ImageID`),
  KEY `IDX_ThumbnailMaster_FileID` (`FileID`),
  KEY `IDX_ThumbnailMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_ThumbnailMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`),
  CONSTRAINT `FK_ThumbnailMaster_FileMaster` FOREIGN KEY (`FileID`) REFERENCES `core_FileMaster` (`FileID`),
  CONSTRAINT `FK_ThumbnailMaster_ImageMaster` FOREIGN KEY (`ImageID`) REFERENCES `core_ImageMaster` (`ImageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_TransactionCodeMaster
-- ----------------------------
CREATE TABLE `core_TransactionCodeMaster` (
  `TransactionID` int(10) unsigned NOT NULL default '0',
  `SecurityCode` mediumtext NOT NULL,
  PRIMARY KEY  (`TransactionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_UserMaster
-- ----------------------------
CREATE TABLE `core_UserMaster` (
  `UserID` int(10) unsigned NOT NULL auto_increment,
  `AccountID` int(10) unsigned NOT NULL,
  `FirstName` varchar(50) NOT NULL default '',
  `LastName` varchar(50) NOT NULL default '',
  `Gender` char(1) default NULL,
  `Username` varchar(20) default NULL,
  `Password` varchar(50) default NULL,
  `PasswordSalt` varchar(75) default NULL,
  `IsBulkMailAllowed` tinyint(1) NOT NULL default '1',
  `IsDisabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`UserID`),
  KEY `IDX_UserMaster_AccountID` (`AccountID`),
  CONSTRAINT `FK_UserMaster_AccountMaster` FOREIGN KEY (`AccountID`) REFERENCES `core_AccountMaster` (`AccountID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_UserRole
-- ----------------------------
CREATE TABLE `core_UserRole` (
  `UserID` int(10) unsigned NOT NULL default '0',
  `RoleID` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`UserID`,`RoleID`),
  KEY `IDX_UserRole_RoleID` (`RoleID`),
  CONSTRAINT `FK_UserRole_RoleMaster` FOREIGN KEY (`RoleID`) REFERENCES `core_RoleMaster` (`RoleID`),
  CONSTRAINT `FK_UserRole_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for core_UserToken
-- ----------------------------
CREATE TABLE `core_UserToken` (
  `UserID` int(10) unsigned NOT NULL default '0',
  `Token` varchar(50) NOT NULL default '',
  `AccountID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`UserID`,`Token`),
  KEY `IDX_UserToken_AccountID` (`AccountID`),
  CONSTRAINT `FK_UserToken_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `core_AccountMaster` VALUES ('1', 'Default');
INSERT INTO `core_ActionMaster` VALUES ('1', 'UserCreated', 'User Created', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('2', 'UserEnabled', 'User Enabled', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('3', 'UserDisabled', 'User Disabled', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('4', 'UserRoleChanged', 'User Role Changed', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('5', 'UserLogin', 'User Login', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('6', 'UserLoginFailure', 'User Login Attempt Failure', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('7', 'UserLogout', 'User Logout', 'view', 'User');
INSERT INTO `core_ActionMaster` VALUES ('8', 'FileUpload', 'File Uploaded', 'download', 'File');
INSERT INTO `core_ActionMaster` VALUES ('9', 'ImageUpload', 'Image Uploaded', 'download', 'Image');
INSERT INTO `core_ActionMaster` VALUES ('10', 'CreditCardProcessSuccess', 'Credit Card Process Successful', null, 'CreditCard');
INSERT INTO `core_ActionMaster` VALUES ('11', 'CreditCardProcessFailed', 'Credit Card Process Failed', null, 'CreditCard');
INSERT INTO `core_ActionMaster` VALUES ('12', 'MessageCreated', 'New Message Created', 'view', 'Message');
INSERT INTO `core_ActionMaster` VALUES ('13', 'MessageDeleted', 'Message Deleted', 'view', 'Message');
INSERT INTO `core_ActionMaster` VALUES ('14', 'CommentCreated', 'New Comment Created', 'view', 'Message');
INSERT INTO `core_ActionMaster` VALUES ('15', 'CommentDeleted', 'Comment Deleted', 'view', 'Message');
INSERT INTO `core_ActiveMerchantAccount` VALUES ('1', '3', null, null);
INSERT INTO `core_AddressMaster` VALUES ('1', '1', '20545 Center Ridge Road\r\nSuite 202', 'Rocky River', 'OH', '44116', 'US');
INSERT INTO `core_CreditCardTransactionTypeMaster` VALUES ('1', 'Authorization');
INSERT INTO `core_CreditCardTransactionTypeMaster` VALUES ('2', 'Charge');
INSERT INTO `core_CreditCardTransactionTypeMaster` VALUES ('3', 'Credit');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('1', 'Diners Club', '0');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('2', 'American Express', '1');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('3', 'JCB', '0');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('4', 'Carte Blanche', '0');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('5', 'Visa', '1');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('6', 'MasterCard', '1');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('7', 'Australian BankCard', '0');
INSERT INTO `core_CreditCardTypeMaster` VALUES ('8', 'Discover/Novus', '1');
INSERT INTO `core_DBversion` VALUES ('3', '1', '1', '2008-08-27 11:42:45');
INSERT INTO `core_DBversion` VALUES ('3', '1', '2', '2008-09-05 13:58:31');
INSERT INTO `core_DBversion` VALUES ('3', '2', '0', '2008-09-13 10:15:34');
INSERT INTO `core_DBversion` VALUES ('3', '2', '1', '2008-09-17 15:15:19');
INSERT INTO `core_DBversion` VALUES ('3', '2', '2', '2008-10-16 16:44:45');
INSERT INTO `core_DBversion` VALUES ('3', '2', '3', '2008-10-17 14:20:19');
INSERT INTO `core_DBversion` VALUES ('4', '0', '0', '2008-11-07 14:19:11');
INSERT INTO `core_EmailMaster` VALUES ('1', '1', 'admin@designinginteractive.com');
INSERT INTO `core_EmailTypeMaster` VALUES ('1', 'Home');
INSERT INTO `core_EmailTypeMaster` VALUES ('2', 'Office');
INSERT INTO `core_EntityEmail` VALUES ('User', '1', '1', '2', '1');
INSERT INTO `core_MerchantAccountMaster` VALUES ('1', 'PayPal', 'PayPalProcessor', '0');
INSERT INTO `core_MerchantAccountMaster` VALUES ('2', 'CardService International', 'LinkPointProcessor', '0');
INSERT INTO `core_MerchantAccountMaster` VALUES ('3', 'Authorize.net', 'AuthorizeNetProcessor', '1');
INSERT INTO `core_MerchantAccountParameters` VALUES ('1', '3', 'x_login', '9mBhQUG25f8');
INSERT INTO `core_MerchantAccountParameters` VALUES ('1', '3', 'x_tran_key', '92myZ65rF48Fm2yA');
INSERT INTO `core_PhoneMaster` VALUES ('1', '1', '1', '440', '7994203');
INSERT INTO `core_PhoneTypeMaster` VALUES ('1', 'Home');
INSERT INTO `core_PhoneTypeMaster` VALUES ('2', 'Mobile');
INSERT INTO `core_PhoneTypeMaster` VALUES ('3', 'Office');
INSERT INTO `core_PhoneTypeMaster` VALUES ('4', 'Fax');
INSERT INTO `core_RoleMaster` VALUES ('1', 'User');
INSERT INTO `core_RoleMaster` VALUES ('2', 'Admin');
INSERT INTO `core_RoleMaster` VALUES ('3', 'Staff');
INSERT INTO `core_UserMaster` VALUES ('1', '1', 'Designing Interactive', 'Administrator', null, 'diadmin', '', null, '0', '0');
INSERT INTO `core_UserRole` VALUES ('1', '2');
