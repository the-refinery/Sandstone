<?php
/*
Tags Class File

@package Sandstone
@subpackage Tag
*/

class Tags extends CollectiveBase
{
	public function __construct($Name = null, $ParentEntity = null)
	{
		parent::__construct($Name, $ParentEntity);

		$this->_elementType = "Tag";
	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();

			$query = new Query();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Tag::GenerateBaseSelectClause();
			$selectClause .= ",	b.UserID,
								b.AddTimestamp ";

			$fromClause = Tag::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityTag b ON b.TagID = a.TagID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_elements, "Tag", "TagID");

			$this->_isLoaded = true;

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function ProcessNewElement($NewElement)
	{

		//Now add the new Tag
		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$addDate = new Date();
		$userID = Application::CurrentUser()->UserID;

		$query->SQL = "	INSERT INTO core_EntityTag
						(
							AssociatedEntityType,
							AssociatedEntityID,
							TagID,
							UserID,
							AddTimestamp
						)
						VALUES
						(
							{$query->SetTextField($associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->TagID},
							{$userID},
							{$query->SetDateField($addDate)}
						)";

		$query->Execute();

		$returnValue = true;

		return $returnValue;
	}

	protected function ProcessOldElement($OldElement)
	{

		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityTag
						WHERE	AssociatedEntityType = {$query->SetTextField($associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		TagID = {$OldElement->TagID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityTag
						WHERE	AssociatedEntityType = {$query->SetTextField($associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}";

		$query->Execute();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		//There is nothing to edit
		return false;
	}

}
?>