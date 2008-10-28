<?php
/**
 * SendMail Class File
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

/**
 * Uses the PHP Mail function to send emails.
 * 
 * @todo Add functionality in send for BCC and CC
 *
 */
class SendMail extends Module 
{
	public function Send($EmailMessage)
	{
		if ($EmailMessage instanceof EmailMessage && $EmailMessage->IsOKtoSend)
		{		
			// Build From String
			$fromInfo  = $EmailMessage->FromDisplayName . " <" . $EmailMessage->FromEmail->Address . ">";

			//Loop through the To info, and send each email.
			foreach ($EmailMessage->ToEmails as $To)
			{
				//Build the To string
				$toInfo = $To[0] . " <" . $To[1]->Address . ">";

				//Build the email header

				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";				
				$headers .= 'To: ' . $toInfo . "\r\n";
				$headers .= 'From: ' . $fromInfo . "\r\n";
				
				//Setup the HTML tags for the message
				$formattedMessage = '<html><head></head><body>';
				
				if ($EmailMessage->IsPreformatted)
				{
					$formattedMessage .= '<pre>';	
				}
				
				//Add the actual message body
				$formattedMessage .= "\n" . $EmailMessage->Message . "\n";
				
				//Close HTML tags
				if ($EmailMessage->IsPreformatted)
				{
					$formattedMessage .= '</pre>';
				}
				
				$formattedMessage .= '</body></html>';
				
				//Send the email
				mail($toInfo, $EmailMessage->Subject, $formattedMessage, $headers);
			}
			
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}
		
		return $returnValue;
		
	}
}

?>