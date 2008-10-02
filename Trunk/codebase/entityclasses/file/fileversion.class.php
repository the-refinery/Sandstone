<?php
/*
FileVersion Class File

@package Sandstone
@subpackage File
 */

NameSpace::Using("Sandstone.ADOdb");

class FileVersion extends EntityBase
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

		$this->AddProperty("File","File",null,false,true,false,false,false,null);
		$this->AddProperty("Version","integer","Version",false,false,false,false,false,null);
		$this->AddProperty("FileSpec","string","FileSpec",false,true,false,false,false,null);
		$this->AddProperty("FileSize","integer","FileSize",false,true,false,false,false,null);
		$this->AddProperty("UploadTimestamp","date","UploadTimestamp",false,false,false,false,false,null);
		$this->AddProperty("UploadUser","User","UploadUserID",false,false,false,true,false,null);
		$this->AddProperty("DownloadCount","integer","DownloadCount",true,false,false,false,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		if (is_set($this->_uploadUser))
		{
			$userID = $this->_uploadUser->UserID;
		}

		$query = "	INSERT INTO core_FileVersions
							(
								FileID,
								Version,
								FileSpec,
								FileSize,
								UploadTimestamp,
								UploadUserID,
								DownloadCount
							)
							VALUES
							(
								{$this->_file->FileID},
								{$this->_version},
								{$conn->SetTextField($this->_fileSpec)},
								{$this->_fileSize},
								{$conn->SetNullDateField($this->_uploadTimestamp)},
								{$conn->SetNullNumericField($userID)},
								0
							)";

		$conn->Execute($query);

		return true;
	}

	protected function SaveUpdateRecord()
	{
		return true;
	}

	public function Delete()
	{

		//Delete the file

		//Is AWS active?
		if (Application::Registry()->AWSisActive)
		{
			//AWS mode
			$s3svc = new S3();
			$s3svc->deleteObject($this->_fileSpec, Application::Registry()->AWSbucket);
		}
		else
		{
			//Local mode
            if (is_file($this->_fileSpec))
			{
				unlink($this->_fileSpec);
			}

		}


		$conn = GetConnection();

		//Now remove the file version record
		$query = "	DELETE
					FROM	core_FileVersions
					WHERE 	FileID = {$this->_file->FileID}
					AND		Version = {$this->Version} ";

		$conn->Execute($query);

	}

	public function LogDownload()
	{

		$remoteIP = $_SERVER['REMOTE_ADDR'];

		if (is_set(Application::CurrentUser()))
		{
			$userID = Application::CurrentUser()->UserID;
		}

		$conn = GetConnection();

		//Create the download log record
		$query = "	INSERT INTO core_FileDownloadLog
					(
						AccountID,
						Timestamp,
						FileID,
						Version,
						FileSpec,
						FileSize,
						UserID,
						UserIPaddress
					)
					VALUES
					(
						{$this->AccountID},
						NOW(),
						{$this->_file->FileID},
						{$this->_version},
						{$conn->SetTextField($this->_fileSpec)},
						{$conn->SetNullNumericField($this->_fileSize)},
						{$conn->SetNullNumericField($userID)},
						{$conn->SetNullTextField($remoteIP)}
					)";

		$conn->Execute($query);

		//Update the download count for this version
		$query = "	UPDATE core_FileVersions SET
						DownloadCount = DownloadCount + 1
					WHERE 	FileID = {$this->_file->FileID}
					AND		Version = {$this->_version}";

		$conn->Execute($query);

		//Update the download count for this file
		$query = "	UPDATE core_FileMaster SET
						DownloadCount = DownloadCount + 1
					WHERE FileID = {$this->_file->FileID}";

		$conn->Execute($query);

		return $returnValue;
	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.FileID,
										a.Version,
										a.FileSpec,
										a.FileSize,
										a.UploadTimestamp,
										a.UploadUserID,
										a.DownloadCount ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_FileVersions a ";

		return $returnValue;
	}

}
?>