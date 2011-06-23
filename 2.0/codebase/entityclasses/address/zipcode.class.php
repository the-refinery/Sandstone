<?php
/**
 * Zipcode Class
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

class ZipCode extends Module
{
	
	protected $_zipCode;
	protected $_city;
	protected $_state;
	protected $_latitude;
	protected $_longitude;
	protected $_gmtOffset;
	protected $_isDaylightSavings;
	
	public function __construct($Zip = null)
	{
		if (is_set($Zip))
		{
			if (is_array($Zip))
				$this->Load($Zip);
			else
				$this->LoadByID($Zip);
		}
	}
	
	/**
	 * ZipCode property
	 * 
	 * @return int
	 */
	public function getZipCode()
	{
		return $this->_zipCode;
	}
	
	/**
	 * City property
	 * 
	 * @return string
	 */
	public function getCity()
	{
		return $this->_city;
	}
	
	/**
	 * State property
	 * 
	 * @return string
	 */
	public function getState()
	{
		return $this->_state;
	}
	
	/**
	 * Latitude property
	 * 
	 * @return int
	 */
	public function getLatitude()
	{
		return $this->_latitude;
	}
	
	/**
	 * Longitude property
	 * 
	 * @return int
	 */
	public function getLongitude()
	{
		return $this->_longitude;
	}
	
	/**
	 * GMToffset property
	 * 
	 * @return int
	 */
	public function getGMToffset()
	{
		return $this->_gmtOffset;
	}
	
	/**
	 * IsDaylightSavings property
	 * 
	 * @return boolean
	 */
	public function getIsDaylightSavings()
	{
		return $this->_isDaylightSavings;
	}

	/**
	 * DisplayText property
	 * 
	 * @return string
	 */
	public function getDisplayText()
	{
		return $this->_city . ', ' . $this->_state->StateCode . ', ' . $this->_zipCode;
	}
	
	public function Load($dr)
	{
		$this->_zipCode = $dr['ZipCode'];
		$this->_city = $dr['City'];
		$this->_state = new State($dr['StateCode']);
		$this->_latitude = $dr['Latitude'];
		$this->_longitude = $dr['Longitude'];
		$this->_gmtOffset = $dr['GMToffset'];
		$this->_isDaylightSavings = $dr['IsDaylightSavings'];

		if ($this->_state->IsLoaded)
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
	
	public function LoadByID($ZipCode)
	{
		
		$conn = GetConnection(License::$CodeTablesDBconfig);
		
		$query = "	SELECT 	ZipCode, 
							City, 
							StateCode,
							Latitude,
							Longitude,
							GMToffset,
							IsDaylightSavings
				 	FROM 	core_ZipCodeMaster 
				 	WHERE 	ZipCode = '{$ZipCode}'";
		
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