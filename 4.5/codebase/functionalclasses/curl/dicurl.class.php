<?php
/*
Curl Class File

@package Sandstone
@subpackage Curl
*/

class DICurl extends Module
{
	/*
	Curl handler
	*/
	protected $_curlHandler;

	/*
	Verbose Output
	*/
	protected $_debug = false;
	
	protected $_errorMessage;


	public function __construct($debug = false)
	{
		$this->_debug = $debug;

		$this->_curlHandler = curl_init();

		/* 
		Setup default settings for all requests
		- Throw errors
		- Allow redirects
		*/
		curl_setopt($this->_curlHandler, CURLOPT_FAILONERROR, true);
		curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, true);
	}

	function SetupCredentials($Username, $Password)
	{
		curl_setopt($this->_curlHandler, CURLOPT_USERPWD, "{$Username}:{$Password}");
		curl_setopt($this->_curlHandler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}

	function SetupReferrer($ReferrerURL)
	{
		curl_setopt($this->_curlHandler, CURLOPT_REFERER, $ReferrerURL);
	}

	function SetupUserAgent($UserAgent)
	{
		curl_setopt($this->_curlHandler, CURLOPT_USERAGENT, $UserAgent);
	}

	function SetupProxy($Proxy)
	{
		curl_setopt($this->_curlHandler, CURLOPT_PROXY, $Proxy);
	}
	
	function SetupHeaderType($Header)
	{
		curl_setopt($this->_curlHandler, CURLOPT_HTTPHEADER, Array("Content-Type: {$Header}"));
		curl_setopt($this->_curlHandler, CURLOPT_HTTPHEADER, Array("Accept: {$Header}"));
	}
	
	/*
	Set to receive output headers in all output functions
		- true or false
	*/
	function IncludeResponseHeader($Value)
	{
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, $Value);
	}

	/*
	Send post data to target URL	 
    */
	function SendPostData($URL, $PostData, $IP=null, $Timeout=10)
	{
		// Setup URL
		curl_setopt($this->_curlHandler, CURLOPT_URL, $URL);
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);

		// Bind to IP Address
		if (isset($IP))
		{
			if ($this->_debug)
			{
				echo "Binding to ip $IP\n";
			}
			
			curl_setopt($this->_curlHandler,CURLOPT_INTERFACE,$IP);
		}

		// Setup Timeout
		curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, $Timeout);

		// Use POST as Method
		curl_setopt($this->_curlHandler, CURLOPT_POST, true);

		// Setup Post String
		$postParameters = array();
		
		if (is_array($PostData) == false)
		{
			return false;
		}
		
		foreach ($PostData as $key => $value)
		{
			$postParameters[] = urlencode($key) . "=" . urlencode($value);
		}

		$postString = implode("&", $postParameters);

		if ($this->_debug)
		{
			echo "Post String: {$postString}\n";
		}

		curl_setopt($this->_curlHandler, CURLOPT_POSTFIELDS, $postString);


		// Send the Request
		$result = curl_exec($this->_curlHandler);

		if (curl_errno($this->_curlHandler))
		{
			if ($this->_debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " . curl_errno($this->_curlHandler) . "\n";
				echo "Error message: " . curl_error($this->_curlHandler) . "\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
	}

	/*
	Fetch data from target URL	 
    */
	function FetchURL($URL, $IP=null, $Timeout=5)
	{
		// Setup URL
		curl_setopt($this->_curlHandler, CURLOPT_URL, $URL);
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);

		// Use GET as Method
		curl_setopt($this->_curlHandler, CURLOPT_HTTPGET, true);

		//bind to specific ip address if it is sent trough arguments
		if (isset($IP))
		{
			if ($this->_debug)
			{
				echo "Binding to ip {$IP}\n";
			}
			
			curl_setopt($this->_curlHandler, CURLOPT_INTERFACE, $IP);
		}

		// Setup Timeout
		curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, $Timeout);

		// Send Curl Request
		$result = curl_exec($this->_curlHandler);

		if(curl_errno($this->_curlHandler))
		{
			if($this->_debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->_curlHandler) ."\n";
				echo "Error message: " .curl_error($this->_curlHandler)."\n";
			}

			return false;
		}
		else
		{
			return $result;
		}
	}

	function StoreCookies($CookieFile)
	{
		// use cookies on each request (cookies stored in $cookie_file)
		curl_setopt ($this->_curlHandler, CURLOPT_COOKIEJAR, $CookieFile);
	}

	function getEffectiveURL()
	{
		return curl_getinfo($this->_curlHandler, CURLINFO_EFFECTIVE_URL);
	}

	function getHTTPResponseCode()
	{
		return curl_getinfo($this->_curlHandler, CURLINFO_HTTP_CODE);
	}

	function getErrorMessage()
	{
		$err = "Error number: " .curl_errno($this->_curlHandler) ."\n";
		$err .="Error message: " .curl_error($this->_curlHandler)."\n";

		return $err;
	}
}
?>