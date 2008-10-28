<?php
/**
 * SEO Page Class
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

class SEOpage extends Module
{
	protected $_seoPageID;
	protected $_title;
	protected $_description;
	protected $_name;
	protected $_pageFileName;
	
	protected $_keywords;
	protected $_sortedKeywords;
	
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
				if (is_numeric($ID))
				{
					$this->LoadByID($ID);
				}
				else 
				{
					$this->LoadByName($ID);
				}
			}
		}
	}
	
	/**
	 * SEOpageID property
	 * 
	 * @return int
	 */
	public function getSEOpageID()
	{
		return $this->_seoPageID;
	}
	
	/**
	 * Title property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getTitle()
	{
		return $this->_title;
	}
	
	public function setTitle($Value)
	{
		$this->_title = trim($Value);
	}
	
	/**
	 * Description property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getDescription()
	{
		return $this->_description;
	}
	
	public function setDescription($Value)
	{
		$this->_description =  trim($Value);
	}
	
	/**
	 * Name property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	public function setName($Value)
	{
		if (is_set($Value))
		{
			$Value = $this->EncodeString($Value);
			
			//Make sure the name we are being passed is unique 
			$success = $this->VerifyUniqueSEOname($Value);
			
			if ($success)
			{
				$this->_name = $Value;	
			}
		}
	}
	
	/**
	 * PageFileName property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getPageFileName()
	{
		return $this->_pageFileName;
	}
	
	public function setPageFileName($Value)
	{
		if (is_set($Value))
		{
			$this->_pageFileName = substr(trim($Value), 0, DB_NAME_MAX_LEN);
		}
	}
	
	/**
	 * Keywords property
	 * 
	 * @return array
	 */
	public function getKeywords()
	{
		
		if (is_set($this->_keywords) == false)
		{
			$this->LoadKeywords();
		}
		
		return $this->_keywords;
	}

	/**
	 * SortedKeywords property
	 * 
	 * @return arraye
	 */
	public function getSortedKeywords()
	{
		
		if (is_set($this->_keywords) == false)
		{
			$this->LoadKeywords();
		}
		
		//Ensure the sorted list is completely up to date.
		$this->LoadSortedKeywords();

		
		return $this->_sortedKeywords;
	}
	
	/**
	 * KeywordsCSV property
	 * 
	 * @return 
	 */
	public function getKeywordsCSV()
	{
		if (is_set($this->_sortedKeywords) == false)
		{
			$this->LoadKeywords();
		}
		
		if (is_set($this->_sortedKeywords))
		{
			//Ensure the sorted list is completely up to date.
			$this->LoadSortedKeywords();
			
			//Get an array of keyword text
			$sortedArray = Array();
			foreach($this->_sortedKeywords as $tempKeyword)
			{
				$tempString = $this->ParseMacroKeyword($tempKeyword);
				
				if (is_set($tempString))
				{
					$sortedArray[] = $tempString;	
				}
				
			}
			
			//Build the CSV return value
			$returnValue = implode(", ", $sortedArray);			
		}
		
		return $returnValue;
	}
	
	public function Load($dr)
	{
		$this->_seoPageID = $dr['SEOpageID'];
		$this->_title = $dr['Title'];
		$this->_description = $dr['Description'];
		$this->_name = $dr['Name'];
		$this->_pageFileName = $dr['PageFileName'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{

		$conn = GetConnection();
		
		$query = "	SELECT 	SEOpageID,
							Title,
							Description,
							Name,
							PageFileName
					FROM 	core_SEOpageMaster
					WHERE 	SEOpageID = $ID";
		
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
	
	public function LoadByName($SearchName)
	{

		$SearchName = strtolower($SearchName);
		
		$conn = GetConnection();
		
		$query = "	SELECT 	SEOpageID,
							Title,
							Description,
							Name,
							PageFileName
					FROM 	core_SEOpageMaster
					WHERE 	Name = '$SearchName'";
		
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
	
	protected function LoadKeywords()
	{

		$conn = GetConnection();
		
		$query = "	SELECT 	KeywordID,
							SEOpageID,
							Keyword,
							SortOrder
					FROM 	core_SEOkeywordMaster
					WHERE 	SEOpageID = {$this->_seoPageID}";
				
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			while ($dr = $ds->FetchRow()) 
			{
				$tempKeyword = new SEOkeyword($dr);
			    $this->_keywords[$tempKeyword->KeywordID] = $tempKeyword;			    
			}
						
			//Build the sorted array
			$this->LoadSortedKeywords();
			
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	protected function LoadSortedKeywords()
	{
		if (is_set($this->_keywords))
		{
			
			$this->_sortedKeywords = array();
			
			foreach ($this->_keywords as $tempKeyword)
			{
				$this->_sortedKeywords[$tempKeyword->KeywordID] = $tempKeyword;
			}
			
			//Now sort it.
			usort($this->_sortedKeywords, array("SEOpage", "KeywordSortOrderCompare"));
			
			$returnValue = true;
		}
		else 
		{
			$this->_sortedKeywords = null;
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	public function Save()
	{	
		$conn = GetConnection();
		
		if (is_set($this->_seoPageID) OR $this->_seoPageID > 0)
		{
			$this->SaveUpdateRecord($conn);
		}
		else
		{
			$this->SaveNewRecord($conn);
		}
		
		if(is_set($this->_keywords))
		{
			$this->SaveKeywords();
		}
		
		$this->_isLoaded = true;
				
	}
	
	protected function SaveNewRecord($conn)
	{
		
		$query = "	INSERT INTO core_SEOpageMaster
					(
						Title,
						Description,
						Name,
						PageFileName
					)
					VALUES
					(
						{$conn->SetNullTextField($this->_title)},
						{$conn->SetNullTextField($this->_description)},
						'{$conn->SetTextField($this->_name)}',
						'{$conn->SetTextField($this->_pageFileName)}'
					)";
		
		$conn->Execute($query);
		
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_seoPageID = $dr['newID'];
		
	}
	
	protected function SaveUpdateRecord($conn)
	{
		
		$query = "	UPDATE core_SEOpageMaster SET
						Title = {$conn->SetNullTextField($this->_title)},
						Description = {$conn->SetNullTextField($this->_description)},
						Name = '{$conn->SetTextField($this->_name)}',
						PageFileName = '{$conn->SetTextField($this->_pageFileName)}'
					WHERE SEOpageID = {$this->_seoPageID}";	
		
		$conn->Execute($query);
		
	}
	
	protected function SaveKeywords()
	{
		
		//Ensure the sorted list is completely up to date.
		$this->LoadSortedKeywords();

		//Update each keyword with it's updated sort order,
		//and then save it.
		for($i=0; $i < count($this->_sortedKeywords); $i++)
		{
			$this->_sortedKeywords[$i]->SortOrder = $i;
			$this->_sortedKeywords[$i]->Save($this);
		}
	}
	
	public function Delete()
	{
		
		$conn = GetConnection();
		
		//First, clean up any keywords
		$query = "	DELETE FROM core_SEOkeywordMaster 
					WHERE SEOpageID = {$this->_seoPageID}";
		
		$conn->Execute($query);
		
		//Now Remove the page
		$query = "	DELETE FROM core_SEOpageMaster 
					WHERE SEOpageID = {$this->_seoPageID}";
		
		$conn->Execute($query);
		
		$this->_seoPageID = null;
		$this->_title = null;
		$this->_description = null;
		$this->_name = null;
		$this->_pageFileName = null;
		
		$this->_isLoaded = false;
		
	}
	
	public function VerifyUniqueSEOname($TestSEOname)
	{
		
		$conn = GetConnection();

		$query = "	SELECT 	SEOpageID,
					Name
			FROM 	core_SEOpageMaster 
			WHERE 	Name LIKE '{$TestSEOname}'";

		if (is_set($this->_seoPageID))
		{
			$query .= "	AND	SEOpageID <> {$this->_seoPageID}";			
		}
		
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() == 0)
		{
			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}
	
	public function AddKeyword($NewKeyword, $SortOrder = 0)
	{
		if (is_set($NewKeyword))
		{
			if (is_set($this->_keywords) == false)
			{
				$this->LoadKeywords();
			}

			if (is_numeric($SortOrder) == false || $SortOrder < 0)
			{
				$SortOrder = 0;
			}
			
			$tempKeyword = new SEOkeyword();
			$tempKeyword->SEOpage = $this;
			$tempKeyword->Keyword = $NewKeyword;
			$tempKeyword->SortOrder = $SortOrder;
			$tempKeyword->Save();
			
			//Is there already a keyword at this sort order?
			$keywordToMove = $this->FindKeywordBySortOrder($SortOrder);
			if (is_set($keywordToMove))
			{
				//We need to move this target one up
				$this->MoveUpSortOrder($keywordToMove);		
			}
			
			$this->_keywords[$tempKeyword->KeywordID] = $tempKeyword;	

			//Update the sorting
			$this->LoadSortedKeywords();
		}
	}
	
	public function ChangeKeywordSortOrder($Keyword, $NewSortOrder)
	{
		if (is_set($this->_keywords) == false)
		{
			$this->LoadKeywords();
		}
				
		if ($Keyword instanceof SEOkeyword && is_set($this->_keywords[$Keyword->KeywordID]))
		{
			
			//Make sure we have a valid new sort order
			if (is_numeric($NewSortOrder) == false || $NewSortOrder < 0)
			{
				$NewSortOrder = 0;
			}
			
			//Get a reference to the move target, and hide it's sort order
			$moveTargetKeyword = $this->_keywords[$Keyword->KeywordID];
			$moveTargetKeyword->HideSortOrder();

			//See if we can find the target Sort Order already used
			$keywordToMove = $this->FindKeywordBySortOrder($NewSortOrder);
	
			if (is_set($keywordToMove))
			{
				//We need to move this target one up
				$this->MoveUpSortOrder($keywordToMove);
			}
			
			//Now Set the new sort order
			$moveTargetKeyword->SortOrder = $NewSortOrder;	
		}
	}
	
	public function RemoveKeyword($OldKeyword)
	{

		if (is_set($this->_keywords) == false)
		{
			$this->LoadKeywords();
		}
		
		if ($OldKeyword instanceof SEOkeyword && $OldKeyword->IsLoaded)
		{
			//They gave us a specific keyword object to remove
			$targetKeywordID = $OldKeyword->KeywordID;	
		}
		elseif (is_numeric($OldKeyword) && is_set($this->_keywords[$OldKeyword]))
		{
			//They gave us a KeywordID to remove
			$targetKeywordID = $OldKeyword;
		}
		else 
		{
			//This must be a keyword text string
			foreach($this->_keywords as $tempKeyword)
			{
				if (strtolower($tempKeyword->Keyword) == strtolower($OldKeyword))
				{
					$targetKeywordID = $tempKeyword->KeywordID;
				}
			}
		}
		
		
		if (is_set($targetKeywordID))
		{
			
			//Delete the Keyword
			$this->_keywords[$targetKeywordID]->Delete();
			
			//Remove it from the array
			unset($this->_keywords[$targetKeywordID]);

			//Update the sorting
			$this->LoadSortedKeywords();
		}
	}
	
	protected function MoveUpSortOrder($Keyword)
	{
		
		$currentSortOrder = $Keyword->SortOrder;
		$targetSortOrder = $currentSortOrder + 1;
		
		//See if we can find the target Sort Order already used
		$keywordToMove = $this->FindKeywordBySortOrder($targetSortOrder);
		
		if (is_set($keywordToMove))
		{
			//We need to move this target one up
			$this->MoveUpSortOrder($keywordToMove);
		}
		
		//Now Set the new sort order
		$Keyword->SortOrder = $targetSortOrder;
	}

	protected function FindKeywordBySortOrder($SortOrder)
	{
		
		foreach ($this->_keywords as $tempKeyword)
		{
			if ($tempKeyword->SortOrder == $SortOrder)
			{
				$returnValue = $tempKeyword;
			}
		}
		
		return $returnValue;
	}
	
	protected function KeywordSortOrderCompare($a, $b)
	{
		if ($a->SortOrder == $b->SortOrder)
		{
			//For 2 that have the same sort order, 
			//Sort by Keyword string
			$returnValue = strcmp($a->Keyword, $b->Keyword);
		}
		elseif ($a->SortOrder < $b->SortOrder)
		{
			$returnValue = -1;
		}
		else 
		{
			$returnValue = 1;
		}
		
		return $returnValue;
		
	}

	protected function ParseMacroKeyword($Keyword)
	{
		//This should be overriden in the specific types 
		//that support Macro Keywords (Products, Categories, etc)
		return $Keyword->Keyword;
	}
	
	protected function EncodeString($Title)
	{
		//$Title = substr(trim($Title), 0, DB_NAME_MAX_LEN);
		
		$Title = str_replace(" ","-", $Title);
		$Title = str_replace(".","", $Title);
		$Title = str_replace(",","", $Title);
		$Title = str_replace("\"","", $Title);
		$Title = str_replace("&","And", $Title);
		$Title = str_replace("\\","", $Title);
		$Title = str_replace("=","-", $Title);
		$Title = str_replace("@","-", $Title);
		$Title = str_replace("&","And", $Title);
		
		$Title = urlencode($Title);
		
		$Title = str_replace("+","-", $Title);
				
		return $Title;
	}
}

?>