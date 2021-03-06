<?php
/*
ImageThumbnail Class File

@package Sandstone
@subpackage Image
 */

class ImageThumbnail extends EntityBase
{
	protected function SetupProperties()
	{

		//AddProperty Parameters:
		// 1) Name
		// 2) DataType
		// 3) DBfieldName
		// 4) IsReadOnly
		// 5) IsRequired
		// 6) IsPrimaryID
		// 7) IsLoadedRequired
		// 8) IsLoadOnDemand
		// 9) LoadOnDemandFunctionName

		$this->AddProperty("ThumbnailID","integer","ThumbnailID",true,false,true,false,false,null);
		$this->AddProperty("Image","Image","ImageID",false,false,false,true,false,null);
		$this->AddProperty("Height","integer","Height",false,true,false,false,false,null);
		$this->AddProperty("Width","integer","Width",false,true,false,false,false,null);
		$this->AddProperty("File","File","FileID",false,true,false,true,false,null);

		parent::SetupProperties();
	}

	protected function SaveNewRecord()
	{
		$query = new Query();

		$query->SQL = "	INSERT INTO core_ThumbnailMaster
						(
							AccountID,
							ImageID,
							Height,
							Width,
							FileID
						)
						VALUES
						(
							{$this->AccountID},
							{$this->_image->ImageID},
							{$this->_height},
							{$this->_width},
							{$this->_file->FileID}
						)";

		$query->Execute();

		$this->GetNewPrimaryID();

		return true;
	}

	protected function SaveUpdateRecord()
	{
		$query = new Query();

		$query->SQL = "	UPDATE core_ThumbnailMaster SET
								Height = {$this->_height},
								Width = {$this->_width},
								FileID = {$this->_file->FileID}
						WHERE ThumbnailID = {$this->_thumbnailID}";

		$query->Execute();

		return true;
	}

	public function Delete()
	{

		$query = new Query();

		//Delete the database record
		$query->SQL = "	DELETE
						FROM	core_ThumbnailMaster
						WHERE 	ThumbnailID = {$this->_thumbnailID}";

		$query->Execute();

		//Delete my file
		if (is_set($this->_file))
		{
			$this->_file->Delete();
		}

		//Clean up this object
		$this->_thumbnailID = null;
		$this->_image = null;
		$this->_file = null;
	}

	public function CalculateSizeMatch($Height = null, $Width = null)
	{

		//Check height
		if (is_set($Height))
		{
			if ($this->_height == $Height)
			{
				$isHeightOK = true;
			}
			else
			{
				$isHeightOK = false;
			}
		}
		else
		{
			$isHeightOK = true;
		}

		//Check width
		if (is_set($Width))
		{
			if ($this->_width == $Width)
			{
				$isWidthOK = true;
			}
			else
			{
				$isWidthOK = false;
			}
		}
		else
		{
			$isWidthOK = true;
		}

		if ($isHeightOK && $isWidthOK)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	/*
	Static Query Functions
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.ThumbnailID,
										a.ImageID,
										a.Height,
										a.Width,
										a.FileID ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_ThumbnailMaster a ";

		return $returnValue;
	}

}
?>