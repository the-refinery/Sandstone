<?php
/*
Image Upload Control Class File

@package Sandstone
@subpackage Application
*/

SandstoneNamespace::Using("Sandstone.Action");
SandstoneNamespace::Using("Sandstone.File");
SandstoneNamespace::Using("Sandstone.Image");

class ImageUploadControl extends FileUploadControl
{

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('imageupload_general');
		$this->_bodyStyle->AddClass('imageupload_body');

		//Default Image Upload Path
		$this->_fileUploadPath = Application::Registry()->ImagesPath;

		$this->_isLocalFileNeeded = true;

	}

	/*
	DefaultValue property

	@return address
	@param image $Value
	*/
	public function getDefaultValue()
	{
		return $this->_defaultValue;
	}

	public function setDefaultValue($Value)
	{
		if ($Value instanceof Image && $Value->IsLoaded)
		{
			$this->_defaultValue = $Value;
			$this->ImageID->DefaultValue = $Value->ImageID;
			$this->FileID->DefaultValue = $Value->File->FileID;
			$this->Description->DefaultValue = $Value->Description;
		}
		else
		{
			$this->_defaultValue = null;
			$this->ImageID->DefaultValue = null;
			$this->FileID->DefaultValue = null;
			$this->FileDescription->DefaultValue = null;
		}
	}

	protected function ParseEventParameters()
	{
		$file = $this->ProcessUpload();

		if (is_set($file))
		{
			//Were we uploading a new version of an existing file?
			if (is_numeric($this->ImageID->Value))
			{
				//our parent would have set the default value to a file,
				//let's replace it with the associated image
				$this->_defaultValue = new Image($this->ImageID->Value);

				//Since this is a new version, we need to clear any
				//existing thumbnails
				$this->_defaultValue->ClearThumbnails();

				//Set the value of this control to our existing image
				$this->_value = $this->_defaultValue;
			}
			else
			{
				//This is a new image upload!
				$this->_value = new Image();
				$this->_value->File = $file;
			}

			list($this->_value->Width, $this->_value->Height) = getimagesize($this->_localFileSpec);
			$this->_value->Description = $file->Description;
			$this->_value->Save();

			//If we are in AWS mode, we don't need the local file any longer, so clean it up
			if (Application::Registry()->AWSisActive)
			{
        		if (is_file($this->_localFileSpec))
				{
					unlink($this->_localFileSpec);
				}
			}

			Action::Log("ImageUpload", "The image '{$file->FileName}', version {$file->CurrentVersion->Version} was uploaded.", $this->_value->ImageID);
		}
		else
		{
			$this->_value = null;
		}

	}

    protected function SetupControls()
	{
		parent::SetupControls();

		$this->ImageID = new HiddenControl();
	}

	protected function SetupFileValidation()
	{
		$this->AddValidFileType("image/gif");
		$this->AddValidFileType("image/png");
		$this->AddValidFileType("image/jpeg");
		$this->AddValidFileType("image/pjpeg");

		$this->AddValidFileExtension("gif");
		$this->AddValidFileExtension("png");
		$this->AddValidFileExtension("jpg");
		$this->AddValidFileExtension("jpeg");
	}

}
?>
