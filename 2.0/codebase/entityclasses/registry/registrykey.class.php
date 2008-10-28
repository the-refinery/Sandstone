<?php
/**
 * Registry Key Class
 * 
 * @package Sandstone
 * @subpackage Registry
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

NameSpace::Using("Sandstone.ADOdb");

class RegistryKey extends Module
{
	
	protected $_keyID;
	protected $_name;
	protected $_description;
	protected $_value;
	protected $_allowedValues;
	
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
	 * KeyID property
	 * 
	 * @return int
	 */
	public function getKeyID()
	{
		return $this->_keyID;
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
	 * Description property
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return $this->_description;
	}
	
	/**
	 * Value property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getValue()
	{
		return $this->_value;
	}
	
	public function setValue($Value)
	{
		if (is_set($Value))
		{
			//Load any allowed values
			if (is_set($this->_allowedValues) == false)
			{
				$this->LoadAllowedValues();
			}

			if (is_set($this->_allowedValues))
			{
				//There are limited values, is the passed one allowed?				
				if (is_set($this->_allowedValues[$Value]))
				{
					$this->_value = $Value;					
				}
			}
			else 
			{
				//There aren't limitations, accept any value
				$this->_value = $Value;
			}
			
		}
		else 
		{
			$this->_value = null;
		}
		
	}
	
	/**
	 * AllowedValues property
	 * 
	 * @return 
	 */
	public function getAllowedValues()
	{
		if (is_set($this->_allowedValues) == false)
		{
			$this->LoadAllowedValues();
		}
		
		return $this->_allowedValues;
	}

	public function Load($dr)
	{
		$this->_keyID = $dr['KeyID'];
		$this->_name = $dr['Name'];
		$this->_description = $dr['Description'];
		$this->_value = $dr['Value'];
		
		$this->_isLoaded = true;
		
		return true;
	}
	
	public function LoadByID($ID)
	{
		$conn = GetConnection();
		
		$query = "	SELECT 	a.KeyID,
							a.Name,
							a.Description,
							b.Value
					FROM 	core_RegistryKeyMaster a
							LEFT JOIN core_RegistryKeyValue b ON a.KeyID = b.KeyID
					WHERE 	a.KeyID = $ID";
		
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
	
	public function LoadAllowedValues()
	{
				
		$conn = GetConnection();
		
		$query = "	SELECT 	Value
					FROM 	core_RegistryKeyAllowedValues
					WHERE 	KeyID = {$this->_keyID}";
		
		$ds = $conn->Execute($query);
		
		if ($ds) 
		{
			if ($ds->RecordCount() > 0)
			{
				while ($dr = $ds->FetchRow()) 
				{
				    $this->_allowedValues[$dr['Value']] = $dr['Value'];
				}
				
				$returnValue = true;
			}
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

		//First, clear any existing record
		$query = "	DELETE FROM core_RegistryKeyValue
					WHERE KeyID = {$this->_keyID}";
		
		$conn->Execute($query);
	

		//Now add the new record, if there is a set value.
		if (is_set($this->_value))
		{
			$query = "	INSERT INTO core_RegistryKeyValue 
						(
							KeyID,
							Value
						)
						VALUES
						(
							{$this->_keyID},
							{$conn->SetTextField($this->_value)}
						)";
			
			$conn->Execute($query);			
		}

		$this->_isLoaded = true;
				
	}
	
}


?>