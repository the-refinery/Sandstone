<?php
/*
Files Collective Class

@package Sandstone
@subpackage File
*/

SandstoneNamespace::Using("Sandstone.ADOdb");

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

			$conn = GetConnection();

			$entityType = get_class($this->_parentEntity);
			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;

			$selectClause = File::GenerateBaseSelectClause();
			$selectClause .= ",	b.PhoneTypeID ";

			$fromClause = File::GenerateBaseFromClause();
			$fromClause .= "	INNER JOIN core_EntityFile b ON b.FileID = a.FileID ";

			$whereClause = "	WHERE	b.AssociatedEntityType = '{$entityType}'
								AND		b.AssociatedEntityID = {$entityID} ";

			$query = $selectClause . $fromClause . $whereClause;

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempFile = new File($dr);
					$this->_elements[$tempFile->FileID] = $tempFile;
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

		//Add the new File
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	INSERT INTO core_EntityFile
					(
						AssociatedEntityType,
						AssociatedEntityID,
						FileID
					)
					VALUES
					(
						{$conn->SetTextField($associatedEntityType)},
						{$associatedEntityID},
						{$NewElement->FileID}
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
					FROM	core_EntityFile
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}
					AND		FileID = {$OldElement->FileID}";

		$conn->Execute($query);

		return true;

	}

	protected function ProcessClearElements()
	{
		$conn = GetConnection();

		$associatedEntityType = get_class($this->_parentEntity);
		$associatedEntityID = $this->_parentEntity->PrimaryID;

		$query = "	DELETE
					FROM	core_EntityFile
					WHERE	AssociatedEntityType = {$conn->SetTextField($associatedEntityType)}
					AND		AssociatedEntityID = {$associatedEntityID}";

		$conn->Execute($query);

		return true;
	}

	protected function ProcessSaveElement($CurrentElement)
	{
		//Nothing to save
		return false;
	}

}

?>