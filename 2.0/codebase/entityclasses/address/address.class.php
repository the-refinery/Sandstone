<?php
/**
 * Address Class
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

class Address extends Module 
{
	protected $_addressID;
	protected $_street;
	protected $_zipCode;
	protected $_isPrimary;
	protected $_newAddress;
	
	protected $_originalDR; 
	
	public function __construct($ID = null, $conn=null)
	{
		if (is_set($ID))
		{
			if (is_array($ID))
				$this->Load($ID);
			else
				$this->LoadByID($ID, $conn);
		}
	}
	
	/**
	 * AddressID property
	 * 
	 * @return int
	 */
	public function getAddressID()
	{
		return $this->_addressID;
	}
	
	/**
	 * Street property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getStreet()
	{
		return $this->_street;
	}
	
	public function setStreet($Value)
	{
		$this->_street = $Value;
	}
	
	/**
	 * ZipCode property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getZipCode()
	{
		return $this->_zipCode;
	}
	
	public function setZipCode($Value)
	{
		if (is_set($Value))
		{
			if ($Value instanceof ZipCode)
			{
				$this->_zipCode = $Value;
			}
		}
		else
		{
			unset($this->_zipCode);
		}
	}

	/**
	 * IsPrimary property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsPrimary()
	{
		return $this->_isPrimary;
	}
	
	public function setIsPrimary($Value)
	{
		$this->_isPrimary = $Value;
	}

	/**
	 * NewAddress property
	 * 
	 * @return string
	 */
	public function getNewAddress()
	{
		return $this->_newAddress;
	}

	/**
	 * DisplayText property
	 * 
	 * @return string
	 */
	public function getDisplayText()
	{
		
		$returnValue = $this->_street . '<br/>';
		
		if (is_set($this->_street2))
		{
			$returnValue = $returnValue . $this->_street2 . '<br/>';
		}
		
		$returnValue = $returnValue . $this->_zipCode->DisplayText;
		
		return $returnValue;
	}
		
	public function Load($dr)
	{
		$this->_addressID = $dr['AddressID'];
		$this->_street = $dr['Street'];
		$this->_zipCode = new ZipCode($dr['ZipCode']);
		$this->_isPrimary = Connection::GetBooleanField($dr['IsPrimary']);		

		//Save this dr as the original
		$this->_originalDR = $dr;
		

		if ($this->_zipCode->IsLoaded)
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
	
	public function LoadByID($ID, $conn=null)
	{
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		$query = "	SELECT	AddressID, 
							Street,  
							ZipCode
				 	FROM 	core_AddressMaster 
				 	WHERE 	AddressID = $ID";
		
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
		
		//We ONLY do inserts of address records.  To maintain 
		//accurate records for past orders		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		$query = "	INSERT INTO core_AddressMaster
					(
						 Street,
						 ZipCode,
						 City,
						 StateCode,
						 StateName,
						 CountryName
					)
					VALUES
					(
						{$conn->SetTextField($this->_street)},
						{$conn->SetTextField($this->_zipCode->ZipCode)},
						{$conn->SetTextField($this->_zipCode->City)},
						{$conn->SetTextField($this->_zipCode->State->StateCode)},
						{$conn->SetTextField($this->_zipCode->State->Name)},
						{$conn->SetTextField($this->_zipCode->State->Country->Name)}
					)";
		
		$conn->Execute($query);
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		//If this is a save of an existing address, return a new object
		//and reset this current one to the original data.
		if (is_set($this->_addressID))
		{
			//Reset this object, from our saved dr if we have one, otherwise 
			//hit the database via ID
			if (is_set($this->_originalDR))
			{
				$this->load($this->_originalDR);
			}
			else
			{
				$this->loadByID($this->_addressID);
			}
			
			//Load an new object for the saved record
			$this->_newAddress = new Address($dr['newID']);

			//Now return the new object
			$returnValue = $this->_newAddress;
		}
		else
		{
			//Set my ID to the new one
			$this->_addressID = $dr['newID'];
			
			//Make sure there's no "new address" property set
			$this->_newAddress = null;
			
			$returnValue = null;
			
			$this->_isLoaded = true;
		}
		
		return $returnValue;
		
	}
	
	public function Export()
	{
		
		$this->_exportEntities[] = $this->CreateXMLentity("street", $this->_street);
		$this->_exportEntities[] = $this->CreateXMLentity("city", $this->_zipCode->City);
		$this->_exportEntities[] = $this->CreateXMLentity("state", $this->_zipCode->State->StateCode);
		$this->_exportEntities[] = $this->CreateXMLentity("zip", $this->_zipCode->ZipCode);
		$this->_exportEntities[] = $this->CreateXMLentity("country", $this->_zipCode->State->Country->Name);
		$this->_exportEntities[] = $this->CreateXMLentity("isprimary", $this->_isPrimary, true);
		
		return parent::Export();
	}
	
}

?>