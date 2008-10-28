<?php
/**
 * Image Upload Control Class File
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
NameSpace::Using("Sandstone.Image");

class ImageUploadControl extends FileUploadControl
{

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_controlStyle->AddClass('imageupload_general');
		$this->_bodyStyle->AddClass('imageupload_body');

		$this->Message->BodyStyle->AddClass('imageupload_message');
		$this->Label->BodyStyle->AddClass('imageupload_label');

		//Default Image Upload Path
		$this->_fileUploadPath = Application::License()->ImagesPath;
	}

	/**
	 * DefaultValue property
	 *
	 * @return address
	 *
	 * @param image $Value
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
			$this->FileID->DefaultValue = $Value->File->FileID;
		}
		else
		{
			$this->_defaultValue = null;
			$this->FileID->DefaultValue = null;
		}
	}

	protected function ParseEventParameters()
	{
		$file = $this->ProcessFileUpload();

		if (is_set($file))
		{
				$this->_value = new Image();
				$this->_value->File = $file;
				list($this->_value->Width, $this->_value->Height) = getimagesize($file->URL);
				$this->_value->Description = $file->Description;
				$this->_value->Save();

				Action::Log("ImageUpload", "The image {$file->FileName} was uploaded.", $this->_value->ImageID);
		}
		else
		{
			$this->_value = null;
		}

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
