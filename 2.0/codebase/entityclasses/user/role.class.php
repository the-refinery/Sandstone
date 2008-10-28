<?php
/**
 * Role Class
 * 
 * @package Sandstone
 * @subpackage User
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * @version 1.0
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

class Role extends Module 
{
	protected $_roleID;
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
	 * RoleID property
	 * 
	 * @return int
	 */
	public function getRoleID()
	{
		return $this->_roleID;
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
		$this->_roleID = $dr['RoleID'];
		$this->_description = $dr['Description'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		$query = "SELECT RoleID, 
					Description
				 FROM core_RoleMaster 
				 WHERE RoleID = $ID";
		
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