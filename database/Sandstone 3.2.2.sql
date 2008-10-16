SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `core_DBversion` VALUES ('3', '2', '2', '2008-10-16 16:44:45');

CREATE TABLE `core_SystemMessageMaster` (
  `MessageID` int(10) unsigned NOT NULL auto_increment,
  `Title` varchar(75) default NULL,
  `Content` text NOT NULL,
  `StartDate` datetime default NULL,
  `EndDate` datetime default NULL,
  `IsAdminOnly` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`MessageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `core_SystemMessageReadLog` (
  `MessageID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`MessageID`,`UserID`),
  KEY `IDX_SystemMessageReadLog_UserID` (`UserID`),
  CONSTRAINT `FK_SystemMessageReadLog_SystemMessageMaster` FOREIGN KEY (`MessageID`) REFERENCES `core_SystemMessageMaster` (`MessageID`),
  CONSTRAINT `FK_SystemMessageReadLog_UserMaster` FOREIGN KEY (`UserID`) REFERENCES `core_UserMaster` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
