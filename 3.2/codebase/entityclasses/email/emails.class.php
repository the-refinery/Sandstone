<?php
/*
Emails Collective Class

@package Sandstone
@subpackage Email
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

class Emails extends CollectiveBase
{

	protected $_emailsByType;
	protected $_primaryEmail;

	public function __construct($Name = null, $ParentEntity = null)
	{
		$this->_emailsByType = new DIarray();

		parent::__construct($Name, $ParentEntity);

		$this->_elementType = "Email";

		$this->_registeredProperties[] = "EmailsByType";
		$this->_registeredProperties[] = "PrimaryEmail";

	}

	/*
	EmailsByType property

	@return DIarray
	 */
	public function getEmailsByType()
	{
		return $this->_emailsByType;
	}

	/*
	PrimaryEmail property

	@return email
	@param email $Value
	 */
	public function getPrimaryEmail()
	{

		if ($this->_isLoaded == false)
		{
			$this->Load();
		}

		return $this->_primaryEmail;
	}

	public function setPrimaryEmail($Value)
	{

		//Make sure we got a loaded email object
		if ($Value instanceof $this->_elementType && $Value->IsLoaded)
		{

			//Make sure this object is in our array
			if (array_key_exists($Value->EmailID, $this->_elements))
			{
				//Was there a primary email before?
				if (is_set($this->_primaryEmail))
				{
					//Remove the IsPrimary value from the old primary email
					$this->_primaryEmail->IsPrimary = false;
					$this->ProcessSaveElement($this->_primaryEmail);
				}

				//Add the IsPrimary value to the new primary email
				$newPrimaryEmail = $this->_elements[$Value->EmailID];

				$newPrimaryEmail->IsPrimary = true;

				$this->ProcessSaveElement($newPrimaryEmail);

				//Set the protected field.
				$this->_primaryEmail = $newPrimaryEmail;
			}
		}

	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();
			$this->_emailsByType->Clear();

			$conn = GetConnection();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Email::GenerateBaseSelectClause();
			$selectClause .= ",	b.EmailTypeID,
								b.IsPrimary ";

			$fromClause = Email::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityEmail b ON b.EmailID = a.EmailID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempEmail = new Email($dr);
					$this->_elements[$tempEmail->EmailID] = $tempEmail;

					$this->_emailsByType[$tempEmail->EmailType->EmailTypeID] = $tempEmail;

					if ($tempEmail->IsPrimary)
					{
						$this->_primaryEmail = $tempEmail;
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

		if (is_set($NewElement->EmailType))
		{
			//First, check to see if we already have an email for this type.
			if (array_key_exists($NewElement->EmailType->EmailTypeID, $this->_emailsByType))
			{
				$currentEmail = $this->_emailsByType[$NewElement->EmailType->EmailTypeID];

				$this->ProcessOldElement($currentEmail);
			}

			//Next, if this new email is set to primary, clear the primary flag
			//of any existing primary email
			if ($NewElement->IsPrimary && is_set($this->_primaryEmail))
			{
				$this->_primaryEmail->IsPrimary = false;

				$this->ProcessSaveElement($this->_primaryEmail);
			}

			//Now add the new email
			$conn = GetConnection();

			$associatedEntityType = get_class($this->_parentEntity);
			$associatedEntityID = $this->_parentEntity->PrimaryID;

			$query = "	INSERT INTO core_EntityEmail
						(
							AssociatedEntityType,
							AssociatedEntityID,
							EmailID,
							EmailTypeID,
							IsPrimary
						)
						VALUES
						(
							{$conn->SetTextField($associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->EmailID},
							{$NewElement->EmailType->EmailTypeID},
							{$conn->SetBooleanField($NewElement->IsPrimary)}
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
					FROM	core_EntityEmail
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		EmailID = {$OldElement->EmailID}";

		$conn->Execute($query);

		return true;

	}

	protected function ProcessClearElements()
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityEmail
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

		$query = "	UPDATE core_EntityEmail SET
						EmailTypeID = {$CurrentElement->EmailType->EmailTypeID},
						IsPrimary = {$conn->SetBooleanField($CurrentElement->IsPrimary)}
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		EmailID = {$CurrentElement->EmailID} ";

		$conn->Execute($query);

		return true;

	}

}
?>