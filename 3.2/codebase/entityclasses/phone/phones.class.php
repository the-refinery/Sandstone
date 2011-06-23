<?php
/*
Phones Collective Class

@package Sandstone
@subpackage Phones
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class Phones extends CollectiveBase
{

	protected $_phonesByType;

	public function __construct($Name = null, $ParentEntity = null)
	{
		$this->_phonesByType = new DIarray();

		parent::__construct($Name, $ParentEntity);

		$this->_elementType = "Phone";

		$this->_registeredProperties[] = "PhonesByType";
	}

	/*
	PhonesByType property

	@return DIarray
	 */
	public function getPhonesByType()
	{
		return $this->_phonesByType;
	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();
			$this->_phonesByType->Clear();

			$conn = GetConnection();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Phone::GenerateBaseSelectClause();
			$selectClause .= ",	b.PhoneTypeID ";

			$fromClause = Phone::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityPhone b ON b.PhoneID = a.PhoneID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempPhone = new Phone($dr);
					$this->_elements[$tempPhone->PhoneID] = $tempPhone;

					$this->_phonesByType[$tempPhone->PhoneType->PhoneTypeID] = $tempPhone;
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

		if (is_set($NewElement->PhoneType))
		{
			//First, check to see if we already have an phone for this type.
			if (array_key_exists($NewElement->PhoneType->PhoneTypeID, $this->_phonesByType))
			{
				$currentPhone = $this->_phonesByType[$NewElement->PhoneType->PhoneTypeID];

				$this->ProcessOldElement($currentPhone);
			}

			//Now add the new Phone
			$conn = GetConnection();

			$associatedEntityType = get_class($this->_parentEntity);
			$associatedEntityID = $this->_parentEntity->PrimaryID;

			$query = "	INSERT INTO core_EntityPhone
						(
							AssociatedEntityType,
							AssociatedEntityID,
							PhoneID,
							PhoneTypeID
						)
						VALUES
						(
							{$conn->SetTextField($associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->PhoneID},
							{$NewElement->PhoneType->PhoneTypeID}
						)";

			$conn->Execute($query);

			$returnValue = true;

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function ProcessOldElement($OldElement)
	{

		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityPhone
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		PhoneID = {$OldElement->PhoneID}";

		$conn->Execute($query);

		return true;

	}

	protected function ProcessClearElements()
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityPhone
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}";

		$conn->Execute($query);

		$this->_phonesByType->Clear();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	UPDATE core_EntityPhone SET
						PhoneTypeID = {$CurrentElement->PhoneType->PhoneTypeID}
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		PhoneID = {$CurrentElement->PhoneID} ";

		$conn->Execute($query);

		return true;

	}

}
?>