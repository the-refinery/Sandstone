<?php

NameSpace::Using("Sandstone.Application");
NameSpace::Using("Sandstone.File");
NameSpace::Using("Sandstone.AWS");

class FilePage extends BasePage
{

	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();

	protected $_selectedFile;
	protected $_selectedVersion;

	protected function Generic_PreProcessor(&$EventParameters)
	{

		$this->_selectedFile = new File($EventParameters['fileid']);

		//Did we get a valid ID?
		if ($this->_selectedFile->IsLoaded == false)
		{
			//Switch over to HTM file type
			$EventParameters['filetype'] = "htm";
			$this->SetResponseCode(404, $EventParameters);
		}
		else
		{
			$requestedFileName = strtolower("{$EventParameters['filename']}.{$EventParameters['filetype']}");

			//Does the filename match?  (case insensitive)
			if ($requestedFileName != strtolower($this->_selectedFile->FileName))
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
					if (is_set($this->_selectedFile->Versions[$EventParameters['fileversion']]) == false)
					{
						//That version number doesn't exist

						//Switch over to HTM file type
						$EventParameters['filetype'] = "htm";
						$this->SetResponseCode(404, $EventParameters);
					}
					else
					{
						//We are good to go with the selected version
						$this->_selectedVersion = $this->_selectedFile->Versions[$EventParameters['fileversion']];

						//everything goes through the "file" filetype processing
						$EventParameters['filetype'] = "file";
					}
				}
				else
				{
					//We are good to go with the current version
					$this->_selectedVersion = $this->_selectedFile->CurrentVersion;

					//everything goes through the "file" filetype processing
					$EventParameters['filetype'] = "file";
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
			switch ($this->_selectedFile->FileType)
			{
				case "image/jpeg":
					ImagePage::DisplayJPEG($this->_selectedVersion->FileSpec);
					break;

				case "image/gif":
					ImagePage::DisplayGIF($this->_selectedVersion->FileSpec);
					break;

				case "image/png":
					ImagePage::DisplayPNG($this->_selectedVersion->FileSpec);
					break;

				default:
			        //Pull the file's contents into memory
			        $fileSize = filesize($this->_selectedVersion->FileSpec);

					$fh = fopen($this->_selectedVersion->FileSpec, 'rb' );
					$fileContents = fread( $fh, $fileSize);
					fclose( $fh );

					//Pass the correct headers
					if (strlen($this->_selectedFile->FileType) > 0)
					{
						$header = "Content-Type: {$this->_selectedFile->FileType}";
					}
					else
					{
						$header= "Content-Disposition: attachment; filename=\"{$this->_selectedFile->FileName}\"";
					}

					header($header);

					//Now echo the file contents
					echo $fileContents;

				break;
			}
		}
	}
}

?>