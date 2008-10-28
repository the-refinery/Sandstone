<?php
/**
 * File Class File
 * @package Sandstone
 * @subpackage File
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

NameSpace::Using("Sandstone.ADOdb");

class File extends EntityBase
{

	public function __construct($ID = null)
	{

		parent::__construct($ID);

		if (is_set($ID) == false)
		{
			//We'll default to a version of 1.
			$this->_version = 1;
		}

	}

	protected function SetupProperties()
	{
		$this->AddProperty("FileID","integer","FileID",true,false,true,false,false,null);
		$this->AddProperty("URL","string","URL",false,true,false,false,false,null);
		$this->AddProperty("FileName","string","FileName",false,true,false,false,false,null);
		$this->AddProperty("Description","string","Description",false,false,false,false,false,null);
		$this->AddProperty("Version","int","Version",false,true,false,false,false,null);
		$this->AddProperty("UploadTimestamp","date","UploadTimestamp",false,false,false,false,false,null);
		$this->AddProperty("UploadUser","User","UploadUserID",false,false,false,true,false,null);

		parent::SetupProperties();
	}

	/**
	 * PhysicalFileName property
	 *
	 * @return string
	 */
	public function getPhysicalFileName()
	{
		if (is_set($this->_url))
		{
			$lastSlashPosition = strrpos($this->_url, "/");

			$returnValue = substr($this->_url, $lastSlashPosition + 1);
		}
		else
		{
			$returnValue = null;
		}


		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		if (is_set($this->_uploadUser))
		{
			$uploadUserID = $this->_uploadUser->UserID;
		}
		else
		{
			$uploadUserID = null;
		}

		$query = "	INSERT INTO core_FileMaster
							(
								URL,
								FileName,
								Description,
								Version,
								UploadTimestamp,
								UploadUserID
							)
							VALUES
							(
								{$conn->SetTextField($this->_uRL)},
								{$conn->SetTextField($this->_fileName)},
								{$conn->SetNullTextField($this->_description)},
								{$this->_version},
								{$conn->SetNullDateField($this->_uploadTimestamp)},
								{$conn->SetNullNumericField($uploadUserID)}
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

		//This update will increment the version
		$this->_version += 1;

		if (is_set($this->_uploadUser))
		{
			$uploadUserID = $this->_uploadUser->UserID;
		}
		else
		{
			$uploadUserID = null;
		}

		$query = "	UPDATE core_FileMaster SET
								URL = {$conn->SetTextField($this->_uRL)},
								FileName = {$conn->SetTextField($this->_fileName)},
								Description = {$conn->SetNullTextField($this->_description)},
								Version = {$this->_version},
								UploadTimestamp = {$conn->SetNullDateField($this->_uploadTimestamp)},
								UploadUserID = {$conn->SetNullNumericField($uploadUserID)}
							WHERE FileID = {$this->_fileID}";

		$conn->Execute($query);

		return true;
	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.FileID,
										a.URL,
										a.FileName,
										a.Description,
										a.Version,
										a.UploadTimestamp,
										a.UploadUserID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_FileMaster a ";

		return $returnValue;
	}

	public function Delete($conn = null)
	{

		if (is_set($conn) == false)
		{
			$conn = GetConnection();
		}

		//Delete the physical file
		unlink($this->_url);

		//Now remove the file record
		$query = "	DELETE
					FROM	core_FileMaster
					WHERE FileID = {$this->_fileID}";

		$conn->Execute($query);

		//Clean up this object
		$this->_fileID = null;
		$this->_url = null;
		$this->_fileName = null;
		$this->_description = null;
		$this->_version = 1;
		$this->_uploadTimestamp = null;
		$this->_uploadUser = null;

	}

}
?>