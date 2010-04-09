<?php
/*
Files Collective Class

@package Sandstone
@subpackage File
*/

class BaseFiles extends CollectiveBase
{

	public function __construct($Name = null, $ParentEntity = null, $AssociatedEntityType = null)
	{
		parent::__construct($Name, $ParentEntity, $AssociatedEntityType);

		$this->_elementType = "File";
	}

	public function Load()
	{
		if (is_set($this->_parentEntity))
		{

			$this->_elements->Clear();

			$query = new Query();

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = File::GenerateBaseSelectClause();

			$fromClause = File::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityFile b ON b.FileID = a.FileID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$this->_associatedEntityType}'
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

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	INSERT INTO core_EntityFile
						(
							AssociatedEntityType,
							AssociatedEntityID,
							FileID
						)
						VALUES
						(
							{$query->SetTextField($this->_associatedEntityType)},
							{$associatedEntityID},
							{$NewElement->FileID}
						)";

		$query->Execute();

		return true;
	}

	protected function ProcessOldElement($OldElement)
	{

		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityFile
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
						AND		AssociatedEntityID = {$associatedEntityID}
						AND		FileID = {$OldElement->FileID}";

		$query->Execute();

		return true;

	}

	protected function ProcessClearElements()
	{
		$query = new Query();

		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query->SQL = "	DELETE
						FROM	core_EntityFile
						WHERE	AssociatedEntityType = {$query->SetTextField($this->_associatedEntityType)}
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
