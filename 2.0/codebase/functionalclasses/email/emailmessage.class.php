<?php
/**
 * Email Message Class File
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

SandstoneNamespace::Using("Sandstone.Email");

class EmailMessage extends Module
{
	/**
	 * A 3 dimensional array of display names with email objects with TO: addresses
	 * 
	 * [code]$toEmails[] = array("DisplayName",$emailObject,$type);[/code]
	 * 
	 * Possible options for type is "TO", "CC", or "BCC"
	 *
	 * @var array($display,$emailObject,"TO")
	 */
	protected $_toEmails = array();
	protected $_fromDisplayName;
	protected $_fromEmail;
	protected $_subject;
	protected $_message;
	protected $_isPreFormatted;
	
	protected $_isSent;
	
	/**
	 * FromDisplayName property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getFromDisplayName()
	{
		return $this->_fromDisplayName;
	}
	
	public function setFromDisplayName($Value)
	{
		$this->_fromDisplayName = $Value;
	}

	/**
	 * FromEmail property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getFromEmail()
	{
		return $this->_fromEmail;
	}
	
	public function setFromEmail($Value)
	{
		if ($Value instanceof Email)
		{
			$this->_fromEmail = $Value;
		}
	}
	
	/**
	 * ToEmails property
	 * 
	 * @return array
	 */
	public function getToEmails()
	{
		return $this->_toEmails;
	}
	
	/**
	 * Subject property
	 * 
	 * @return string
	 */
	public function getSubject()
	{
		return $this->_subject;
	}
	
	public function setSubject($Value)
	{
		$this->_subject = $Value;
	}
	
	/**
	 * Message property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getMessage()
	{
		return $this->_message;
	}
	
	public function setMessage($Value)
	{
		$this->_message = $Value;
	}
	
	/**
	 * IsPreformatted property
	 * 
	 * @return boolean
	 * 
	 * @param boolean $Value
	 */
	public function getIsPreformatted()
	{
		return $this->_isPreFormatted;
	}
	
	public function setIsPreformatted($Value)
	{
		$this->_isPreFormatted = $Value;
	}
	
	/**
	 * IsOKtoSend property
	 * 
	 * @return boolean
	 */
	public function getIsOKtoSend()
	{
		if (is_set($this->_fromDisplayName) &&
			is_set($this->_fromEmail) && 
			is_set($this->_subject) &&
			is_set($this->_message)  &&
			count($this->_toEmails) > 0 &&
			$this->_isSent != true)
		{
			$returnValue = true;		
		}
		else 
		{
			$returnValue = false;
		}
		
		return $returnValue;

	}
	
	/**
	 * IsSent property
	 * 
	 * @return boolean
	 */
	public function getIsSent()
	{
		return $this->_isSent;
	}

	public function AddRecipient($displayName, $emailObject, $type = "TO")
	{
		$type = strtoupper($type);

		if (is_set($displayName) && ($type == "TO" || $type == "CC" || $type = "BCC") && $emailObject instanceof Email)
		{
			$this->_toEmails[] = array($displayName, $emailObject, $type);
		}

	}

	public function Send()
	{

		$sender = new SendMail();

		$returnValue = $sender->Send($this);
	
		$this->_isSent = $returnValue;
		
		return $returnValue;
	}
}

?>