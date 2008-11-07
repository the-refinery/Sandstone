<?php
/*
Files Collective Class

@package Sandstone
@subpackage File
*/

class Files extends CollectiveBase
{

	public function __construct($Name = null, $ParentEntity = null)
	{
		parent::__construct($Name, $ParentEntity);

		$this->_elementType = "File";
	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();

			$query = new Query();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = File::GenerateBaseSelectClause();

			$fromClause = File::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityFile b ON b.FileID = a.FileID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query->SQL = $selectClause . $fromClause . $whereClause;

			$query->Execute();

			$query->LoadEntityArray($this->_elements, "File", "FileID");

			$returnValue = true;

			$this->_isLoaded = true;

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function ProcessNewElement($NewElement)
	{

		//Add the new File
		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	INSERT INTO core_EntityFile
						(
							AssociatedEntityType,
							AssociatedEntityID,
							FileID
						)
						VALUES
						(
							{$query->SetTextField($associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->FileID}
						)";

		$query->Execute();

		return true;
	}

	protected function ProcessOldElement($OldElement)
	{

		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityFile
						WHERE	AssociatedEntityType = {$query->SetTextField($associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		FileID = {$OldElement->FileID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityFile
						WHERE	AssociatedEntityType = {$query->SetTextField($associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}";

		$query->Execute();

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		//Nothing to save
		return false;
	}

}

?>