<?php
/*
Images Collective Class

@package Sandstone
@subpackage Image
*/

class Images extends CollectiveBase
{

	protected $_primaryImage;

	public function __construct($Name = null, $ParentEntity = null, $AssociatedEntityType = null)
	{
		parent::__construct($Name, $ParentEntity, $AssociatedEntityType);

		$this->_elementType = "Image";

		$this->_registeredProperties[] = "PrimaryImage";

	}

	/*
	PrimaryEmail property

	@return email
	@param email $Value
	 */
	public function getPrimaryImage()
	{

		if ($this->_isLoaded == false)
		{
			$this->Load();
		}

		return $this->_primaryImage;
	}

	public function setPrimaryImage($Value)
	{

		//Make sure we got a loaded image object
		if ($Value instanceof $this->_elementType && $Value->IsLoaded)
		{

			//Make sure this object is in our array
			if (array_key_exists($Value->ImageID, $this->_elements))
			{
				//Was there a primary image before?
				if (is_set($this->_primaryImage))
				{
					//Remove the IsPrimary value from the old primary image
					$this->_primaryImage->IsPrimary = false;
					$this->ProcessSaveElement($this->_primaryImage);
				}

				//Add the IsPrimary value to the new primary image
				$newPrimaryImage = $this->_elements[$Value->ImageID];

				$newPrimaryImage->IsPrimary = true;

				$this->ProcessSaveElement($newPrimaryImage);

				//Set the protected field.
				$this->_primaryImage = $newPrimaryImage;
			}
		}

	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();

			$query = new Query();

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Image::GenerateBaseSelectClause();
			$selectClause .= ", b.IsPrimary ";

			$fromClause = Image::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityImage b ON b.ImageID = a.ImageID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$this->_associatedEntityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_elements, "Image", "ImageID", $this, "LoadCallback");

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadCallback($Image)
	{
		if ($Image->IsPrimary)
		{
			$this->_primaryImage = $Image;
		}
	}

	protected function ProcessNewElement($NewElement)
	{

		//If this new image is set to primary, clear the primary flag
		//of any existing primary image
		if ($NewElement->IsPrimary && is_set($this->_primaryImage))
		{
			$this->_primaryImage->IsPrimary = false;

			$this->ProcessSaveElement($this->_primaryImage);
		}

		//Now add the new image
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	INSERT INTO core_EntityImage
						(
							AssociatedEntityType,
							AssociatedEntityID,
							ImageID,
							IsPrimary
						)
						VALUES
						(
							{$query->SetTextField($this->_associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->ImageID},
							{$query->SetBooleanField($NewElement->IsPrimary)}
						)";

		$query->Execute();

		return true;
	}

	protected function ProcessOldElement($OldElement)
	{

		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityImage
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		ImageID = {$OldElement->ImageID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityImage
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}";

		$query->Execute();

		$this->_primaryEmail = null;
		$this->_emailsByType->Clear();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	UPDATE core_EntityImage SET
							IsPrimary = {$query->SetBooleanField($CurrentElement->IsPrimary)}
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		ImageID = {$CurrentElement->ImageID} ";

		$query->Execute();

		return true;

	}

}
?>
