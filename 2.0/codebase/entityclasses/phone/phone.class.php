<?php
/**
 * Phone Class
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

class Phone extends Module 
{
	protected $_phoneID;
	protected $_countryCode;
	protected $_areaCode;
	protected $_localNumber;
	protected $_phoneType;
	
	public function __construct($ID = null, $conn=null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
            {
                $this->Load($ID);
            }
			else
            {
                $this->LoadByID($ID, $conn);
            }
		}
	}
	
	/**
	 * PhoneID property
	 * 
	 * @return int
	 */
	public function getPhoneID()
	{
		return $this->_phoneID;
	}
	
	/**
	 * Number property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getNumber()
	{
		return "+" . $this->getCountryCode() . " (" . $this->_areaCode . ") " . 
			substr($this->_localNumber,0,3) . "-" . substr($this->_localNumber,3,4);
	}
	
	public function setNumber($Value)
	{		
		$this->_localNumber = substr($Value,-7,7);
		$this->_areaCode = substr($Value,-10,3);
		
		if(substr($Value,1,strlen($Value) - 10) != "")
		{
			$this->_countryCode = substr($Value,0,strlen($Value) - 10);
		}
		else 
		{
			$this->_countryCode = 1;
		}
	}
	
	/**
	 * CountryCode property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getCountryCode()
	{
		if (is_set($this->_countryCode))
		{
			return $this->_countryCode;
		}
		else 
		{
			return 1;
		}
	}
	
	public function setCountryCode($Value)
	{
		if (is_set($Value))
		{
			$this->_countryCode = substr(trim($Value), 0, DB_COUNTRY_CODE_MAX_LEN);	
		}
		else
		{
			$this->_countryCode = $Value;
		}
		
	}
	
	/**
	 * AreaCode property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getAreaCode()
	{
		return $this->_areaCode;
	}

	public function setAreaCode($Value)
	{
		if (is_set($Value))
		{
			$this->_areaCode = substr(trim($Value), 0, DB_AREA_CODE_MAX_LEN);
		}
		else
		{
			$this->_areaCode = $Value;
		}
	}

	/**
	 * LocalNumber property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getLocalNumber()
	{
		return $this->_localNumber;
	}
	
	public function setLocalNumber($Value)
	{
		
		if (is_set($Value))
		{
			$this->_localNumber = substr(trim($Value), 0, DB_LOCAL_NUMBER_MAX_LEN);	
		}
		else
		{
			$this->_localNumber = $Value;
		}
		
	}
	
	/**
	 * PhoneNumber property
	 * 
	 * @return int
	 */
	public function getPhoneNumber()
	{
		$number = $this->_areaCode . $this->_localNumber;
		
		return $number;
	}

	/**
	 * PhoneType property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getPhoneType()
	{
		return $this->_phoneType;
	}
	
	public function setPhoneType($Value)
	{
		if ($Value instanceof PhoneType)
		{
			$this->_phoneType = $Value;
		}
		elseif (is_set($Value) == false)
		{
			$this->_phoneType = null;
		}
		
	}
	
	public function Load($dr)
	{
		
		$this->_phoneID = $dr['PhoneID'];
		$this->_countryCode = $dr['CountryCode'];
		$this->_areaCode = $dr['AreaCode'];
		$this->_localNumber = $dr['LocalNumber'];
		
		if (is_set($dr['PhoneTypeID']))
		{
			$this->_phoneType = new PhoneType($dr['PhoneTypeID']);	
		}

		if (is_set($dr['PhoneTypeID']))
		{
			if ($this->_phoneType->IsLoaded)
			{
				$returnValue = true;
				$this->_isLoaded = true;
			}
			else
			{
				$returnValue = false;
				$this->_isLoaded = false;			
			}
		}
		else 
		{
			$returnValue = true;
			$this->_isLoaded = true;			
		}

		return $returnValue;
		
	}
	
	public function LoadByID($ID, $conn=null)
	{
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		$query = "SELECT PhoneID, 
					CountryCode, 
					AreaCode, 
					LocalNumber
				 FROM core_PhoneMaster 
				 WHERE PhoneID = $ID";
		
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

	public function Save($conn=null)
	{
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		if (is_set($this->_phoneID) OR $this->_phoneID > 0)
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
		
		$query = "	INSERT INTO core_PhoneMaster
					(
						CountryCode,
						AreaCode,
						LocalNumber
					)
					VALUES
					(
						{$conn->SetNullTextField($this->_countryCode)},
						{$conn->SetNullTextField($this->_areaCode)},
						{$conn->SetNullTextField($this->_localNumber)}
					)";
		
		$conn->Execute($query);
		
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_phoneID = $dr['newID'];
		
	}
	
	protected function SaveUpdateRecord($conn)
	{
				
		$query = "	UPDATE core_PhoneMaster SET
						CountryCode = {$conn->SetNullTextField($this->_countryCode)},
						AreaCode = {$conn->SetNullTextField($this->_areaCode)},
						LocalNumber = {$conn->SetNullTextField($this->_localNumber)}
					WHERE PhoneID = {$this->_phoneID}";
		
		$conn->Execute($query);
		
	}
	
	public function Export()
	{
		
		$this->_exportEntities[] = $this->CreateXMLentity("number", $this->getPhoneNumber());
		$this->_exportEntities[] = $this->CreateXMLentity("type", $this->_phoneType->Description);
		
		return parent::Export();
	}

	public function IsValid($Control)
	{
		// No Value is ok, let the IsRequired Validator handle that
		if ($Control->Value != "")
		{
			$tempValue = ereg_replace("[^0-9]", '', $Control->Value);
		
			if (strlen($Control->Value) != 10)
			{
				$returnValue = $Control->Label . " is not a valid phone number!";
			}
		}
		
		return $returnValue;
	}
}

?>