<?php
/*
SendMail Class File

@package Sandstone
@subpackage Email
*/

class SendMail extends Mailer
{

	public function Send()
	{
		if ($this->_sendAsHTML == true)
		{
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		}

		if (is_set($this->_bccEmail))
		{
			$headers .= "Bcc: {$this->_bccEmail}\r\n";
		}

		mail("{$this->_toName} <{$this->_toEmail}>", $this->_subject, $this->Render(), $headers . "From: {$this->_senderName} <{$this->_senderEmail}>\r\n");

		//Comment out the one above and use this one for debugging with Mercury Mail Server
//		mail("{$this->_toEmail}", $this->_subject, $this->Render(), $headers . "From: {$this->_senderEmail}\r\n");
	}

}

?>
