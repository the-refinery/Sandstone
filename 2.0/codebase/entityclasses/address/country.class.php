<?php
/**
 * Country Class
 * 
 * @package Sandstone
 * @subpackage Address
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

SandstoneNamespace::Using("Sandstone.ADOdb");

class Country extends Module 
{
	protected $_countryID;
	protected $_name;
	protected $_iso;
	
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
	 * CountryID property
	 * 
	 * @return int
	 */
	public function getCountryID()
	{
		return $this->_countryID;
	}
	
	/**
	 * Name property
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	/**
	 * ISO property
	 * 
	 * @return 
	 */
	public function getISO()
	{
		return $this->_iso;
	}
	
	public function Load($dr)
	{
		$this->_countryID = $dr['CountryID'];
		$this->_name = $dr['Name'];
		$this->_iso = $dr['ISO'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		
		$conn = GetConnection(License::$CodeTablesDBconfig);
		
		$query = "	SELECT 	CountryID, 
							Name, 
							ISO
					 FROM 	core_CountryMaster 
					 WHERE 	CountryID = {$ID}";
		
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