<?php
/**
 * ImageThumbnail Class
 * 
 * @package Sandstone
 * @subpackage Image
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class ImageThumbnail extends Module 
{

	protected $_thumbnailID;
	protected $_imageID;
	
	protected $_height;
	protected $_width;
	protected $_file;
	
	public function __construct($ID = null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
			{
				$this->Load($ID);
			}
			else 
			{
				$this->LoadByID($ID);
			}
		}
	}
	
	/**
	 * ThumbnailID property
	 * 
	 * @return integer
	 */
	public function getThumbnailID()
	{
		return $this->_thumbnailID;
	}

	/**
	 * ImageID property
	 * 
	 * @return integer
	 * 
	 * @param integer $Value
	 */
	public function getImageID()
	{
		return $this->_imageID;
	}

	public function setImageID($Value)
	{
		$this->_imageID = $Value;
	}

	/**
	 * Height property
	 * 
	 * @return integer
	 * 
	 * @param integer $Value
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	public function setHeight($Value)
	{
		$this->_height = $Value;
	}

	/**
	 * Width property
	 * 
	 * @return integer
	 * 
	 * @param integer $Value
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function setWidth($Value)
	{
		$this->_width = $Value;
	}

	/**
	 * File property
	 * 
	 * @return File
	 * 
	 * @param File $Value
	 */
	public function getFile()
	{
		return $this->_file;
	}

	public function setFile($Value)
	{
		if ($Value instanceof File && $Value->IsLoaded)
		{
			$this->_file = $Value;	
		}
	}

	public function Load($dr)
	{
		
		$this->_thumbnailID = $dr['ThumbnailID'];
		$this->_imageID = $dr['ImageID'];
		
		$this->_height = $dr['Height'];
		$this->_width = $dr['Width'];
		$this->_file = new File($dr['FileID']);
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		
		$conn = GetConnection();
		
		$query = "	SELECT	ThumbnailID,
							ImageID,
							Height,
							Width,
							FileID
					FROM	core_ThumbnailMaster
					WHERE	ThumbnailID = {$ID}";
		
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
	
	public function Save($conn = null)
	{
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();
		}
		
		if (is_set($this->_thumbnailID) OR $this->_thumbnailID > 0)
		{
			$this->SaveUpdateRecord($conn);
		}
		else
		{
			$this->SaveNewRecord($conn);
		}
		
		$this->_isLoaded = true;
	}

	protected function SaveNewRecord($conn)
	{
				
		$query = "	INSERT INTO core_ThumbnailMaster
					(
						ImageID,
						Height,
						Width,
						FileID
					)
					VALUES
					(
						{$this->_imageID},
						{$this->_width},
						{$this->_height},
						{$this->_file->FileID}
					)";
		
		$conn->Execute($query);
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_thumbnailID = $dr['newID'];
		
	}
	
	protected function SaveUpdateRecord($conn)
	{
		
		$query = "	UPDATE core_ThumbnailMaster SET
						ImageID = {$this->_imageID},
						Width = {$this->_width},
						Height = {$this->_height},
						FileID = {$this->_file->FileID}
					WHERE ThumbnailID = {$this->_thumbnailID}";
		
		$conn->Execute($query);
		
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
		
		//Delete the database record
		$query = "	DELETE
					FROM	core_ThumbnailMaster
					WHERE 	ThumbnailID = {$this->_thumbnailID}";
		
		$conn->Execute($query);
		
		//Clean up this object
		$this->_thumbnailID = null;
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
	
}

?>