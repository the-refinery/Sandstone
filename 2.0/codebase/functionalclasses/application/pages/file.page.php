<?php

SandstoneNamespace::Using("Sandstone.Application");
SandstoneNamespace::Using("Sandstone.File");

class FilePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = false;
	
	protected function Upload_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		$this->BuildControlArray($EventParameters);
		
		$fileControl = $this->_controls['upload'];
		
		// Create new file, or update if FileID is set
		$tempFile = new File($EventParameters['fileid']);
		
		// Generate Filename
		$filename = $this->GenerateFilename($fileControl->FileName);
		
		// Where do we upload to?
		if (is_set($EventParameters['uploadpath']))
		{
			$UploadPath = $EventParameters['uploadpath'];
		}
		else
		{
			$UploadPath = Application::License()->FilesPath;
		}

		// Move the file to it's permenant location
		move_uploaded_file($fileControl->TemporaryFileSpec, $UploadPath . $filename) or die($e); 
		
		// Save File Object
		$tempFile->URL = $UploadPath . $filename;
		$tempFile->FileName = $fileControl->FileName;
		$tempFile->Save();
		
		$returnValue->Value = $tempFile;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function UploadFromURL_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		$this->BuildControlArray($EventParameters);
		
		$tempFile = new File($EventParameters['fileid']);
		
		$fileExtension = strtolower(strrchr($this->_controls['url'],".")); 

		$filecontents = file_get_contents($this->_controls['url']);
		$path = explode('/', $this->_controls['url']);
		$filename = $path[count($path)-1];
		$newfilename = $this->GenerateFilename($filename);
		
		// Where do we upload to?
		if (is_set($EventParameters['uploadpath']))
		{
			$UploadPath = $EventParameters['uploadpath'];
		}
		else
		{
			$UploadPath = Application::License()->FilesPath;
		}
		
		// Create the new file
		$fp = fopen($UploadPath . $newfilename, "w");
		
		fwrite($fp, $filecontents);
		fclose($fp);
		
		$tempFile->URL = $UploadPath . $newfilename;
		$tempFile->FileName = $filename;
		$tempFile->Save();
		
		$returnValue->Value = $tempFile;
		$returnValue->Complete();
			
		return $returnValue;
	}
	
	protected function GenerateFilename($UploadedFilename)
	{
		$fileExtension = strtolower(strrchr($UploadedFilename,"."));
		
		$returnValue = date("Ymd.His.") . $fileExtension;
		
		return $returnValue;
	}
	
	protected function BuildControlArray($EventParameters)
	{
		$tempControl = new FileControl();
		$tempControl->Name = "upload";
		$tempControl->Label = "Upload File";
		$this->AddPageControl($tempControl, $EventParameters);
	}
}

?>