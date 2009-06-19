<?php
/*
Phones Collective Class

@package Sandstone
@subpackage Phones
*/

class Phones extends CollectiveBase
{

	protected $_phonesByType;

	public function __construct($Name = null, $ParentEntity = null, $AssociatedEntityType = null)
	{
		$this->_phonesByType = new DIarray();

		parent::__construct($Name, $ParentEntity, $AssociatedEntityType);

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

			$query = new Query();

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Phone::GenerateBaseSelectClause();
			$selectClause .= ",	b.PhoneTypeID ";

			$fromClause = Phone::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityPhone b ON b.PhoneID = a.PhoneID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$this->_associatedEntityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_elements, "Phone", "PhoneID", $this, "LoadCallback");

			$returnValue = true;

			$this->_isLoaded = true;

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadCallback($Phone)
	{
		$this->_phonesByType[$Phone->PhoneType->PhoneTypeID] = $Phone;
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
			$query = new Query();

			$associatedEntityID = $this->_parentEntity->PrimaryID;

			$query->SQL = "	INSERT INTO core_EntityPhone
							(
								AssociatedEntityType,
								AssociatedEntityID,
								PhoneID,
								PhoneTypeID
							)
							VALUES
							(
								{$query->SetTextField($this->_associatedEntityType)},
								{$associatedEntityID},
								{$NewElement->PhoneID},
								{$NewElement->PhoneType->PhoneTypeID}
							)";

			$query->Execute();

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

		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityPhone
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		PhoneID = {$OldElement->PhoneID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityPhone
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}";

		$query->Execute();

		$this->_phonesByType->Clear();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	UPDATE core_EntityPhone SET
							PhoneTypeID = {$CurrentElement->PhoneType->PhoneTypeID}
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		PhoneID = {$CurrentElement->PhoneID} ";

		$query->Execute();

		return true;

	}

}
?>
