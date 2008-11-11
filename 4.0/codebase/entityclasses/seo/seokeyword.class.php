<?php
/*
SEO Keyword Class

@package Sandstone
@subpackage SEO
*/

NameSpace::Using("Sandstone.Database");

class SEOkeyword extends Module
{
	protected $_keywordID;
	protected $_seoPageID;
	protected $_keyword;
	protected $_sortOrder;

	public function __construct($ID = null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
			{
				$this->Load($ID);
			}
			else
			{
				$this->LoadByID($ID);
			}
		}
	}

	/*
	KeywordID property

	@return int
	*/
	public function getKeywordID()
	{
		return $this->_keywordID;
	}

	/*
	SEOpageID property

	@return int
	@param SEOpage $Value
	*/
	public function getSEOpageID()
	{
		return $this->_seoPageID;
	}

	public function setSEOpage($Value)
	{
		if ($Value instanceof SEOpage && $Value->IsLoaded)
		{
			$this->_seoPageID = $Value->SEOpageID;
		}
		else
		{
			$this->SEOpageID = null;
		}
	}

	/*
	Keyword property

	@return string
	@param string $Value
	*/
	public function getKeyword()
	{
		return $this->_keyword;
	}

	public function setKeyword($Value)
	{
		if (is_set($Value))
		{
			$this->_keyword = trim($Value);
		}
	}

	/*
	SortOrder property

	@return int
	@param int $Value
	*/
	public function getSortOrder()
	{
		return $this->_sortOrder;
	}

	public function setSortOrder($Value)
	{
		if (is_numeric($Value) && $Value >= 0)
		{
			$this->_sortOrder = $Value;
		}
	}

	public function Load($dr)
	{
		$this->_keywordID = $dr['KeywordID'];
		$this->_seoPageID = $dr['SEOpageID'];
		$this->_keyword = $dr['Keyword'];
		$this->_sortOrder = $dr['SortOrder'];

		$this->_isLoaded = true;

		return true;
	}

	public function LoadByID($ID)
	{

		$query = new Query();

		$query->SQL = "	SELECT 	KeywordID,
								SEOpageID,
								Keyword,
								SortOrder
						FROM 	core_SEOkeywordMaster
						WHERE 	KeywordID = $ID";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$returnValue = $this->Load($query->SingleRowResult);
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	public function Save()
	{
		if (is_set($this->_keywordID) OR $this->_keywordID > 0)
		{
			$this->SaveUpdateRecord();
		}
		else
		{
			$this->SaveNewRecord();
		}

		$this->_isLoaded = true;
	}

	protected function SaveNewRecord()
	{

		$query = new Query();

		$query->SQL = "	INSERT INTO core_SEOkeywordMaster
						(
							SEOpageID,
							Keyword,
							SortOrder
						)
						VALUES
						(
							{$query->SetNullNumericField($this->_seoPageID)},
							{$query->SetTextField($this->_keyword)},
							{$this->_sortOrder}
						)";

		$query->Execute();

		//Get the new ID
		$query->SQL = "SELECT LAST_INSERT_ID() newID ";

		$query->Execute();

		$this->_keywordID = $query->SingleRowResult['newID'];

	}

	protected function SaveUpdateRecord($conn)
	{

		$query = new Query();

		$query->$query = "	UPDATE core_SEOkeywordMaster SET
								SEOpageID = {$query->SetNullNumericField($this->_seoPageID)},
								Keyword = {$query->SetTextField($this->_keyword)},
								SortOrder = {$this->_sortOrder}
							WHERE KeywordID = {$this->_keywordID}";

		$query->Execute();

	}

	public function Delete()
	{

		$query = new Query();

		$query->SQL = "	DELETE
						FROM core_SEOkeywordMaster
						WHERE KeywordID = {$this->_keywordID}";

		$query->Execute();

		$this->_keywordID =null;
		$this->_seoPageID = null;
		$this->_keyword = null;
		$this->_sortOrder = null;

		$this->_isLoaded = false;

	}

	public function HideSortOrder()
	{
		//This is for use when doing moves, so that it's not
		//found when looking for it's old Sort order.
		$this->_sortOrder = -1;
	}


}

?>