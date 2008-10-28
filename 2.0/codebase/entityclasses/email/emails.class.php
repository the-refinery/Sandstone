<?php
/**
 * Emails Collective Class
 * 
 * @package Sandstone
 * @subpackage Email
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2006 Designing Interactive
 * 
 * 
 */

NameSpace::Using("Sandstone.ADOdb");

class Emails extends Module
{
	
	protected $_associatedEntityModule;
	protected $_associatedEntityType;
	protected $_associatedEntityID;
	protected $_emails = array();
	protected $_emailsByType = array();
	protected $_primaryEmail;
	
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
	 * Emails property
	 * 
	 * @return array
	 */
	public function getEmails()
	{
		$returnValue = $this->_emails;
		
		return $returnValue;
	}
	
	/**
	 * EmailsByType property
	 * 
	 * @return array
	 */
	public function getEmailsByType()
	{
		$returnValue = $this->_emailsByType;
		
		return $returnValue;		
	}
	
	/**
	 * PrimaryEmail property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getPrimaryEmail()
	{
		
		if (is_set($this->_primaryEmail))
		{
			$returnValue = $this->_primaryEmail;	
		}
		else 
		{
			//There isn't a primary email flagged.  If we only have 1 email in the
			//array, return it as the defacto primary email.
			if (count($this->_emails) == 1)
			{
				//Doing it this way because we don't know the key (ID) for the
				//one email object in te array.
				foreach ($this->_emails as $tempEmail)
				{
					$returnValue = $tempEmail;
				}				
			}
			else
			{
				$returnValue = null;
			}
		}
		
		return $returnValue;
	}
	
	public function setPrimaryEmail($Value)
	{
		if ($Value instanceof Email)
		{
			
			//Make sure that this email has a valid type
			if (is_set($tempEmailType))
			{					
				//If there is already another email for this type, remove it.
				if (is_set($this->_emailsByType[$tempEmailType->EmailTypeID]))
				{
					$this->RemoveEmail($this->_phonesByType[$tempEmailType->EmailTypeID]);
				}
				
				
				//Turn off any other primary flags
				$this->ClearPrimaryFlags();
					
				//Now set the one we were passed as primary
				$Value->IsPrimary = true;
				
				//Assure it's been added to the arrays
				$this->_emails[$Value->EmailID] = $Value;
			    $this->_emailsByType[$Value->EmailType->EmailTypeID] = $Value;
				
				//Finally set it as the primary Email
				$this->_primaryEmail = $this->_emails[$Value->EmailID];
			}
			
			
		}
		elseif (is_null($Value))
		{
			//Turn off any other primary flags
			$this->ClearPrimaryFlags();

			//Now clear the protected field
			$this->_primaryImage = NULL;
		}
		
	}
		
	public function Load($conn=null)
	{

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Email";
		$idField = $this->_associatedEntityType . "ID";
		
		if (is_set($conn) == false)
		{
			$conn = GetConnection();	
		}
		
		$query = "SELECT a.EmailID,
					a.EmailTypeID,
					a.IsPrimary,
					b.Address
				 FROM $tableName a
				 INNER JOIN core_EmailMaster b on a.EmailID = b.EmailID
				 WHERE a.$idField = {$this->_associatedEntityID}";

		$ds = $conn->Execute($query);
		
		if ($ds && $ds->RecordCount() > 0)
		{
			//Set the return value to failure, then set it to true as soon as we are able to 
			//successfully load one.
			$returnValue = false;

			while ($dr = $ds->FetchRow()) 
			{
				

				$tempEmail = new Email($dr);
				
				
				if ($tempEmail->IsLoaded)
				{
					if ($tempEmail->IsPrimary)
					{
						$this->_primaryEmail = $tempEmail;
					}
	
					$this->_emails[$tempEmail->EmailID] = $tempEmail;
					$this->_emailsByType[$tempEmail->EmailType->EmailTypeID] = $tempEmail;
					
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

		$tableName = $this->_associatedEntityModule . "_" . $this->_associatedEntityType . "Email";
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
		foreach ($this->_emails as $tempEmail)
		{
			
			
			
			$query = "	INSERT INTO $tableName
						(
							$idField,
							EmailID,
							EmailTypeID,
							IsPrimary
						)
						VALUES
						(
							{$this->_associatedEntityID},
							{$tempEmail->EmailID},
							{$tempEmail->EmailType->EmailTypeID},
							{$conn->SetBooleanField($tempEmail->IsPrimary)}
						)";
			
			$conn->Execute($query);
		}
		
		$this->_isLoaded = true;
	}
	
	public function AddEmail($newEmail)
	{
		if ($newEmail instanceof Email)
		{

			$tempEmailType= $newEmail->EmailType;
			
			//Make sure that this email has a valid type
			if (is_set($tempEmailType))
			{				
				
				//If there is already another email for this type, remove it.
				if (is_set($this->_emailsByType[$tempEmailType->EmailTypeID]))
				{
					$this->RemoveEmail($this->_emailsByType[$tempEmailType->EmailTypeID]);
				}

				//Make sure we don't wind up with 2 images marked as primary
				if ($newEmail->IsPrimary)
				{
					if (is_set($this->_primaryEmail))
					{
						if ($this->_primaryEmail->EmailID <> $newEmail->EmailID)
						{
							$newEmail->IsPrimary = false;
						}					
					}
					else
					{
						$this->_primaryEmail = $newEmail;
					}
				}
	
				
				//Add it to the arrays
				$this->_emails[$newEmail->EmailID] = $newEmail;
			    $this->_emailsByType[$newEmail->EmailType->EmailTypeID] = $newEmail;
			}
			
		}
	}
	
	public function RemoveEmail($oldEmail)
	{
		if ($oldEmail instanceof Email)
		{
			//Clear the array element
			unset($this->_emails[$oldEmail->EmailID]);
			unset($this->_emailsByType[$oldEmail->EmailType->EmailTypeID]);

			//Check to see if this was the primary email, if so - clear it.
			if (is_set($this->_primaryEmail))
			{
				if ($this->_primaryEmail->EmailID == $oldEmail->EmailID)
				{
					$this->_primaryEmail = null;
				}
			}

			
		}

	}

	protected function ClearPrimaryFlags()
	{
		//Loop through the array, turning off all the IsPrimary flags
		foreach ($this->_emails as $tempEmail)
		{
			$tempEmail->IsPrimary = false;
		}
		
	}
	
	public function Export()
	{
		
		foreach($this->_emails as $tempEmail)
		{
			$this->_exportEntities[] = $tempEmail->Export();
		}
		
		return parent::Export();
		
	}
	
	
}



?>