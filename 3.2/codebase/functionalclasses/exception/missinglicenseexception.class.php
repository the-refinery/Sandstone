<?php
/*
Missing License Exception Class

@package Sandstone
@subpackage Exception
*/

class MissingLicenseException extends DIException
{

	public function __toString()
	{

		$url = Routing::GetRequestedURL();
		$session = Application::Session()->StringDump();

		$returnValue .=	 "<h2>Missing AccountID In Query</h2>";
		$returnValue .= "<h3>Requested URL: {$url}</h3>";
		$returnValue .= "<h3>Session: {$session}</h3>";
		$returnValue .= "<h3>Query: {$this->getMessage()}</h3>";

		$returnValue .= $this->DItraceAsString();

		return $returnValue;
	}

}
?>
