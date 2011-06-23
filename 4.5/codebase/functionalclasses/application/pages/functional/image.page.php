<?php

SandstoneNamespace::Using("Sandstone.Application");
SandstoneNamespace::Using("Sandstone.File");
SandstoneNamespace::Using("Sandstone.Image");
SandstoneNamespace::Using("Sandstone.AWS");

class ImagePage extends BasePage
{
	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected $_selectedImage;
	protected $_selectedVersion;

	protected function Generic_PreProcessor(&$EventParameters)
	{
		$this->_selectedImage = new Image($EventParameters['imageid']);

		//Did we get a valid ID?
		if ($this->_selectedImage->IsLoaded == false)
		{
			//Switch over to HTM file type
			$EventParameters['filetype'] = "htm";
			$this->SetResponseCode(404, $EventParameters);
		}
		else
		{
			$requestedFileName = strtolower("{$EventParameters['filename']}.{$EventParameters['filetype']}");

			//Does the filename match?  (case insensitive)
			if ($requestedFileName != strtolower($this->_selectedImage->File->FileName))
			{
				//File name doesn't match

				//Switch over to HTM file type
				$EventParameters['filetype'] = "htm";
				$this->SetResponseCode(404, $EventParameters);
			}
			else
			{
				//Do we have a specific version requested?
				if (is_set($EventParameters['fileversion']))
				{
					//Does this version exist?
					if (is_set($this->_selectedImage->File->Versions[$EventParameters['fileversion']]) == false)
					{
						//That version number doesn't exist

						//Switch over to HTM file type
						$EventParameters['filetype'] = "htm";
						$this->SetResponseCode(404, $EventParameters);

					}
					else
					{
						//We are good to go with the selected version
						$this->_selectedVersion = $this->_selectedImage->File->Versions[$EventParameters['fileversion']];

						//Is a thumbnail requested?
						if (is_set($EventParameters['isthumbnail']))
						{
							$EventParameters['filetype'] = "thumbnail";
						}
						else
						{
							$EventParameters['filetype'] = "file";
						}
					}
				}
				else
				{
					//We are good to go with the current version
					$this->_selectedVersion = $this->_selectedImage->File->CurrentVersion;

					//Is a thumbnail requested?
					if (is_set($EventParameters['isthumbnail']))
					{
						$EventParameters['filetype'] = "thumbnail";
					}
					else
					{
						$EventParameters['filetype'] = "file";
					}
				}
			}
		}
	}

	protected function FILE_Processor($EventParameters)
	{
    	//Log the download
		$this->_selectedVersion->LogDownload();

		//Are we in AWS mode?
		if (Application::Registry()->AWSisActive)
		{
			//Generate the URL
			$s3conn = new S3;
			$url = $s3conn->GenerateFileURL($this->_selectedVersion->FileSpec);

			//Redirect to this URL
			Application::Redirect($url);
		}
		else
		{
			//Local Mode

			//Depending on the file type, we handle things differently
			switch ($this->_selectedImage->File->FileType)
			{
				case "image/jpeg":
					self::DisplayJPEG($this->_selectedVersion->FileSpec);
					break;

				case "image/gif":
					self::DisplayGIF($this->_selectedVersion->FileSpec);
					break;

				case "image/png":
					self::DisplayPNG($this->_selectedVersion->FileSpec);
					break;
			}
		}
	}

	protected function THUMBNAIL_Processor($EventParameters)
	{
		//Am I going for a specific size, or a max size?
		if ($EventParameters['matchedrule'] == "thumbnail")
		{
			//Specific size

			//Are we within our size limits?
			if ($EventParameters['width'] <= 800 && $EventParameters['height'] <= 600)
			{
				$targetThumbnail = $this->_selectedImage->FindThumbnail($EventParameters['height'], $EventParameters['width']);
			}
		}
		else
		{
			//Max size

			//Are we within the size limits?
			if ($EventParameters['maxsize'] <= 800)
			{
				$targetThumbnail = $this->_selectedImage->FindMaxSizeThumbnail($EventParameters['maxsize']);
			}
		}


		//If we found a thumbnail, output it.  Otherwise 404
        if (is_set($targetThumbnail))
		{
			//Set the current file version to the active version of the thumbnail
			$this->_selectedVersion = $targetThumbnail->File->CurrentVersion;

			//Now call the normal file processor to output the file
			$this->FILE_Processor($EventParameters);
		}
		else
		{
			//Switch over to HTM file type
			$EventParameters['filetype'] = "htm";
			$this->SetResponseCode(404, $EventParameters);
		}

	}

	static public function DisplayJPEG($FileSpec)
	{
		if (is_file($FileSpec))
		{
			//Get the image data
			$imageData = imagecreatefromjpeg($FileSpec);

			//Send the correct header
			header("Content-type: image/jpeg;");

			//Send the image
			imagejpeg($imageData, "", 80);
		}

	}

	static public function DisplayGIF($FileSpec)
	{
		if (is_file($FileSpec))
		{
			//Get the image data
			$imageData = imagecreatefromgif($FileSpec);

			//Send the correct header
			header("Content-type: image/gif;");

			//Send the image
			imagegif($imageData);
		}

	}

    static public function DisplayPNG($FileSpec)
	{
		if (is_file($FileSpec))
		{
			//Get the image data
			$imageData = imagecreatefrompng($FileSpec);

			//Send the correct header
			header("Content-type: image/png;");

			//Send the image
			imagepng($imageData);
		}

	}
}

?>