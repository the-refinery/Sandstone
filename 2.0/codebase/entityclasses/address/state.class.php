<?php
/**
 * State Class
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

class State extends Module
{
	
	protected $_stateCode;
	protected $_name;
	protected $_country;

	
	public function __construct($StateCode = null)
	{
		if (is_set($StateCode))
		{
			if (is_array($StateCode))
				$this->Load($StateCode);
			else
				$this->LoadByID($StateCode);
		}
	}

	/**
	 * StateCode property
	 * 
	 * @return 
	 */
	public function getStateCode()
	{
		return $this->_stateCode;
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
	 * Country property
	 * 
	 * @return string
	 */
	public function getCountry()
	{
		return $this->_country;
	}
	
	public function Load($dr)
	{
		$this->_stateCode = $dr['StateCode'];
		$this->_name = $dr['Name'];
		$this->_country = new Country($dr['CountryID']);

		if ($this->_country->IsLoaded)
		{
			$returnValue = true;
			$this->_isLoaded = true;
		}
		else
		{
			$returnValue = false;
			$this->_isLoaded = false;			
		}

		return $returnValue;
		
	}
	
	public function LoadByID($StateCode)
	{
		
		$conn = GetConnection(License::$CodeTablesDBconfig);

		$StateCode = strtoupper($StateCode);
				
		$query = "	SELECT 	StateCode, 
							Name, 
							CountryID
				 	FROM 	core_StateMaster 
				 	WHERE 	StateCode = '{$StateCode}'";

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
