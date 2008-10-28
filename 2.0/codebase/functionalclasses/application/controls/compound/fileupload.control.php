<?php
/**
 * File Upload Control Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

NameSpace::Using("Sandstone.Action");
NameSpace::Using("Sandstone.File");

class FileUploadControl extends BaseControl
{

	const DEFAULT_INVALID_TYPE_MESSAGE = "Invalid File Type";

	protected $_fileUploadPath;

	protected $_validFileTypes;
	protected $_validFileExtensions;

	protected $_invalidFileTypeMessage;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('fileupload_general');
		$this->_bodyStyle->AddClass('fileupload_body');

		$this->Message->BodyStyle->AddClass('fileupload_message');
		$this->Label->BodyStyle->AddClass('fileupload_label');

		//Default File Upload Path
		$this->_fileUploadPath = Application::License()->FilesPath;

		$this->SetupFileValidation();

		//Default Invalid Type message
		$this->_invalidFileTypeMessage = self::DEFAULT_INVALID_TYPE_MESSAGE;

		$this->_isRawValuePosted = false;
	}

	/**
	 * DefaultValue property
	 *
	 * @return address
	 *
	 * @param file $Value
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

	/**
	 * FileUploadPath property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getFileUploadPath()
	{
		return $this->_fileUploadPath;
	}

	public function setFileUploadPath($Value)
	{
		$this->_fileUploadPath = $Value;
	}

	/**
	 * ValidFileTypes property
	 *
	 * @return array
	 */
	public function getValidFileTypes()
	{
		return $this->_validFileTypes;
	}

	/**
	 * ValidFileExtensions property
	 *
	 * @return array
	 */
	public function getValidFileExtensions()
	{
		return $this->_validFileExtensions;
	}

	/**
	 * InvalidFileTypeMessage property
	 *
	 * @return string
	 *
	 * @param string $Value
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

	protected function ParseEventParameters()
	{
		$this->_value = $this->ProcessFileUpload();
	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->Template = "{Label} {FileUpload} {FileURL} {Description}";

		$this->FileID = new HiddenControl();

		$this->FileUpload = new FileControl();
		$this->FileUpload->ControlStyle->AddClass("fileupload_fileitem");
		$this->FileUpload->Label->Text = "Upload from Your Computer";
		$this->FileUpload->Effects->Scope= $this->_effects->Scope;

		$this->FileURL = new TextBoxControl();
		$this->FileURL->ControlStyle->AddClass("fileupload_urlitem");
		$this->FileURL->Label->Text = "Upload from the Internet";
		$this->FileURL->Effects->Scope= $this->_effects->Scope;

		//By default, we don't show the description field
		$this->Description = new TextAreaControl();
		$this->Description->Effects->Scope= $this->_effects->Scope;
		$this->Description->Label->Text = "File Description";
		$this->Description->IsRendered = false;
	}

	protected function ProcessFileUpload()
	{
		// Create new file, or update if FileID is set
		if (is_numeric($this->FileID->Value))
		{
			$currentFileID = $this->FileID->Value;
		}

		$returnValue = new File($currentFileID);

		//Do we have an uploaded file, or a URL?
		if (strlen($this->FileUpload->FileName) > 0)
		{
			$isValid = $this->ValidateFileType($this->FileUpload->Type);

			if ($isValid)
			{
				$originalFileName = $this->FileUpload->FileName;

		        // Generate the new physical file name and file spec
				$newFileName = $this->GenerateFilename($originalFileName);
				$newFileSpec = $this->_fileUploadPath . $newFileName;

				// Move the file to it's permenant location
				move_uploaded_file($this->FileUpload->TemporaryFileSpec, $newFileSpec) or die();

				$isValidUpload = true;
			}
			else
			{
				$this->_validationMessage = "Invalid File Type";
				$isValidUpload = false;
			}

		}
		else if (is_set($this->FileURL->Value))
		{

			//Determine the original file name
			$path = explode('/', $this->FileURL->Value);
			$originalFileName = $path[count($path)-1];

			$isValid = $this->ValidateFileExtension($originalFileName);

			if ($isValid)
			{
        		// Generate the new physical file name and file spec
				$newFileName = $this->GenerateFilename($originalFileName);
				$newFileSpec = $this->_fileUploadPath . $newFileName;

				//Get the contents of the remote file
				$filecontents = file_get_contents($this->FileURL->Value);

				// Create the new file and save the file contents
				$fp = fopen($newFileSpec, "w");
				fwrite($fp, $filecontents);
				fclose($fp);

				$isValidUpload = true;
			}
			else
			{
				$isValidUpload = false;
			}

		}
		else
		{
			//Nothing passed
			$isValidUpload = false;
		}

		if ($isValidUpload)
		{
			// Update and Save the File Object
			$returnValue->URL = $newFileSpec;
			$returnValue->FileName = $originalFileName;
			$returnValue->Description = $this->Description->Value;
			$returnValue->UploadUser = Application::CurrentUser();
			$returnValue->UploadTimestamp = new Date();

			$returnValue->Save();

			Action::Log("FileUpload", "The file {$originalFileName} was uploaded.", $returnValue->FileID);
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

	protected function ValidateFileType($FileType)
	{

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

	protected function GenerateFilename($UploadedFilename)
	{
		$fileExtension = strtolower(strrchr($UploadedFilename,"."));

		$returnValue = date("Ymd.His") . $fileExtension;

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

}
?>
