<?php
/*
File Upload Control Class File

@package Sandstone
@subpackage Application
*/

NameSpace::Using("Sandstone.Action");
NameSpace::Using("Sandstone.File");
NameSpace::Using("Sandstone.AWS");

class FileUploadControl extends BaseControl
{

	const DEFAULT_INVALID_TYPE_MESSAGE = "Invalid File Type";

	protected $_fileUploadPath;

	protected $_validFileTypes;
	protected $_validFileExtensions;

	protected $_isFileTypeValid;
	protected $_invalidFileTypeMessage;

	protected $_defaultValue;

	protected $_fileContents;
	protected $_isLocalFileNeeded;

	protected $_localFileSpec;
	protected $_originalFileName;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('fileupload_general');
		$this->_bodyStyle->AddClass('fileupload_body');

		//Default File Upload Path
		$this->_fileUploadPath = Application::Registry()->FilesPath;

		//Setup our file validations
		$this->SetupFileValidation();
		$this->AddValidator("FileUploadControl", "ValidFileType");

		//Default Invalid Type message
		$this->_invalidFileTypeMessage = self::DEFAULT_INVALID_TYPE_MESSAGE;

		$this->_isRawValuePosted = false;
	}

	/*
	DefaultValue property

	@return address
	@param file $Value
	*/
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		if ($Value instanceof File && $Value->IsLoaded)
		{
			$this->_defaultValue = $Value;
			$this->FileID->DefaultValue = $Value->FileID;
			$this->Description->DefaultValue = $Value->Description;
		}
		else
		{
			$this->_defaultValue = null;
			$this->FileID->DefaultValue = null;
			$this->Description->DefaultValue = null;
		}
	}

	/*
	FileUploadPath property

	@return string
	@param string $Value
	*/
	public function getFileUploadPath()
	{
		return $this->_fileUploadPath;
	}

	public function setFileUploadPath($Value)
	{
		$this->_fileUploadPath = $Value;
	}

	/*
	ValidFileTypes property

	@return array
	*/
	public function getValidFileTypes()
	{
		return $this->_validFileTypes;
	}

	/*
	ValidFileExtensions property

	@return array
	*/
	public function getValidFileExtensions()
	{
		return $this->_validFileExtensions;
	}

	/*
	InvalidFileTypeMessage property

	@return string
	@param string $Value
	*/
	public function getInvalidFileTypeMessage()
	{
		return $this->_invalidFileTypeMessage;
	}

	public function setInvalidFileTypeMessage($Value)
	{
		if (str_len($Value > 0))
		{
			$this->_invalidFileTypeMessage = $Value;
		}
		else
		{
			$this->_invalidFileTypeMessage = self::DEFAULT_INVALID_TYPE_MESSAGE;
		}

	}

	/*
	IsFileTypeValid property

	@return boolean
	 */
	public function getIsFileTypeValid()
	{
		return $this->_isFileTypeValid;
	}

	public function getOriginalFileName()
	{
		return $this->_originalFileName;
	}

	public function getFileType()
	{
		return strtolower($this->FileUpload->Type);
	}

	protected function ParseEventParameters()
	{
		$this->_value = $this->ProcessUpload();
	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->FileID = new HiddenControl();

		$this->FileUpload = new FileControl();
		$this->FileUpload->ControlStyle->AddClass("fileupload_fileitem");
		$this->FileUpload->LabelText = "Upload from Your Computer";

		$this->FileURL = new TextBoxControl();
		$this->FileURL->ControlStyle->AddClass("fileupload_urlitem");
		$this->FileURL->LabelText = "Upload from the Internet";

		//By default, we don't show the description field
		$this->Description = new TextAreaControl();
		$this->Description->LabelText = "File Description";
		$this->Description->IsRendered = false;
	}

	protected function ProcessUpload()
	{
		//Setup our return value
		$returnValue = $this->SetupProcessFileUploadReturnValue();

		//Which mode are we in, File Upload or URL?
        if (strlen($this->FileUpload->FileName) > 0)
		{
			//File Upload
			$isValidUpload = $this->ProcessFileUpload($returnValue);
		}
		else if (is_set($this->FileURL->Value))
		{
			//URL
			$isValidUpload = $this->ProcessURLupload($returnValue);
		}
		else
		{
			//Nothing Passed
			$isValidUpload = false;
		}

		//Do we have a valid file upload?
		if ($isValidUpload)
		{
			//Log the file upload
			Action::Log("FileUpload", "The file '{$returnValue->FileName}', version {$returnValue->CurrentVersion->Version} was uploaded.", $returnValue->FileID);
		}
		else
		{
			//Invalid file upload
			$returnValue = null;
		}

		return $returnValue;

	}

	protected function SetupProcessFileUploadReturnValue()
	{

		// Create new file, or update if FileID is set
		if (is_numeric($this->FileID->Value))
		{
			$currentFileID = $this->FileID->Value;
			$this->_defaultValue = new File($currentFileID);
			$returnValue = $this->_defaultValue;
		}
		else
		{
			$returnValue = new File();
		}

		return $returnValue;
	}

	protected function ProcessFileUpload($FileObject)
	{

		//Validate our file type
		$isValid = $this->ValidateFileType($this->FileUpload->Type);

		if ($isValid)
		{
			$originalFileName = $this->FileUpload->FileName;
			$this->_originalFileName = $originalFileName;

		    // Generate the new physical file name and file spec
			$newFileName = $this->GenerateFilename($originalFileName, $FileObject);

			//Setup the file specs
			$this->_localFileSpec = $this->GenerateLocalFileSpec($newFileName);
			$versionFileSpec = $this->GenerateVersionFileSpec($newFileName);

			// Move the file to it's permenant or AWS local location
			move_uploaded_file($this->FileUpload->TemporaryFileSpec, $this->_localFileSpec) or die();

			//Determine the file size and type
			$newFileSize = filesize($this->_localFileSpec);
			$newFileType = $this->FileUpload->Type;

			//Save the file and version records
			$returnValue = $this->SaveFileAndVersion($FileObject, $originalFileName, $newFileName, $versionFileSpec, $newFileType, $newFileSize);

			//Do we need to move this file up to AWS?
			if ($returnValue == true && Application::Registry()->AWSisActive)
			{

            	//Pull the file's contents into memory
				$fh = fopen($this->_localFileSpec, 'rb' );
				$this->_fileContents = fread( $fh, $newFileSize);
				fclose( $fh );

				//Do the x-fer to AWS
				$returnValue = $this->ProcessAWSupload($versionFileSpec, $newFileType, $newFileName);

				//Delete the local copy, if we don't need it
				if ($this->_isLocalFileNeeded == false)
				{
					if (is_file($this->_localFileSpec))
					{
						unlink($this->_localFileSpec);
					}
				}
			}

		}
		else
		{
			$this->_validationMessage = $this->_invalidFileTypeMessage;
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function GenerateLocalFileSpec($NewFileName)
	{
		//If AWS is active, move the file to a temporary local location
		if (Application::Registry()->AWSisActive)
		{
			$targetPath = Application::Registry()->AWSlocalUploadPath;
		}
		else
		{
			$targetPath = $this->_fileUploadPath;
		}

		if (Application::Registry()->IsMultiAccount)
		{
			//For multi account applications, we create a directory
			//for each account
			$targetPath .= Application::License()->AccountID . "/";

			if (file_exists($targetPath) == false)
			{
				mkdir($targetPath);
			}
		}

		$returnValue = $targetPath . $NewFileName;

		return $returnValue;
	}

	protected function GenerateVersionFileSpec($NewFileName)
	{
		if (Application::Registry()->AWSisActive)
		{
			$returnValue = $NewFileName;
		}
		else
		{
			$returnValue = $this->_localFileSpec;
		}

		return $returnValue;

	}

	protected function ProcessURLupload($FileObject)
	{
		//Determine the original file name
		$path = explode('/', $this->FileURL->Value);
		$originalFileName = $path[count($path)-1];
		$this->_originalFileName = $originalFileName;

		//Get the size and type of the remote file
		$headers = array_change_key_case(get_headers($this->FileURL->Value, 1),CASE_LOWER);

		if (array_key_exists('content-length', $headers))
		{
			$newFileSize = $headers['content-length'];
		}

		if (array_key_exists('content-type', $headers))
		{
			$newFileType = $headers['content-type'];
		}

		$isValid = $this->ValidateFileType($newFileType);

		if ($isValid)
		{
        	// Generate the new physical file name and file spec
			$newFileName = $this->GenerateFilename($originalFileName, $FileObject);

			//Setup the file specs
			$this->_localFileSpec = $this->GenerateLocalFileSpec($newFileName);
			$versionFileSpec = $this->GenerateVersionFileSpec($newFileName);

			//Get the contents of the remote file
//			$this->_fileContents = file_get_contents($this->FileURL->Value);

$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_URL, $this->FileURL->Value);
$this->_fileContents = curl_exec($c);
curl_close($c);

header("Content-type: image/jpeg;");
di_echo($this->_fileContents);

			//Save the file and version records
			$returnValue = $this->SaveFileAndVersion($FileObject, $originalFileName, $newFileName, $newFileSpec, $newFileType, $newFileSize);

			// Create the new file and save the file contents, if this AWS isn't active
			if (Application::Registry()->AWSisActive)
			{
            	//Do the x-fer to AWS
				$returnValue = $this->ProcessAWSupload($newFileSpec, $newFileType, $originalFileName);
			}

			//If we aren't in AWS mode, or we need the file later, save it locally
			if (Application::Registry()->AWSisActive == false || $this->_isLocalFileNeeded)
			{
				//Save it locally
				$fp = fopen($this->_localFileSpec, "w");
				fwrite($fp, $this->_fileContents);
				fclose($fp);
			}

			$returnValue = true;
		}
		else
		{
			$this->_validationMessage = $this->_invalidFileTypeMessage;
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function SaveFileAndVersion($FileObject, $OriginalFileName, $PhysicalFileName, $FileSpec, $FileType, $FileSize)
	{
		//If we are uploading an additional version, we don't change the
		//filename or filetype
		if ($FileObject->IsLoaded == false)
		{
			$FileObject->FileName = $OriginalFileName;
			$FileObject->FileType = $FileType;
			$FileObject->PhysicalFileName = $PhysicalFileName;
		}

		$FileObject->Description = $this->Description->Value;

		//Save the file
		$returnValue = $FileObject->Save();

		if ($returnValue == true)
		{
			//Add a new file version
			$returnValue = $FileObject->AddVersion($FileSpec, $FileSize, Application::CurrentUser());
		}

		return $returnValue;

	}

	protected function ProcessAWSupload($FileSpec, $FileType, $FileName)
	{
		//Create the object on AWS
		$s3svc = new S3();

		$s3svc->putObject($FileName, $this->_fileContents, Application::Registry()->AWSbucket, 'private', $FileType, array("data-file-name"=>$OriginalFileName));

		//Need to determine how to evaluate success/failure here
		$returnValue = true;

		return $returnValue;
	}

	protected function ValidateFileType($FileType)
	{
		//If we are uploading a new version of an existing file,
		//the filetype MUST match
		if (is_set($this->_defaultValue))
		{
			if ($FileType == $this->_defaultValue->FileType)
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			//New File
			if (count($this->_validFileTypes) > 0)
			{
				$FileType = strtolower($FileType);

				if (in_array($FileType, $this->_validFileTypes))
				{

					$returnValue = true;
				}
				else
				{
					$returnValue = false;
				}
			}
			else
			{
				//No valid types set so all are allowed
				$returnValue = true;
			}
		}

		$this->_isFileTypeValid = $returnValue;

		return $returnValue;
	}

	protected function ValidateFileExtension($FileName)
	{
		if (count($this->_validFileExtensions) > 0)
		{
			$fileExtension = strtolower(substr(strrchr($FileName,"."), 1));

			if (in_array($fileExtension, $this->_validFileExtensions))
			{
				$returnValue = true;
			}
			else
			{
				$returnValue = false;
			}
		}
		else
		{
			//No valid extensions set so all are allowed
			$returnValue = true;
		}

		return $returnValue;
	}

	protected function GenerateFilename($UploadedFilename, $CurrentFileObject)
	{
		//Are we uploading a new version, or a completely new file?
		if ($CurrentFileObject->IsLoaded)
		{
			//New Version, so it's filename-V#.extension
			$nextVersionNumber = $CurrentFileObject->CurrentVersion->Version + 1;

			$fileSpecDecode = file::DecodeFilespec($CurrentFileObject->PhysicalFileName);

			$returnValue = "{$fileSpecDecode['FileName']}-v{$nextVersionNumber}.{$fileSpecDecode['Extension']}";
		}
		else
		{
			//New File

			//Do we have any other files with this file name?
			$fileCount = File::LookupFilenameCount($UploadedFilename);

			if ($fileCount == 0)
			{
				//This is the first of this file name
				$returnValue = $UploadedFilename;
			}
			else
			{
				//We have others, so it's filename-#.extension
				$fileSpecDecode = file::DecodeFilespec($UploadedFilename);

				$returnValue = "{$fileSpecDecode['FileName']}-{$fileCount}.{$fileSpecDecode['Extension']}";
			}
		}

		return $returnValue;
	}

	protected function SetupFileValidation()
	{
		$this->_validFileTypes = Array();
		$this->_validFileExtensions = Array();
	}

	protected function AddValidFileType($FileType)
	{
		$FileType = strtolower($FileType);
		$this->_validFileTypes[$FileType] = $FileType;
	}

	protected function AddValidFileExtension($FileExtension)
	{
		$FileExtension = strtolower($FileExtension);

		$this->_validFileExtensions[$FileExtension] = $FileExtension;
	}

	public function ValidFileType($Control)
	{

		if ($Control->IsFileTypeValid == false)
		{
			$returnValue = $Control->InvalidFileTypeMessage;
		}

		return $returnValue;
	}

}
?>