<?php
/*
File Class File

@package Sandstone
@subpackage File
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class File extends EntityBase
{

	protected function SetupProperties()
	{

		//AddProperty Parameters:
		// 1) Name
		// 2) DataType
		// 3) DBfieldName
		// 4) IsReadOnly
		// 5) IsRequired
		// 6) IsPrimaryID
		// 7) IsLoadedRequired
		// 8) IsLoadOnDemand
		// 9) LoadOnDemandFunctionName


		$this->AddProperty("FileID","integer","FileID",true,false,true,false,false,null);
		$this->AddProperty("FileName","string","FileName",false,true,false,false,false,null);
		$this->AddProperty("FileType","string","FileType",false,true,false,false,false,null);
		$this->AddProperty("Description","string","Description",false,false,false,false,false,null);
		$this->AddProperty("CurrentVersion","FileVersion",null,true,false,false,false,true,"LoadVersions");
		$this->AddProperty("Versions","array",null,true,false,false,false,true,"LoadVersions");
		$this->AddProperty("DownloadCount","integer","DownloadCount",true,false,false,false,false,null);
		$this->AddProperty("PhysicalFileName","string","PhysicalFileName",false,true,false,false,false,null);

		parent::SetupProperties();
	}

	public function LoadVersions()
	{

		$this->_versions->Clear();
		$this->_currentVersion = null;

		if ($this->IsLoaded)
		{
			$conn = GetConnection();

			$selectClause = FileVersion::GenerateBaseSelectClause();

			$fromClause = FileVersion::GenerateBaseFromClause();

			$whereClause = "WHERE	a.FileID = {$this->_fileID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				$maxVersionID = 0;

				//Load the companies
				while ($dr = $ds->FetchRow())
				{
					$tempVersion = new FileVersion($dr);

					$tempVersion->File = $this;

					$this->_versions[$tempVersion->Version] = $tempVersion;

					if ($tempVersion->Version > $maxVersionID)
					{
						$maxVersionID = $tempVersion->Version;
					}
				}

				if (count($this->_versions) > 0)
				{
					$this->_currentVersion = $this->_versions[$maxVersionID];
				}

				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			$returnValue = false;
		}
		return $returnValue;


	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_FileMaster
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
								{$conn->SetTextField($this->_fileName)},
								{$conn->SetTextField($this->_fileType)},
								{$conn->SetNullTextField($this->_description)},
								0,
								{$conn->SetTextField($this->_physicalFileName)}
							)";

		$conn->Execute($query);

		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";

		$dr = $conn->GetRow($query);

		$this->_primaryIDproperty->Value = $dr['newID'];

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$conn = GetConnection();

		$query = "	UPDATE core_FileMaster SET
								Description = {$conn->SetNullTextField($this->_description)}
							WHERE FileID = {$this->_fileID}";

		$conn->Execute($query);

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

		$conn = GetConnection();

		//Now remove the file record
		$query = "	DELETE
					FROM	core_FileMaster
					WHERE FileID = {$this->_fileID}";

		$conn->Execute($query);

		//Clean up this object
		$this->_fileID = null;
		$this->_fileName = null;
		$this->_description = null;
		$this->_currentVersion = null;
		$this->_versions->Clear();

		return true;
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

		$conn = GetConnection();

		$query = "	SELECT	Count(*) FileCount
					FROM	core_FileMaster
					WHERE	FileName = '{$FileName}'";

		$ds = $conn->Execute($query);

		if ($ds)
		{
			$dr = $ds->FetchRow();

			$returnValue = $dr['FileCount'];
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