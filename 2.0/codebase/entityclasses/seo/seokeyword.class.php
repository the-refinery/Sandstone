<?php
/**
 * SEO Keyword Class
 * 
 * @package Sandstone
 * @subpackage SEO
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

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
	
	/**
	 * KeywordID property
	 * 
	 * @return int
	 */
	public function getKeywordID()
	{
		return $this->_keywordID;
	}
	
	/**
	 * SEOpageID property
	 * 
	 * @return int
	 * 
	 * @param SEOpage $Value
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
	
	/**
	 * Keyword property
	 * 
	 * @return string
	 * 
	 * @param string $Value
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
	
	/**
	 * SortOrder property
	 * 
	 * @return int
	 * 
	 * @param int $Value
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

		$conn = GetConnection();
		
		$query = "	SELECT 	KeywordID,
							SEOpageID,
							Keyword,
							SortOrder
					FROM 	core_SEOkeywordMaster
					WHERE 	KeywordID = $ID";
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			$dr = $ds->FetchRow();
			$returnValue = $this->Load($dr);
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;		
	}
	
	public function Save()
	{			
		$conn = GetConnection();
		
		if (is_set($this->_keywordID) OR $this->_keywordID > 0)
		{
			$this->SaveUpdateRecord($conn);
		}
		else
		{
			$this->SaveNewRecord($conn);
		}
		
		$this->_isLoaded = true;
	}
	
	protected function SaveNewRecord($conn)
	{
		
		$query = "	INSERT INTO core_SEOkeywordMaster
					(
						SEOpageID,
						Keyword,
						SortOrder
					)
					VALUES
					(
						{$conn->SetNullNumericField($this->_seoPageID)},
						{$conn->SetTextField($this->_keyword)},
						{$this->_sortOrder}
					)";
		
		$conn->Execute($query);
		
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_keywordID = $dr['newID'];
		
	}
	
	protected function SaveUpdateRecord($conn)
	{
		
		$query = "	UPDATE core_SEOkeywordMaster SET
						SEOpageID = {$conn->SetNullNumericField($this->_seoPageID)},
						Keyword = {$conn->SetTextField($this->_keyword)},
						SortOrder = {$this->_sortOrder}
					WHERE KeywordID = {$this->_keywordID}";	
		
		$conn->Execute($query);
		
	}

	public function Delete()
	{

		$conn = GetConnection();
		
		$query = "	DELETE 
					FROM core_SEOkeywordMaster
					WHERE KeywordID = {$this->_keywordID}";
		
		$conn->Execute($query);
		
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