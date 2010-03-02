SET FOREIGN_KEY_CHECKS=0;

INSERT INTO core_DBversion VALUES ('4', '0', '7', '2010-02-23 16:20');

CREATE TABLE core_FileRoles (
	FileID int(10) unsigned NOT NULL,
	RoleID int(10) unsigned NOT NULL,
	PRIMARY KEY (FileID, RoleID),
	KEY IDX_FileRoles_RoleID (RoleID),
	CONSTRAINT FK_FileRoles_FileMaster FOREIGN KEY (FileID) REFERENCES core_FileMaster (FileID),
	CONSTRAINT FK_FileRoles_RoleMaster FOREIGN KEY (RoleID) REFERENCES core_RoleMaster (RoleID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
