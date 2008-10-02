<?php
/*
Email Mailer Class File

@package Sandstone
@subpackage Email
*/

class Mailer extends Renderable 
{
	protected $_toName;
	protected $_toEmail;
	protected $_senderName;
	protected $_senderEmail;
	protected $_subject;
	
	protected $_sendAsHTML = false;
	
	public function getToName()
	{
		return $this->_toName;
	}
	
	public function setToName($Value)
	{
		$this->_toName = $Value;
	}
	
	public function getToEmail()
	{
		return $this->_toName;
	}
	
	public function setToEmail($Value)
	{
		$this->_toEmail = $Value;
	}
	
	public function getSenderName()
	{
		return $this->_senderName;
	}
	
	public function setSenderName($Value)
	{
		$this->_senderName = $Value;
	}
	
	public function getSenderEmail()
	{
		return $this->_senderEmail;
	}
	
	public function setSenderEmail($Value)
	{
		$this->_senderEmail = $Value;			
	}
	
	public function getSubject()
	{
		return $this->_subject;
	}
	
	public function setSubject($Value)
	{
		$this->_subject = $Value;
	}
	
	public function getSendAsHTML()
	{
		return $this->_sendAsHTML;
	}
	
	public function setSendAsHTML($Value)
	{
		$this->_sendAsHTML = $Value;
	}
	
	// Overwritten in sub classes
	public function Send()
	{
	}
	
	public function Render()
	{		
		$this->_template->RequestFileType = "email";
		
		$returnValue = parent::Render();
		
		return $returnValue;
	}
		
}

?>