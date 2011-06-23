<?php
/*
Tags Class File

@package Sandstone
@subpackage Tag
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

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

			$conn = GetConnection();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = Tag::GenerateBaseSelectClause();
			$selectClause .= ",	b.UserID,
								b.AddTimestamp ";

			$fromClause = Tag::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityTag b ON b.TagID = a.TagID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempTag = new Tag($dr);
					$this->_elements[$tempTag->TagID] = $tempTag;
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

		//Now add the new Tag
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$addDate = new Date();
		$userID = Application::CurrentUser()->UserID;

		$query = "	INSERT INTO core_EntityTag
					(
						AssociatedEntityType,
						AssociatedEntityID,
						TagID,
						UserID,
						AddTimestamp
					)
					VALUES
					(
						{$conn->SetTextField($associatedEntityType)},
						{$associatedEntityID},
						{$NewElement->TagID},
						{$userID},
						{$conn->SetDateField($addDate)}
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
					FROM	core_EntityTag
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		TagID = {$OldElement->TagID}";

		$conn->Execute($query);

		return true;

	}

	protected function ProcessClearElements()
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityTag
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}";

		$conn->Execute($query);

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		//There is nothing to edit
		return false;
	}

}
?>