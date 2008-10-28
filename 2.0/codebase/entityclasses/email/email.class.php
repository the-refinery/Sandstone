<?php
/**
 * Email Class
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

class Email extends Module 
{
	protected $_emailID;
	protected $_address;
	protected $_emailType;
	protected $_isPrimary;

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
	 * EmailID property
	 * 
	 * @return int
	 */
	public function getEmailID()
	{
		return $this->_emailID;
	}
	
	/**
	 * Address property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getAddress()
	{
		return $this->_address;
	}
	
	public function setAddress($Value)
	{
	
		if (is_set($Value))
		{
			$this->_address = substr(trim($Value), 0, DB_EMAIL_MAX_LEN);	
		}
		else
		{
			$this->_address = $Value;
		}	
	}
	
	/**
	 * EmailType property
	 * 
	 * @return emailType
	 * 
	 * @param emailType $Value
	 */
	public function getEmailType()
	{
		return $this->_emailType;
	}

	public function setEmailType($Value)
	{
		if ($Value instanceof EmailType || is_set($Value) == false)
		{
			$this->_emailType = $Value;
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
	
	public function Load($dr)
	{
		$this->_emailID = $dr['EmailID'];
		$this->_address = $dr['Address'];
		
		if (is_set($dr['EmailTypeID']))
		{
			$this->_emailType = new EmailType($dr['EmailTypeID']);	
		}
		
		$this->_isPrimary = Connection::GetBooleanField($dr['IsPrimary']);

		if (is_set($dr['EmailTypeID']))
		{
			if ($this->_emailType->IsLoaded)
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


		$selectClause = self::GenerateBaseSelectClause();
		$fromClause = self::GenerateBaseFromClause();
		$whereClause = "WHERE a.EmailID = {$ID}";

		$query = $selectClause . $fromClause . $whereClause;
		
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
		
		if (is_set($this->_emailID) OR $this->_emailID > 0)
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
		
		$query = "	INSERT INTO core_EmailMaster
					(
						Address
					)
					VALUES
					(
						{$conn->SetNullTextField($this->_address)}
					)";
		
		$conn->Execute($query);
		
		
		//Get the new ID
		$query = "SELECT LAST_INSERT_ID() newID ";
		
		$dr = $conn->GetRow($query);
		
		$this->_emailID = $dr['newID'];
		
	}
	
	protected function SaveUpdateRecord($conn)
	{
				
		$query = "	UPDATE core_EmailMaster SET
						Address = {$conn->SetNullTextField($this->_address)}
					WHERE EmailID = {$this->_emailID}";
		
		$conn->Execute($query);
		
	}

	public static function CheckValidEmail($Email)
	{
		if( preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $Email))
		{
			$returnValue = true;
		}     
		else 
		{
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	public function ValidateEmailFormat($Control)
	{
		if (!$this->CheckValidEmail($Control->Value)) 
		{
			if (is_set($Control->Label->Text))
			{
				$name = $Control->Label->Text;
			}
			else
			{
				$name = $Control->Name;
			}
			
			$returnValue = $name . " is not a valid email address!";
			
		}
		
		return $returnValue;
	}
	
	public function Export()
	{
		$this->_exportEntities[] = $this->CreateXMLentity("address", $this->_address);
		$this->_exportEntities[] = $this->CreateXMLentity("type", $this->_emailType->Description);	
		$this->_exportEntities[] = $this->CreateXMLentity("isprimary", $this->_isPrimary, true);
		
		return parent::Export();
	}

	/**
	 *
	 * Static Query Functions
	 *
	 */
	static public function GenerateBaseSelectClause()
	{
		$returnValue = "	SELECT	a.EmailID,
										a.Address ";

		return $returnValue;
	}

	static public function GenerateBaseFromClause()
	{
		$returnValue = "	FROM	core_EmailMaster a ";

		return $returnValue;
	}

}

?>