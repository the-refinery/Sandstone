<?php
/*
File Class File

@package Sandstone
@subpackage File
*/

class BaseFile extends EntityBase
{

	protected function SetupProperties()
	{
		$this->AddProperty("FileID","integer","FileID",PROPERTY_PRIMARY_ID);
		$this->AddProperty("FileName","string","FileName",PROPERTY_REQUIRED);
		$this->AddProperty("FileType","string","FileType",PROPERTY_REQUIRED);
		$this->AddProperty("Description","string","Description",PROPERTY_READ_WRITE);
		$this->AddProperty("CurrentVersion","FileVersion",null,PROPERTY_READ_ONLY,"LoadVersions");
		$this->AddProperty("Versions","array",null,PROPERTY_READ_ONLY,"LoadVersions");
		$this->AddProperty("DownloadCount","integer","DownloadCount",PROPERTY_READ_ONLY);
		$this->AddProperty("PhysicalFileName","string","PhysicalFileName",PROPERTY_REQUIRED);

		$this->AddProperty("Roles","array",null,PROPERTY_READ_ONLY,"LoadRoles");

		parent::SetupProperties();
	}

	public function LoadVersions()
	{

		$returnValue = false;

		$this->_versions->Clear();
		$this->_currentVersion = null;

		if ($this->IsLoaded)
		{
			$query = new Query();

			$selectClause = FileVersion::GenerateBaseSelectClause();

			$fromClause = FileVersion::GenerateBaseFromClause();

			$whereClause = "WHERE	a.FileID = {$this->_fileID} ";

			$orderByClause = "ORDER BY a.Version ";

			$query->SQL = $selectClause . $fromClause . $whereClause . $orderByClause;

			$query->Execute();

			$query->LoadEntityArray($this->_versions, "FileVersion", "Version", $this, "LoadVersionsCallback");

			$returnValue = true;
		}

		return $returnValue;

	}

