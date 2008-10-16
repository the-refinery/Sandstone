<?php
/*
Image Class File
@package Sandstone
@subpackage Image
*/

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.File");
NameSpace::Using("Sandstone.AWS");

class Image extends EntityBase
{
	protected function SetupProperties()
	{
		$this->AddProperty("ImageID","integer","ImageID",true,false,true,false,false,null);
		$this->AddProperty("File","File","FileID",false,true,false,true,false,null);
		$this->AddProperty("AlternateText","string","AlternateText",false,false,false,false,false,null);
		$this->AddProperty("Width","integer","Width",false,false,false,false,false,null);
		$this->AddProperty("Height","integer","Height",false,false,false,false,false,null);
		$this->AddProperty("IsPrimary","boolean","IsPrimary",false,false,false,false,false,null);
		$this->AddProperty("Description","string","Description",false,false,false,false,false,null);
		$this->AddProperty("Thumbnails","array",null,true,false,false,false,true,"LoadThumbnails");

		parent::SetupProperties();
	}

	public function LoadByFileID($FileID)
	{

		$conn = GetConnection();

		$selectClause = self::GenerateBaseSelectClause();

		$fromClause = self::GenerateBaseFromClause();

		$whereClause = "WHERE a.FileID = {$FileID} ";

		$query = $selectClause . $fromClause . $whereClause;

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();

			$returnValue = $this->Load($dr);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadThumbnails()
	{

		if ($this->IsLoaded)
		{
			$conn = GetConnection();

			$this->_thumbnails->Clear();

			$query = "	SELECT	ThumbnailID,
								ImageID,
								Height,
								Width,
								FileID
						FROM	core_ThumbnailMaster
						WHERE	ImageID = {$this->_imageID}";

			$ds = $conn->Execute($query);

			if ($ds && $ds->RecordCount() > 0)
			{
				//Set the return value to failure, then set it to true as soon as we are able to
				//successfully load one.
				$returnValue = false;

				while ($dr = $ds->FetchRow())
				{

					$tempThumbnail = new ImageThumbnail($dr);

					if ($tempThumbnail->IsLoaded)
					{
						$tempThumbnail->Image = $this;
						$this->_thumbnails[$tempThumbnail->ThumbnailID] = $tempThumbnail;

						$returnValue = true;
					}

				}

			}
			else
			{
				//No thumbnails isn't an issue.
				$returnValue = true;
			}
		}
		else
		{
			$returnValue = false;
		}


		return $returnValue;
	}

	public function Save()
	{

		$returnValue = parent::Save();

		if ($returnValue == true)
		{
			$returnValue = $this->SaveThumbnails();
		}

		return $returnValue;
	}

	protected function SaveNewRecord()
	{
		$conn = GetConnection();

		$query = "	INSERT INTO core_ImageMaster
							(
								AccountID,
								FileID,
								AlternateText,
								Width,
								Height,
								Description
							)
							VALUES
							(
								{$this->AccountID},
								{$this->_file->FileID},
								{$conn->SetNullTextField($this->_alternateText)},
								{$conn->SetNullNumericField($this->_width)},
								{$conn->SetNullNumericField($this->_height)},
								{$conn->SetNullTextField($this->_description)}
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

		$query = "	UPDATE core_ImageMaster SET
								FileID = {$this->_file->FileID},
								AlternateText = {$conn->SetNullTextField($this->_alternateText)},
								Width = {$conn->SetNullNumericField($this->_width)},
								Height = {$conn->SetNullNumericField($this->_height)},
								Description = {$conn->SetNullTextField($this->_description)}
							WHERE ImageID = {$this->_imageID}";

		$conn->Execute($query);

		return true;
	}

	protected function SaveThumbnails()
	{
		if (count($this->_thumbnails) > 0)
		{
			foreach($this->_thumbnails as $tempThumbnail)
			{
				$tempThumbnail->Save();
			}
		}

		return true;
	}

	public function Delete()
	{
		$conn = GetConnection();

		//Delete the image record
		$query = "	DELETE
					FROM	core_ImageMaster
					WHERE 	ImageID = {$this->_imageID}";

		$conn->Execute($query);

		//Delete my file
		if (is_set($this->_file))
		{
			$this->_file->Delete();
		}

		//Clear Thumbnails
		$this->ClearThumbnails();

	}

	public function AddThumbnail($Thumbnail)
	{
		if ($Thumbnail instanceof ImageThumbnail)
		{
			//Set it's Image ID and save it
			$Thumbnail->ImageID = $this->_imageID;
			$Thumbnail->Save();


			//Now add it to the array
			$this->_thumbnails[$Thumbnail->ThumbnailID] = $Thumbnail;
		}
	}

	public function FindThumbnail($Height = null, $Width = null)
	{

		//Make sure at least one parameter is passed
		if (is_set($Height) || is_set($Width))
		{

			//Search the array, and find a thumbnail that matches the
			//given height and width.  If only one dimension is given,
			//return the largest matching thumbnail
			if (count($this->Thumbnails) > 0)
			{
				$returnValue= $this->FindThumbnailMatch($Height, $Width);
			}

			if (is_set($returnValue) == false)
			{
				//We didn't find a thumbnail of this size, so generate one
				$returnValue = $this->GenerateThumbnail($Height, $Width);
			}

		}
		else
		{
			//Neither parameter passed, just return null
			$returnValue = null;
		}

		return $returnValue;
	}

	public function FindMaxSizeThumbnail($MaxSize)
	{

		//Do I scale from width or height?
		if ($this->_width >= $this->_height)
		{
			//Scale by Width
			$returnValue = $this->FindThumbnail(null, $MaxSize);
		}
		else
		{
			//Scale by Height
			$targetThumbnail = $this->FindThumbnail($MaxSize, null);
		}

		return $returnValue;
	}

	protected function FindThumbnailMatch($Height, $Width)
	{

		//Loop across our thumbnails and find the biggest matching thumbnail
		foreach($this->Thumbnails as $tempThumbnail)
		{
			//Does it match our requested size?
			$isMatch = $tempThumbnail->CalculateSizeMatch($Height, $Width);

			if ($isMatch)
			{
				if (is_set($returnValue))
				{
					//We already have a found thumbnail, is this one bigger?
					if (($tempThumbnail->Height * $tempThumbnail->Width) > ($returnValue->Height * $returnValue->Width))
					{
						$returnValue = $tempThumbnail;
					}

				}
				else
				{
					$returnValue = $tempThumbnail;
				}
			}
		}

		return $returnValue;
	}

	protected function GenerateThumbnail($Height, $Width)
	{
		//First, if only one dimension is passed, calculate the other
		if ($Height > 0 && (is_set($Width) == false || $Width == 0))
		{
			// Only Height was specified, scale Width
			$Width = round(($Height / $this->_height) * $this->_width);
		}
		elseif ($Width > 0 && (is_set($Height) == false || $Height == 0))
		{
			// Only Width was specified, scale Height
			$Height = round(($Width / $this->_width) * $this->_height);
		}

		//Create new Image (actual file data), and fill with white
		$newThumbnailFileData = imagecreatetruecolor($Width, $Height);
		$white = imagecolorallocate($newThumbnailFileData, 255, 255, 255);
		imagefill($newThumbnailFileData,0,0,$white);

		//Load the contents of the original file
		$originalImageFileData = $this->LoadOriginalImageData();

		// Resample original image into the thumbnail
		imagecopyresampled($newThumbnailFileData, $originalImageFileData, 0, 0, 0, 0, $Width, $Height, $this->_width, $this->_height);

		//Save the Thumbnail file and create the data records
		$returnValue = $this->SaveNewThumbnailFile($newThumbnailFileData, $Height, $Width);

		return $returnValue;
	}

	protected function LoadOriginalImageData()
	{

        if (Application::Registry()->AWSisActive)
		{

			//Get the file's contents from S3
			$s3conn = new S3;
			$bucket = Application::Registry()->AWSbucket;
			$objectKey = $this->_file->CurrentVersion->FileSpec;

			if (Application::Registry()->IsAWSaccountBased)
			{
				$objectKey = Application::License()->AccountID . "/" . $objectKey;
			}

			$fileContents = $s3conn->getObject($objectKey, $bucket);

			//Convert the contents to an image resource.
			$returnValue = imagecreatefromstring($fileContents);
		}
		else
		{
			$targetFileSpec = $this->_file->CurrentVersion->FileSpec;

			switch ($this->_file->FileType)
			{
				case "image/gif":
					$returnValue = imagecreatefromgif($targetFileSpec);
					break;

				case "image/jpeg":
				case "image/pjpeg":
					$returnValue = imagecreatefromjpeg($targetFileSpec);
					break;

				case "image/png":
					$returnValue = imagecreatefrompng($targetFileSpec);
					break;
			}

		}

		return $returnValue;
	}

	protected function SaveNewThumbnailFile($FileData, $Height, $Width)
	{

		//Create the filename
		$filename = "thumb_" . $Width . "-" . $Height . "-IMG" . $this->_imageID;

		//determine the file extension
		switch ($this->_file->FileType)
		{
			case "image/gif":
				$filename .= ".gif";
				break;

			case "image/jpeg":
			case "image/pjpeg":
				$filename .= ".jpg";
				break;

			case "image/png":
				$filename .= ".png";
				break;
		}

		if (Application::Registry()->AWSisActive)
		{
			//If AWS is active, generate the file in the temp location.
			$fileSpec = Application::Registry()->AWSlocalUploadPath . $filename;
		}
		else
		{
			//Otherwise, put it in our standard images location
			$fileSpec = Application::Registry()->ImagesPath . $filename;
		}

		//if a physical file of this name exists, delete it.
		if (file_exists($fileSpec))
		{
			unlink($fileSpec);
		}

		//Write the file contents to disk
		switch ($this->_file->FileType)
		{
			case "image/gif":
				imagetruecolortopalette($FileData, 0, 256);
				imagegif($FileData, $fileSpec);
				break;

			case "image/jpeg":
			case "image/pjpeg":
				imagejpeg($FileData, $fileSpec, 80);
				break;

			case "image/png":
				imagepng($FileData, $fileSpec);
				break;
		}

		//Capture the file size of the new thumbnail.
		$thumbnailFileSize = filesize($fileSpec);

		//If AWS is active, upload the file to AWS
		if (Application::Registry()->AWSisActive)
		{
			$this->UploadThumbnailToAWS($filename, $fileSpec, $thumbnailFileSize);

			//Set the filespec to just the filename for the file version reference
			$fileSpec = $filename;
		}

		//Create a file record for this new thumbnail image
		$newThumbnailFile = new File();
		$newThumbnailFile->FileName = $filename;
		$newThumbnailFile->PhysicalFileName = $filename;
		$newThumbnailFile->FileType = $this->_file->FileType;
		$newThumbnailFile->Description = "Image Thumbnail";
		$newThumbnailFile->Save();

		//Add a new file version
		$newThumbnailFile->AddVersion($fileSpec, $thumbnailFileSize);

		//Now create a new ImageThumbnail
		$newThumbnail = new ImageThumbnail();
		$newThumbnail->Image = $this;
		$newThumbnail->File = $newThumbnailFile;
		$newThumbnail->Height = $Height;
		$newThumbnail->Width = $Width;
		$newThumbnail->Save();

		//Reload our thumbnails array
		$this->LoadThumbnails();

		$returnValue = $this->_thumbnails[$newThumbnail->ThumbnailID];

		return $returnValue;

	}

	protected function UploadThumbnailToAWS($FileName, $FileSpec, $FileSize)
	{

		//Get the thumbnail's file contents
		$fh = fopen($FileSpec, 'rb' );
		$fileContents = fread( $fh, $FileSize);
		fclose( $fh );

		//Write the object to S3
		$s3svc = new S3();
		$s3svc->putObject($FileName, $fileContents, Application::Registry()->AWSbucket, 'private', $this->_file->FileType, array("data-file-name"=>$this->_file->FileName));

		//Remove the local file
		unlink($FileSpec);
	}

	public function ClearThumbnails()
	{
		if (count($this->Thumbnails) > 0)
		{
			//Delete each thumbnail
			foreach($this->_thumbnails as $tempThumbnail)
			{
				$tempThumbnail->Delete();
			}

			//Clear the Array
			$this->_thumbnails->Clear();
		}

	}

	public function Export()
	{

		$this->_exportEntities[] = $this->CreateXMLentity("filename", $this->_file->PhysicalFileName);
		$this->_exportEntities[] = $this->CreateXMLentity("isprimary", $this->_isPrimary, true);

		return parent::Export();
	}

	/*
	Static Query Functions
	*/
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ImageID,
										a.FileID,
										a.AlternateText,
										a.Width,
										a.Height,
										a.Description ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ImageMaster a ";

		return $returnValue;
	}

}
?>