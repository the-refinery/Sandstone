<?php
/**
 * Tags Class File
 * @package Sandstone
 * @subpackage Tag
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 *
 */

class Tags extends Module
{

	protected $_parentEntity;
	protected $_tags;

	protected $_isTagsChanged;

	public function __construct($ParentEntity)
	{
		if ($ParentEntity instanceof EntityBase)
		{
			$this->_parentEntity = $ParentEntity;
		}

		$this->_tags = new DIarray();
	}

	public function __toString()
	{

    	if (count($this->_tags) == 0)
		{
			$this->Load();
		}

        if (count($this->_tags) > 0)
		{
			$returnValue = "<ul>";

			foreach ($this->_tags as $tempTag)
			{
				$returnValue .= "<li><b>{$tempTag->Text}</b> ({$tempTag->User->Username} @ {$tempTag->AddTimestamp->MySQLTimestamp}) [ID: {$tempTag->TagID}]</li>";
			}

			$returnValue .= "</ul>";
		}
		else
		{
			$returnValue = "<i>None Set</i>";
		}

		return $returnValue;
	}

	/**
	 * ParentEntity property
	 *
	 * @return EntityBase
	 */
	public function getParentEntity()
	{
		return $this->_parentEntity;
	}

	/**
	 * Tags property
	 *
	 * @return DIarray
	 */
	public function getTags()
	{
		if (count($this->_tags) == 0)
		{
			$this->Load();
		}

		return $this->_tags;
	}

	public function Load()
	{

		$this->_tags->Clear();

		if (is_set($this->_parentEntity->PrimaryIDproperty->Value))
		{
			$conn = GetConnection();

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;
			$entityType = strtolower(get_class($this->_parentEntity));

			$query = "	SELECT	a.TagID,
								a.TagText,
								b.UserID,
								b.AddTimestamp
						FROM 	core_TagMaster a
								INNER JOIN core_TagEntity b on b.TagID = a.TagID
						WHERE	LOWER(b.AssociatedEntityType) = {$conn->SetTextField($entityType)}
						AND		b.AssociatedEntityID = {$entityID}
						ORDER BY a.TagText";

			$ds = $conn->Execute($query);

			if ($ds)
			{
				while ($dr = $ds->FetchRow())
				{
					$tempTag = new Tag($dr);

					$this->_tags[$tempTag->Text] = $tempTag;
				}

				$returnValue = true;
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

	public function Save()
	{
		if ($this->_isTagsChanged)
		{

			$entityID = $this->_parentEntity->PrimaryIDproperty->Value;
			$entityType = strtolower(get_class($this->_parentEntity));

			$conn = GetConnection();

			//Clear any existing Tags
			$query = "	DELETE
						FROM	core_TagEntity
						WHERE	LOWER(AssociatedEntityType) = {$conn->SetTextField($entityType)}
						AND		AssociatedEntityID = {$entityID}";

			$conn->Execute($query);

			$returnValue = true;

			//Loop through the Tags, saving them and adding the relationship
			foreach ($this->_tags as $tempTag)
			{
				$success = $tempTag->Save();

				if ($success)
				{
					if (is_set($tempTag->AddTimestamp))
					{
						$addTimestamp = $tempTag->AddTimestamp;
					}
					else
					{
						$addTimestamp = new Date();
					}


					$query = "	INSERT INTO core_TagEntity
								(
									TagID,
									AssociatedEntityID,
									AssociatedEntityType,
									UserID,
									AddTimestamp
								)
								VALUES
								(
									{$tempTag->TagID},
									{$entityID},
									{$conn->SetTextField($entityType)},
									{$tempTag->User->UserID},
									{$conn->SetDateField($addTimestamp)}
								)";

					$conn->Execute($query);
				}
				else
				{
					$returnValue = false;
				}
			}
		}
		else
		{
			$returnValue = true;
		}

		return $returnValue;
	}

	public function AddTag($TagText)
	{
		if (count($this->_tags) == 0)
		{
			$this->Load();
		}

		$TagText = $this->FormatTagText($TagText);

		if (strlen($TagText) > 0 && array_key_exists($TagText, $this->_tags) == false)
		{
			$newTag = new Tag();

			$newTag->User = Application::CurrentUser();
			$newTag->Text = $TagText;
			$newTag->AddTimestamp = new Date();

			$this->_tags[$newTag->Text] = $newTag;

			$this->_tags->Ksort();

			$this->_isTagsChanged = true;

			$returnValue = $TagText;
		}

		return $returnValue;
	}

	public function RemoveTag($TagText)
	{
		if (count($this->_tags) == 0)
		{
			$this->Load();
		}

		$TagText = $this->FormatTagText($TagText);

        if (strlen($TagText) > 0)
		{
			unset($this->_tags[$TagText]);

			$this->_isTagsChanged = true;
		}

	}

	protected function FormatTagText($TagText)
	{

		$returnValue = preg_replace('|[^a-z0-9_.\-@#$%*!&]|i', '', strtolower($TagText));

		return $returnValue;
	}

}
?>