	public function LoadVersionsCallback($Version)
	{
		$Version->File = $this;

		$this->_currentVersion = $Version;

		return $Version;
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_FileMaster
						(
							AccountID,
							FileName,
							FileType,
							Description,
							DownloadCount,
							PhysicalFileName
						)
						VALUES
						(
							{$this->AccountID},
							{$query->SetTextField($this->_fileName)},
							{$query->SetTextField($this->_fileType)},
							{$query->SetNullTextField($this->_description)},
							0,
							{$query->SetTextField($this->_physicalFileName)}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	public function LoadRoles()
	{

		$returnValue = false;

		$this->_roles->Clear();

		if ($this->IsLoaded)
		{
			$query = new Query();

			$selectClause = Role::GenerateBaseSelectClause();

			$fromClause = Role::GenerateBaseFromClause();
			$fromClause .= "INNER JOIN core_FileRoles b on b.RoleID = a.RoleID ";

			$whereClause = "WHERE	b.FileID = {$this->_fileID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_roles, "Role", "RoleID");

			$returnValue = true;
		}

		return $returnValue;

	}


	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_FileMaster SET
								Description = {$query->SetNullTextField($this->_description)}
							WHERE FileID = {$this->_fileID}";

		$query->Execute();

		return true;
	}

	public function AddVersion($VersionFileSpec, $FileSize, $UploadUser = null)
	{

        if (count($this->Versions) == 0)
        {
            $newVersionNumber = 1;
        }
        else
        {
            $newVersionNumber = $this->CurrentVersion->Version + 1;
        }

        $tempVersion = new FileVersion();
        $tempVersion->File = $this;
        $tempVersion->Version = $newVersionNumber;
        $tempVersion->FileSpec = $VersionFileSpec;
        $tempVersion->FileSize = $FileSize;
        $tempVersion->UploadTimestamp = new Date();

        if ($UploadUser instanceof User && $UploadUser->IsLoaded)
        {
            $tempVersion->UploadUser = $UploadUser;
        }

        $returnValue = $tempVersion->Save();

        //Reload our versions array
        $this->LoadVersions();

        return $returnValue;
	}

	public function AddRole($Role)
	{
		$returnValue = false;

		if ($this->IsLoaded && $Role instanceof Role && $Role->IsLoaded)
		{
			$query = new Query();

			$query->SQL = "DELETE 
				FROM core_FileRoles
				WHERE FileID = {$this->_fileID}
				AND RoleID = {$Role->RoleID} ";

			$query->Execute();

			$query->SQL = "INSERT INTO core_FileRoles
				(
					FileID,
					RoleID
				)
				VALUES
				(
		{$this->_fileID},
		{$Role->RoleID}
	) ";

			$query->Execute();

			$returnValue = $this->LoadRoles();

		}

		return $returnValue;
	}

	public function RemoveRole($Role)
	{
		$returnValue = false;

		if ($this->IsLoaded && $Role instanceof Role && $Role->IsLoaded)
		{
			$query = new Query();

			$query->SQL = "DELETE 
				FROM core_FileRoles
				WHERE FileID = {$this->_fileID}
				AND RoleID = {$Role->RoleID} ";

			$query->Execute();

			$returnValue = $this->LoadRoles();
		}

		return $returnValue;
	}

	public function Purge()
	{
		foreach ($this->Versions as $tempVersion)
		{
			if ($tempVersion->Version != $this->CurrentVersion->Version)
			{
				$tempVersion->Delete();
			}
		}

		//Reload our versions array
		$this->LoadVersions();

		return true;
	}

	public function Delete()
	{

		//Delete the physical file for each version
		foreach ($this->Versions as $tempVersion)
		{
			$tempVersion->Delete();
		}

		$query = new Query();

		//Now remove the file record
		$query->SQL = "	DELETE
						FROM	core_FileMaster
						WHERE FileID = {$this->_fileID}";

		$query->Execute();

		//Clean up this object
		$this->_fileID = null;
		$this->_fileName = null;
		$this->_description = null;
		$this->_currentVersion = null;
		$this->_versions->Clear();

		return true;
	}

	public function ValidateUserAccess($User)
	{
		$returnValue = false;

		if ($this->IsLoaded && $User instanceof User && $User->IsLoaded)
		{
			if (count($this->Roles) > 0)
			{
				foreach ($User->Roles as $tempRole)
				{
					if (array_key_exists($tempRole->RoleID, $this->_roles))
					{
						$returnValue = true;
					}
				}
			}
			else
			{
				$returnValue = true;
			}
		}

		return $returnValue;
	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.FileID,
										a.FileName,
										a.FileType,
										a.Description,
										a.DownloadCount,
										a.PhysicalFileName ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_FileMaster a ";

		return $returnValue;
	}

	static public function LookupFilenameCount($FileName)
	{

		$query = new Query();

		$query->SQL = "	SELECT	Count(*) FileCount
						FROM	core_FileMaster
						WHERE	FileName = {$query->SetTextField($FileName)} ";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$returnValue = $query->SingleRowResult['FileCount'];
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	static public function DecodeFilespec($FileSpec)
	{

		//Build an array of any directory structure
		if (strpos($FileSpec, "/") === false)
		{
			if (strpos($FileSpec, "\\") === false)
			{
				$directories = Array();
				$fullFileName = $FileSpec;
			}
			else
			{
				$directories = explode("\\", $FileSpec);

				$fileNameIndex = count($directories) - 1;

				$fullFileName = $directories[$fileNameIndex];
				unset($directories[$fileNameIndex]);
			}
		}
		else
		{
			$directories = explode("/", $FileSpec);

			$fileNameIndex = count($directories) - 1;

			$fullFileName = $directories[$fileNameIndex];
			unset($directories[$fileNameIndex]);
		}


		//Now pull the file name apart
		$dotPosition = strpos($fullFileName, ".");

		if ($dotPosition === false)
		{
			$fileName = $fullFileName;
			$extension = "";
		}
		else
		{
			$fileName = substr($fullFileName, 0, $dotPosition);
			$extension = substr($fullFileName, $dotPosition + 1);
		}

		//Build our return value
		$returnValue['Directories'] = $directories;
		$returnValue['FullFileName'] = $fullFileName;
		$returnValue['FileName'] = $fileName;
		$returnValue['Extension'] = $extension;

		return $returnValue;

	}

}
?>
