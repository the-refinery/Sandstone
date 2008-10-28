<?php
/**
 * Images Collective Class
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

class Images extends Module
{
	
	protected $_associatedEntityModule;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_images = array();
	protected $_primaryImage;

	public function __construct($Module = null, $Type = null, $ID = null)
	{
		if (is_set($ID) && is_numeric($ID) && is_set($Type) && is_set($Module))
		{

			$this->_associatedEntityModule = $Module;
			$this->_associatedEntityType = $Type;
			$this->_associatedEntityID = $ID;
			
			$this->Load();
		}
	}
		
	/**
	 * AssociatedEntityModule property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityModule()
	{
		return $this->_associatedEntityModule;
	}
	
	public function setAssociatedEntityModule($Value)
	{
		$this->_associatedEntityModule = trim($Value);
	}
	
	/**
	 * AssociatedEntityType property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}
	
	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = trim($Value);
	}
	
	/**
	 * AssociatedEntityID property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getAssociatedEntityID()
	{
		return $this->_associatedEntityID;
	}
	
	public function setAssociatedEntityID($Value)
	{
		if (is_numeric($Value))
		{
			$this->_associatedEntityID = $Value;
		}
	}

	/**
	 * Images property
	 * 
	 * @return array
	 */
	public function getImages()
	{
		$returnValue = $this->_images;
		
		return $returnValue;
	}
	
	/**
	 * PrimaryImage property
	 * 
	 * @return Image
	 * 
	 * @param Image $Value
	 */
	public function getPrimaryImage()
	{
		
		if (is_set($this->_primaryImage))
		{
			$returnValue = $this->_primaryImage;	
		}
		else 
		{
			//Doing it this way because we don't know the key (ID) for the
			//one image object in the array.
			foreach ($this->_images as $tempImage)
			{
				$returnValue = $tempImage;
			}				
		}
		
		return $returnValue;
	}
	
	public function setPrimaryImage($Value)
	{
		if ($Value instanceof Image)
		{
			//Turn off any other primary flags
			$this->ClearPrimaryFlags();
				
			//Now set the one we were passed as primary
			$Value->IsPrimary = true;
			
			//Assure it's been added to the array
			$this->_images[$Value->ImageID] = $Value;
			
			//Finally set it as the primary Image
			$this->_primaryImage = $this->_images[$Value->ImageID];
		}
		elseif (is_null($Value))
		{
			//Turn off any other primary flags
			$this->ClearPrimaryFlags();

			//Now clear the protected field
			$this->_primaryImage = NULL;
		}
		
	}
	
	public function Load()
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Image";
		$idField = $this->_associatedEntityType . "ID";
		
		$conn = GetConnection();
		
		$query = "	SELECT 	a.ImageID, 
							a.IsPrimary, 
							b.FileID, 
							b.AlternateText, 
							b.Width, 
							b.Height 
					FROM 	$tableName a 
							INNER JOIN core_ImageMaster b ON a.ImageID = b.ImageID 
					WHERE	a.$idField = {$this->_associatedEntityID}";

		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to 
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow()) 
			{
				
				$tempImage = new Image($dr);
	
				if ($tempImage->IsLoaded)
				{
					
					if ($tempImage->IsPrimary)
					{
						$this->_primaryImage = $tempImage;
					}
		
					$this->_images[$tempImage->ImageID] = $tempImage;
						
					$returnValue = true;
					
				}
											
			}			
			
		}
		else
		{
			$returnValue = false;
		}
		
		$this->_isLoaded = $returnValue;
		
		return $returnValue;

	}

	public function Save()
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Image";
		$idField = $this->_associatedEntityType . "ID";
		
		$conn = GetConnection();

		
		//First, clear all database entries
		$query = "DELETE FROM $tableName
					WHERE $idField = {$this->_associatedEntityID}";
		
		$conn->Execute($query);
		
		//Now loop through each of the images and add a record for each
		foreach ($this->_images as $tempImage)
		{
			
			$query = "	INSERT INTO $tableName
						(
							$idField,
							ImageID,
							IsPrimary
						)
						VALUES
						(
							{$this->_associatedEntityID},
							{$tempImage->ImageID},
							{$conn->SetBooleanField($tempImage->IsPrimary)}
						)";
			
			$conn->Execute($query);
		}
		
		$this->_isLoaded = true;
		
	}
	
	public function AddImage($newImage)
	{
		if ($newImage instanceof Image)
		{
			
			//Make sure we don't wind up with 2 images marked as primary
			if ($newImage->IsPrimary)
			{
				if (is_set($this->_primaryImage))
				{
					if ($this->_primaryImage->ImageID <> $newImage->ImageID)
					{
						$newImage->IsPrimary = false;
					}					
				}
				else
				{
					$this->_primaryImage = $newImage;
				}
			}
			else
			{
				//If this is the first image to be added, 
				//mark it as the primary by default
				if (count($this->_images) == 0)
				{
					$newImage->IsPrimary = true;
					$this->_primaryImage = $newImage;
				}				
			}
			

			//Add it to the array.
			$this->_images[$newImage->ImageID] = $newImage;
			
		}
	}
	
	public function RemoveImage($oldImage)
	{
		if ($oldImage instanceof Image)
		{
			//Clear the array element
			unset($this->_images[$oldImage->ImageID]);
			
			//Check to see if this was the primary image, if so - clear it.
			if (is_set($this->_primaryImage))
			{
				if ($this->_primaryImage->ImageID == $oldImage->ImageID)
				{
					$this->_primaryImage = null;
				}
			}
		}
	}
	
	protected function ClearPrimaryFlags()
	{
		//Loop through the array, turning off all the IsPrimary flags
		foreach ($this->_images as $tempImage)
		{
			$tempImage->IsPrimary = false;
		}
		
	}
	
	public function Export()
	{	

		foreach($this->_images as $tempImage)
		{
			$this->_exportEntities[] = $tempImage->Export();
		}
		
		return parent::Export();
	}	
	
}

?>