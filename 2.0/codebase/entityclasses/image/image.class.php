<?php
/**
 * Image Class File
 * @package Sandstone
 * @subpackage Image
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

NameSpace::Using("Sandstone.ADOdb");
NameSpace::Using("Sandstone.File");

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
		$this->AddProperty("Thumbnails","array",null,true,false,false,false,false,null);

		parent::SetupProperties();
	}

	public function Load($dr)
	{
		$returnValue = parent::Load($dr);

		if ($returnValue == true)
		{
			$returnValue = $this->LoadThumbnails();
		}

		return $returnValue;
	}

	protected function LoadThumbnails()
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
								FileID,
								AlternateText,
								Width,
								Height,
								Description
							)
							VALUES
							(
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

	public function Delete($conn = null)
	{
		if (is_set($conn) == false)
		{
			$conn = GetConnection();
		}

		//Delete my file
		if (is_set($this->_file))
		{
			$this->_file->Delete($conn);
		}

		//Clear Thumbnails
		$this->ClearThumbnails($conn);

		//Delete the image record
		$query = "	DELETE
					FROM	core_ImageMaster
					WHERE 	ImageID = {$this->_imageID}";

		$conn->Execute($query);


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
			if (count($this->_thumbnails) > 0)
			{
				foreach($this->_thumbnails as $tempThumbnail)
				{

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
			}

		}
		else
		{
			//Neither parameter passed, just return null
			$returnValue = null;
		}

		return $returnValue;
	}

	public function ClearThumbnails($conn = null)
	{
		$conn = GetConnection();

		if (count($this->_thumbnails) > 0)
		{
			//Delete each thumbnail
			foreach($this->_thumbnails as $tempThumbnail)
			{
				$tempThumbnail->Delete($conn);
			}

			//Clear the Array
			$this->_thumbnails = Array();
		}

	}

	public function Export()
	{

		$this->_exportEntities[] = $this->CreateXMLentity("filename", $this->_file->PhysicalFileName);
		$this->_exportEntities[] = $this->CreateXMLentity("isprimary", $this->_isPrimary, true);

		return parent::Export();
	}

	/**
	 *
	 * Static Query Functions
	 *
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