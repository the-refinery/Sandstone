<?php
/*
Images Collective Class

@package Sandstone
@subpackage Image
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class Images extends CollectiveBase
{

	protected $_primaryImage;

	public function __construct($Name = null, $ParentEntity = null)
	{
		parent::__construct($Name, $ParentEntity);

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

			$conn = GetConnection();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Image::GenerateBaseSelectClause();
			$selectClause .= ", b.IsPrimary ";

			$fromClause = Image::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityImage b ON b.ImageID = a.ImageID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempImage = new Image($dr);
					$this->_elements[$tempImage->ImageID] = $tempImage;

					if ($tempImage->IsPrimary)
					{
						$this->_primaryImage = $tempImage;
					}

				}

				$returnValue = true;

				$this->_isLoaded = true;

			}
			else
			{
				$returnValue = false;
			}

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
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
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	INSERT INTO core_EntityImage
					(
						AssociatedEntityType,
						AssociatedEntityID,
						ImageID,
						IsPrimary
					)
					VALUES
					(
						{$conn->SetTextField($associatedEntityType)},
						{$associatedEntityID},
						{$NewElement->ImageID},
						{$conn->SetBooleanField($NewElement->IsPrimary)}
					)";

		$conn->Execute($query);

		$returnValue = true;

		return $returnValue;
	}

	protected function ProcessOldElement($OldElement)
	{

		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityImage
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		ImageID = {$OldElement->ImageID}";

		$conn->Execute($query);

		return true;

	}

	protected function ProcessClearElements()
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityImage
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}";

		$conn->Execute($query);

		$this->_primaryEmail = null;
		$this->_emailsByType->Clear();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	UPDATE core_EntityImage SET
						IsPrimary = {$conn->SetBooleanField($CurrentElement->IsPrimary)}
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		ImageID = {$CurrentElement->ImageID} ";

		$conn->Execute($query);

		return true;

	}

}
?>