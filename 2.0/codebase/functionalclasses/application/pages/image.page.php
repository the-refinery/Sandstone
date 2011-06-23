<?php

SandstoneNamespace::Using("Sandstone.Application");
SandstoneNamespace::Using("Sandstone.File");
SandstoneNamespace::Using("Sandstone.Image");

class ImagePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = false;
	
	protected function Load_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Display Full Image
		$image = new Thumbnail($EventParameters['imageid'], Application::License()->ImagesPath);
		$image->Open();

		$image->X = $image->OriginalImage->Width;
		$image->Y = $image->OriginalImage->Height;
		
		$image->ShowImage();
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function ThumbnailByMax_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Display Full Image
		$image = new Thumbnail($EventParameters['imageid']);
		$image->Open();

		// Determine which X,Y is larger, and scale that way
		if ($image->OldX >= $image->OldY)
		{
			$image->X = $EventParameters['max'];
		}
		else
		{
			$image->Y = $EventParameters['max'];
		}
		
		$image->ShowImage();
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function Thumbnail_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		
		// Display Full Image
		$image = new Thumbnail($EventParameters['imageid']);
		$image->Open();

		$image->X = $EventParameters['width'];
		$image->Y = $EventParameters['height'];
		
		$image->ShowImage();
		
		$returnValue->Value = true;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function Upload_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		$this->BuildControlArray($EventParameters);
		
		$fileControl = $this->_controls['upload'];
		
		// Check that an image was uploaded
		if ($fileControl->Type == "image/gif" || $fileControl->Type == "image/png" || $fileControl->Type == "image/jpeg" || $fileControl->Type == "image/pjpeg")
		{
			$EventParameters['uploadpath'] = Application::License()->ImagesPath;
			$eventResults = Application::RaiseEvent("File", "Upload", $EventParameters);

			if ($eventResults->Value)
			{
				$image = new Image();
				$image->File = $eventResults->Value;
				list($image->Width, $image->Height) = getimagesize($image->File->URL);
				$image->Save();
			}
		}
		else
		{
			echo "TYPE FAILED";
			die();
		}

		$returnValue->Value = $image;
		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function UploadFromURL_Handler($EventParameters)
	{
		$returnValue = new EventResults();
		$this->BuildControlArray($EventParameters);
		
		$ext = strtolower(strrchr($this->_controls['url']->Value, ".")); 

		// Verify file type by extension
		if ($ext == ".gif" || $ext == ".png" || $ext == ".jpg" || $ext == ".jpeg")
		{
			$EventParameters['uploadpath'] = Application::License()->ImagesPath;
			$eventResults = Application::RaiseEvent("File", "UploadFromURL", $EventParameters);
			
			if ($eventResults->Value)
			{
				$image = new Image();
				$image->File = $eventResults->Value;
				list($image->Width, $image->Height) = getimagesize($image->File->URL);
				$image->Save();
			}
		}

		$returnValue->Complete();
		
		return $returnValue;
	}
	
	protected function BuildControlArray($EventParameters)
	{
		$this->tempControl = new FileControl();
		$this->tempControl->Name = "upload";
		$this->tempControl->Label->Text = "Upload File";
	}
}

?>