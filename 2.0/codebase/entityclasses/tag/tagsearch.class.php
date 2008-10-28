<?php
/**
 * Tag Search Class File
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

class TagSearch extends Module
{

	protected $_searchString;
	protected $_tags;
	protected $_results;

	public function __construct($SearchString = null)
	{
		$this->_tags = new DIarray();
		$this->_results = new DIarray();

		if (is_set($SearchString) && strlen($SearchString) > 0)
		{
			$this->_searchString = $SearchString;
			$this->Search();
		}
	}

	/**
	 * SearchString property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getSearchString()
	{
		return $this->_searchString;
	}

	public function setSearchString($Value)
	{
		$this->_searchString = $Value;
	}

	/**
	 * Tags property
	 *
	 * @return DIarray
	 */
	public function getTags()
	{
		return $this->_tags;
	}

	/**
	 * Results property
	 *
	 * @return DIarray
	 */
	public function getResults()
	{
		return $this->_results;
	}

	public function Search()
	{
		if (is_set($this->_searchString) && strlen($this->_searchString) > 0)
		{
			$this->ClearResults();

			$returnValue = $this->LoadMatchingTags();

			if ($returnValue == true)
			{
				$returnValue = $this->LoadResults();
			}

		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	protected function LoadMatchingTags()
	{

		$conn = GetConnection();

		$formattedSearchString = strtolower("%{$this->_searchString}%");

		$query = "	SELECT	TagID,
							TagText
					FROM	core_TagMaster
					WHERE	TagText LIKE {$conn->SetTextField($formattedSearchString)}
					ORDER BY TagText";

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempTag = new Tag($dr);

				$this->_tags[$tempTag->TagID] = $tempTag;
			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;

	}

	protected function LoadResults()
	{
		$conn = GetConnection();

		$tagIDs = implode(",", $this->_tags->Keys());

		$query = "	SELECT	DISTINCT AssociatedEntityID,
							AssociatedEntityType
					FROM	core_TagEntity
					WHERE	TagID IN ({$tagIDs})
					ORDER BY AddTimestamp DESC ";

		$ds = $conn->Execute($query);

		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow())
			{
				$tempEntityType = $dr['AssociatedEntityType'];
				$tempEntityID = $dr['AssociatedEntityID'];

				$tempEntity = new $tempEntityType ($tempEntityID);

				$this->_results[] = $tempEntity;

			}

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function ClearResults()
	{
		$this->_tags->Clear();
		$this->_results->Clear();
	}


}
?>
