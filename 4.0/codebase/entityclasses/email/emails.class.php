<?php
/*
Emails Collective Class

@package Sandstone
@subpackage Email
*/

class Emails extends CollectiveBase
{

	protected $_emailsByType;
	protected $_primaryEmail;

	public function __construct($Name = null, $ParentEntity = null, $AssociatedEntityType = null)
	{
		$this->_emailsByType = new DIarray();

		parent::__construct($Name, $ParentEntity, $AssociatedEntityType);

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

			$query = new Query();

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Email::GenerateBaseSelectClause();
			$selectClause .= ",	b.EmailTypeID,
								b.IsPrimary ";

			$fromClause = Email::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityEmail b ON b.EmailID = a.EmailID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$this->_associatedEntityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_elements, "Email", "EmailID", $this, "LoadCallback");

			$returnValue = true;

			$this->_isLoaded = true;

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function LoadCallback($Email)
	{
		$this->_emailsByType[$Email->EmailType->EmailTypeID] = $Email;

		if ($Email->IsPrimary)
		{
			$this->_primaryEmail = $Email;
		}
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
			$query = new Query();

			$associatedEntityID = $this->_parentEntity->PrimaryID;

			$query->SQL = "	INSERT INTO core_EntityEmail
							(
								AssociatedEntityType,
								AssociatedEntityID,
								EmailID,
								EmailTypeID,
								IsPrimary
							)
							VALUES
							(
								{$query->SetTextField($this->_associatedEntityType)},
								{$associatedEntityID},
								{$NewElement->EmailID},
								{$NewElement->EmailType->EmailTypeID},
								{$query->SetBooleanField($NewElement->IsPrimary)}
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
						FROM	core_EntityEmail
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		EmailID = {$OldElement->EmailID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityEmail
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

		$query->SQL = "	UPDATE core_EntityEmail SET
							EmailTypeID = {$CurrentElement->EmailType->EmailTypeID},
							IsPrimary = {$query->SetBooleanField($CurrentElement->IsPrimary)}
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		EmailID = {$CurrentElement->EmailID} ";

		$query->Execute();

		return true;

	}

}
?>
