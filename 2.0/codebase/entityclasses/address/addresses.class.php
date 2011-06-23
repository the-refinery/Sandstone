<?php
/**
 * Addresses Collective Class
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

class Addresses extends Module
{
	
	protected $_associatedEntityModule;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_addresses;
	protected $_primaryAddress;

	
	public function __construct($Module = null, $Type = null, $ID = null, $conn=null)
	{
		if (is_set($ID) && is_numeric($ID) && is_set($Type) && is_set($Module))
		{

			$this->_associatedEntityModule = $Module;
			$this->_associatedEntityType = $Type;
			$this->_associatedEntityID = $ID;
			
			//$this->Load($conn);
		}
	}
	
	/**
	 * AssociatedEntityModule property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityModule()
	{
		return $this->_associatedEntityModule;
	}
	
	public function setAssociatedEntityModule($Value)
	{
		$this->_associatedEntityModule = trim($Value);
	}
	
	/**
	 * AssociatedEntityType property
	 * 
	 * @return 
	 * 
	 * @param  $Value
	 */
	public function getAssociatedEntityType()
	{
		return $this->_associatedEntityType;
	}
	
	public function setAssociatedEntityType($Value)
	{
		$this->_associatedEntityType = trim($Value);
	}
	
	/**
	 * AssociatedEntityID property
	 * 
	 * @return int
	 * 
	 * @param int $Value
	 */
	public function getAssociatedEntityID()
	{
		return $this->_associatedEntityID;
	}
	
	public function setAssociatedEntityID($Value)
	{
		if (is_numeric($Value))
		{
			$this->_associatedEntityID = $Value;
		}
	}

	/**
	 * Addresses property
	 * 
	 * @return array
	 */
	public function getAddresses()
	{
		if (is_set($this->_addresses) == false)
		{
			$this->Load();
		}
		
		return $this->_addresses;
	}
	
	/**
	 * PrimaryAddress property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getPrimaryAddress()
	{
		if (is_set($this->_addresses) == false)
		{
			$this->Load();
		}
		
		if (is_set($this->_primaryAddress))
		{
			$returnValue = $this->_primaryAddress;	
		}
		else 
		{
			//There isn't a primary Address flagged.  If we only have 1 address in the
			//array, return it as the defacto primary address.
			if (count($this->_addresses) == 1)
			{
				//Doing it this way because we don't know the key (ID) for the
				//one address object in the array.
				foreach ($this->_addresses as $tempAddress)
				{
					$returnValue = $tempAddress;
				}				
			}
			else
			{
				$returnValue = null;
			}
		}
		
		return $returnValue;
	}
	
	public function setPrimaryAddress($Value)
	{
		
		if (is_set($this->_addresses) == false)
		{
			$this->Load();
		}
		
		if ($Value instanceof Address)
		{
			//Turn off any other primary flags
			$this->ClearPrimaryFlags();
				
			//Now set the one we were passed as primary
			$Value->IsPrimary = true;
			
			//Assure it's been added to the array
			$this->_addresses[$Value->AddressID] = $Value;
			
			//Finally set it as the primary Image
			$this->_primaryAddress = $this->_addresses[$Value->AddressID];
		}
		elseif (is_null($Value))
		{
			//Turn off any other primary flags
			$this->ClearPrimaryFlags();

			//Now clear the protected field
			$this->_primaryAddress = NULL;
		}
		
	}
	
	public function Load()
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Address";
		$idField = $this->_associatedEntityType . "ID";
		
		$conn = GetConnection();	
		
		$query = "	SELECT 	a.AddressID, 
							a.IsPrimary,
							b.Street, 
							b.ZipCode
					FROM 	$tableName a 
							INNER JOIN core_AddressMaster b ON a.AddressID = b.AddressID 
					WHERE 	a.$idField = {$this->_associatedEntityID}";
					
		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to 
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow()) 
			{
				
				$tempAddress = new Address($dr);
				
				if ($tempAddress->IsLoaded)
				{
					if ($tempAddress->IsPrimary)
					{
						$this->_primaryAddress = $tempAddress;
					}
		
					$this->_addresses[$tempAddress->AddressID] = $tempAddress;
					
					$returnValue = true;
				}
							
			}			
			
		}
		else
		{
			$returnValue = false;
		}
		
		$this->_isLoaded = $returnValue;
		
		return $returnValue;
		
	}

	public function Save($conn=null)
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Address";
		$idField = $this->_associatedEntityType . "ID";
		
		if (is_set($this->_addresses) == false)
		{
			$this->Load();
		}
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		//First, clear all database entries
		$query = "	DELETE 
					FROM 	$tableName
					WHERE 	$idField = {$this->_associatedEntityID}";
		
		$conn->Execute($query);
		
		//Now loop through each of the images and add a record for each
		if (is_set($this->_addresses))
		{
			foreach ($this->_addresses as $tempAddress)
			{

				$query = "	INSERT INTO $tableName
							(
								$idField,
								AddressID,
								IsPrimary
							)
							VALUES
							(
								{$this->_associatedEntityID},
								{$tempAddress->AddressID},
								{$conn->SetBooleanField($tempAddress->IsPrimary)}
							)";

				$conn->Execute($query);
			}
		}
		$this->_isLoaded = true;
	}
	
	public function AddAddress($newAddress)
	{
		if ($newAddress instanceof Address)
		{
			
			if (is_set($this->_addresses) == false)
			{
				$this->Load();
			}			
			
			//Make sure we don't wind up with 2 addresses marked as primary
			if ($newAddress->IsPrimary)
			{
				if (is_set($this->_primaryAddress))
				{
					if ($this->_primaryAddress->AddressID <> $newAddress->ImageID)
					{
						$newAddress->IsPrimary = false;
					}					
				}
				else
				{
					$this->_primaryAddress = $newAddress;
				}
			}

			//Add it to the array.
			$this->_addresses[$newAddress->AddressID] = $newAddress;
			
		}
	}
	
	public function RemoveAddress($oldAddress)
	{
		if ($oldAddress instanceof Address)
		{
			if (is_set($this->_addresses) == false)
			{
				$this->Load();
			}			
						
			//Clear the array element
			unset($this->_addresses[$oldAddress->AddressID]);
			
			//Check to see if this was the primary address, if so - clear it.
			if (is_set($this->_primaryAddress))
			{
				if ($this->_primaryAddress->AddressID == $oldAddress->AddressID)
				{
					unset($this->_primaryAddress);
				}
			}
		}

	}

	protected function ClearPrimaryFlags()
	{
		//Loop through the array, turning off all the IsPrimary flags
		foreach ($this->_addresses as $tempAddress)
		{
			$tempAddress->IsPrimary = false;
		}
		
	}
	
	public function Export()
	{
		if (is_set($this->_addresses) == false)
		{
			$this->Load();
		}			
				
		foreach($this->_addresses as $tempAddress)
		{
			$this->_exportEntities[] = $tempAddress->Export();
		}
		
		return parent::Export();
	}
	
}



?>
