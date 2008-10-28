<?php
/**
 * Registry Class
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

class Registry
{
	protected $_keys;
	
	public function __construct()
	{
		
		$conn = GetConnection();
		
		$query = "	SELECT 	a.KeyID,
							a.Name,
							a.Description,
							b.Value
					FROM 	core_RegistryKeyMaster a
							LEFT JOIN core_RegistryKeyValue b ON a.KeyID = b.KeyID";
		
		$ds = $conn->Execute($query);
		
		if ($ds) 
		{
			if ($ds->RecordCount() > 0)
			{
				while ($dr = $ds->FetchRow()) 
				{
					$tempKey = new RegistryKey($dr);
				    $this->_keys[$tempKey->Name] = $tempKey;
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
	
	public function __get($property)
	{	
				
		if ($property == "Keys")
		{
			$returnValue = $this->_keys;
		}
		else 
		{			
			if (is_set($this->_keys[$property]))
			{
				$returnValue = $this->_keys[$property]->Value;
			}
			else
			{
				$returnValue = null;
			}
		}
		
		return $returnValue;
	}
	
	public function __set($property, $value)
	{
		$this->_properties[$property] = $value;
	}
}

?>