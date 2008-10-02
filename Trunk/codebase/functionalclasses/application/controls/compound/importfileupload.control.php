<?php
/*
File Upload Control Class File

@package Sandstone
@subpackage Application
*/

NameSpace::Using("Sandstone.Action");
NameSpace::Using("Sandstone.File");
NameSpace::Using("Sandstone.AWS");

class ImportFileUploadControl extends FileUploadControl
{

	public function __construct()
	{
		parent::__construct();

		$this->Template->FileName = "fileupload";
		$this->_isLocalFileNeeded = true;
	}

	public function getLocalFileSpec()
	{
		return $this->_localFileSpec;;
	}

	protected function SaveFileAndVersion($FileObject, $OriginalFileName, $PhysicalFileName, $FileSpec, $FileType, $FileSize)
	{
		return true;
	}

    protected function ProcessAWSupload($FileSpec, $FileType, $OriginalFileName)
	{
		return true;
	}

}
?>