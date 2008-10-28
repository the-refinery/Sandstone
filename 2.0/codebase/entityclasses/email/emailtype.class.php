<?php
/**
 * Email Type Class
 * 
 * @package Sandstone
 * @subpackage Email
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

NameSpace::Using("Sandstone.ADOdb");

class EmailType extends Module 
{
	protected $_emailTypeID;
	protected $_description;
	
	public function __construct($ID = null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
				$this->Load($ID);
			else
				$this->LoadByID($ID);
		}
	}
	
	/**
	 * EmailTypeID property
	 * 
	 * @return int
	 */
	public function getEmailTypeID()
	{
		return $this->_emailTypeID;
	}
	
	/**
	 * Description property
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return $this->_description;
	}
	
	public function Load($dr)
	{
		$this->_emailTypeID = $dr['EmailTypeID'];
		$this->_description = $dr['Description'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		$query = "SELECT EmailTypeID, 
					Description
				 FROM core_EmailTypeMaster 
				 WHERE EmailTypeID = $ID";
		
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
}

?>