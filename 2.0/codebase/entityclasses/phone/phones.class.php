<?php
/**
 * Phones Collective Class
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

class Phones extends Module
{
	protected $_associatedEntityModule;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_phones = array();
	protected $_phonesByType = array();
	
	public function __construct($Module = null, $Type = null, $ID = null, $conn=null)
	{
		if (is_set($ID) && is_numeric($ID) && is_set($Type) && is_set($Module))
		{

			$this->_associatedEntityModule = $Module;
			$this->_associatedEntityType = $Type;
			$this->_associatedEntityID = $ID;
			
			$this->Load($conn);
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
	 * Phones property
	 * 
	 * @return array
	 */
	public function getPhones()
	{
		$returnValue = $this->_phones;
		
		return $returnValue;
	}
	
	/**
	 * PhonesbyType property
	 * 
	 * @return array
	 */
	public function getPhonesByType()
	{
		$returnValue = $this->_phonesByType;
		
		return $returnValue;		
	}
		
	public function Load($conn=null)
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Phone";
		$idField = $this->_associatedEntityType . "ID";
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		$query = "SELECT a.PhoneID,
					a.PhoneTypeID,
					b.CountryCode, 
					b.AreaCode, 
					b.LocalNumber
				 FROM $tableName a
				 INNER JOIN core_PhoneMaster b on a.PhoneID = b.PhoneID
				 WHERE a.$idField = {$this->_associatedEntityID}";
		
				$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to 
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow()) 
			{
				

				
				$tempPhone = new Phone($dr);
				
				
				if ($tempPhone->IsLoaded)
				{
	
					$this->_phones[$tempPhone->PhoneID] = $tempPhone;
					$this->_phonesByType[$tempPhone->PhoneType->PhoneTypeID] = $tempPhone;
					
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

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Phone";
		$idField = $this->_associatedEntityType . "ID";
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}

		//First, clear all database entries
		$query = "DELETE FROM $tableName
					WHERE $idField = {$this->_associatedEntityID}";
		
		$conn->Execute($query);
		
		//Now loop through each of the images and add a record for each
		foreach ($this->_phones as $tempPhone)
		{
			$query = "	INSERT INTO $tableName
						(
							$idField,
							PhoneID,
							PhoneTypeID
						)
						VALUES
						(
							{$this->_associatedEntityID},
							{$tempPhone->PhoneID},
							{$tempPhone->PhoneType->PhoneTypeID}
						)";
			
			$conn->Execute($query);
		}
		
		$this->_isLoaded = true;
	}
	
	public function AddPhone($newPhone)
	{
		if ($newPhone instanceof Phone)
		{

			$tempPhoneType= $newPhone->PhoneType;
			
			//Make sure that this phone number 
			//has a valid phone type
			if (is_set($tempPhoneType))
			{				
				
				//If there is already another phone for this type, remove it.
				if (is_set($this->_phonesByType[$tempPhoneType->PhoneTypeID]))
				{
					$this->RemovePhone($this->_phonesByType[$tempPhoneType->PhoneTypeID]);
				}
								
				//Add it to the arrays
				$this->_phones[$newPhone->PhoneID] = $newPhone;
			    $this->_phonesByType[$tempPhoneType->PhoneTypeID] = $newPhone;
			}
			
		}
	}
	
	public function RemovePhone($oldPhone)
	{
		if ($oldPhone instanceof Phone)
		{
			//Clear the array element
			unset($this->_phones[$oldPhone->PhoneID]);
			unset($this->_phonesByType[$oldPhone->PhoneType->PhoneTypeID]);
		}

	}
	
	public function Export()
	{
		foreach($this->_phones as $tempPhone)
		{
			$this->_exportEntities[] = $tempPhone->Export();
		}
		
		return parent::Export();
		
	}

}



?>