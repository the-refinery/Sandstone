<?php
/**
 * Phone Type Class
 * 
 * @package Sandstone
 * @subpackage Phone
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class PhoneType extends Module 
{
	protected $_phoneTypeID;
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
	 * PhoneTypeID property
	 * 
	 * @return int
	 */
	public function getPhoneTypeID()
	{
		return $this->_phoneTypeID;
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
		$this->_phoneTypeID = $dr['PhoneTypeID'];
		$this->_description = $dr['Description'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		$query = "SELECT PhoneTypeID, 
					Description
				 FROM core_PhoneTypeMaster 
				 WHERE PhoneTypeID = $ID";
		
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