<?php
/*
Application Class File

@package Sandstone
@subpackage Application
*/

Namespace::Using("Sandstone.Database");
Namespace::Using("Sandstone.Routing");
NameSpace::Using("Sandstone.SEO");
NameSpace::Using("Sandstone.User");

class Application extends Module
{
	protected $_license;
	protected $_currentUser;
	protected $_registry;

	protected $_cookie;
	protected $_session;
	protected $_routing;

	protected $_infoCache = Array();

	protected $_dbConnections = Array();

	protected $_isLicenseCheckComplete;

	protected function __construct()
	{

	}

	static public function Instance()
	{
		static $application;

		if (is_set($application) == false)
		{
			$application = new Application();
		}

		return $application;
	}

	static public function Run()
	{
		$App = Application::Instance();

		$returnValue = $App->ProcessRun();

		return $returnValue;
	}

	static public function RaiseEvent($PageName, $EventName, $Parameters)
	{

		$App = Application::Instance();

		$returnValue = $App->ProcessRaiseEvent($PageName, $EventName, $Parameters);

		return $returnValue;

	}

	static public function License()
	{
		$App = Application::Instance();

		return $App->License;
	}

	static public function CurrentUser()
	{
		$App = Application::Instance();

		return $App->CurrentUser;
	}

	static public function Cookie()
	{
		$App = Application::Instance();

		return $App->Cookie;
	}

	static public function SetCookie($Name, $Value, $LifeTime = null)
	{
		$App = Application::Instance();

		$App->ProcessSetCookie($Name, $Value, $LifeTime);
	}

	static public function ClearCookie($Name)
	{
		$App = Application::Instance();

		$App->ProcessClearCookie($Name);
	}

	static public function Session()
	{
		$App = Application::Instance();

		return $App->Session;
	}

	static public function SetSessionVariable($Name, $Value)
	{
		$App = Application::Instance();

		$App->ProcessSetSessionVariable($Name, $Value);
	}

	static public function ClearSessionVariable($Name)
	{
		$App = Application::Instance();

		$App->ProcessClearSessionVariable($Name);
	}

	static public function InfoCache()
	{
		$App = Application::Instance();

		return $App->InfoCache;
	}

	static public function SetInfoCacheVariable($Name, $Value)
	{
		$App = Application::Instance();

		$App->ProcessSetInfoCacheVariable($Name, $Value);
	}

	static public function ClearInfoCacheVariable($Name)
	{
		$App = Application::Instance();

		$App->ProcessClearInfoCacheVariable($Name);
	}

	static public function Registry()
	{
		$App = Application::Instance();

		return $App->Registry;
	}
	
	static public function DatabaseConnection($ConfigArray = null)
	{
		$App = Application::Instance();

		return $App->ProcessDatabaseConnection($ConfigArray);
	}

	static public function BaseURL()
	{
		$App = Application::Instance();

		return $App->BaseURL;
	}

	static public function RoutingPath()
	{
		$App = Application::Instance();

		return $App->RoutingPath;
	}

	static public function SecureURL()
	{
		$App = Application::Instance();

		return $App->SecureURL;
	}

	static public function CurrentBaseURL()
	{
		$App = Application::Instance();

		return $App->CurrentBaseURL;
	}

	static public function Redirect($url)
	{
		if (strtolower(substr($url, 0, 4)) == "http")
		{
			$location = $url;
		}
		else
		{
			$location = Routing::GetPageBaseURL() . $url;
		}

		// Do the redirect.
		header("Location: " . $location);

		die();
	}
	
	static public function CacheOutput($Seconds)
	{
		
	}

	static public function SelectAccount($AccountName)
	{

		$App = Application::Instance();

		$returnValue = $App->ProcessSelectAccount($AccountName);

		return $returnValue;
	}

	static public function IsLicenseCheckComplete()
	{
		$App = Application::Instance();

		return $App->IsLicenseCheckComplete;
	}

	static public function CurrentSystemMessage()
	{
		$returnValue = new SystemMessage();
		$returnValue->LoadCurrentMessage();

		if ($returnValue->IsLoaded == false)
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	/*
	License property
	*/
	public function getLicense()
	{
		if (is_set($this->_license) == false || $this->_license->IsLoaded == false)
		{
			$this->LicenseCheck();
		}

		return $this->_license;
	}

	/*
	CurrentUser property
	*/
	public function getCurrentUser()
	{
		return $this->_currentUser;
	}

	/*
	Cookie property
	*/
	public function getCookie()
	{
		return $this->_cookie;
	}

	/*
	Session property
	*/
	public function getSession()
	{
		return $this->_session;
	}

	/*
	InfoCache property

	@return Array
	 */
	public function getInfoCache()
	{
		return $this->_infoCache;
	}

	/*
	Registry property
	*/
	public function getRegistry()
	{
		if (is_set($this->_registry) == false)
		{
			$this->_registry = new Registry();
		}

		return $this->_registry;
	}

	/*
	BaseURL property
	*/
	public function getBaseURL()
	{
		/*
		if (strlen($_SERVER['HTTPS']) > 0 && Application::Registry()->DevMode <> 1)
		{
			$returnValue = $this->SecureURL;
		}
		else
		{
			$returnValue = $this->BaseNonSecureURL;
		}

		return $returnValue;
		*/

		return Routing::BaseURL();

	}

	public function getBaseNonSecureURL()
	{
		/*
		$returnValue = Application::Registry()->BaseURL;

		if (is_set($_REQUEST['subdomain']) && strlen($_REQUEST['subdomain']) > 0)
		{
			$returnValue = str_replace("www", $_REQUEST['subdomain'], $returnValue);
		}

		return $returnValue;
		*/

		return Routing::BaseNonSecureURL();
	}

	public function getSecureURL()
	{
		/*
		if (is_set(Application::Registry()->SecureURL))
		{
			// We've overwritten this setting in License.
			$returnValue = Application::Registry()->SecureURL;
		}
		else
		{
			$baseURL = $this->BaseNonSecureURL;
			$returnValue = substr_replace($baseURL, 'https', 0, 4);
		}

		return $returnValue;
		 */

		return Routing::SecureURL();
	}

	public function getCurrentBaseURL()
	{
		if ($_SERVER['SERVER_PORT'] == 80) // TODO: See if there's a better way to do this.
		{
			$returnValue = $this->getBaseURL();
		}
		else
		{
			$returnValue = $this->getSecureURL();
		}

		return $returnValue;
	}

	/*
	RoutingPath property

	The value after Base or Secure URL
	*/
	public function getRoutingPath()
	{
		return $this->_routing->RoutingURL;
	}

	/*
	IsLicenseCheckComplete property

	@return boolean
	 */
	public function getIsLicenseCheckComplete()
	{
		return $this->_isLicenseCheckComplete;
	}

	public function ProcessRun()
	{
		$this->SetupErrorHandling();

		try
		{
			$this->GeneralSystemCheck();
			$this->ApplicationSystemCheck();
			$this->BuildCookieAndSessionArrays();

			//Determine our routing for this request
			$EventParameters = Routing::ParseRequest();

			if (is_set($EventParameters['sslredirect']))
			{
				Application::Redirect($EventParameters['sslredirect']);
			}

			//Do we have a valid license?
			$eventResults = $this->LicenseCheck($EventParameters);

			//Make sure the License check didn't fire a 403 page
			if (is_set($eventResults) == false)
			{
				$eventResults = $this->ProcessRaiseEvent($EventParameters);
			}

			$eventResults->Flush();
		}
		catch (Exception $e)
		{
			//Redirect to the Error Display Page
			$fileType = $EventParameters['filetype'];

			$EventParameters =  Array("Exception" => $e);
			$EventParameters['page'] = "Error";
			$EventParameters['event']= "GET";
			$EventParameters['filetype'] = $fileType;
			$EventParameters['pageclass'] = "errorpage";

			$eventResults = $this->ProcessRaiseEvent($EventParameters);
			$eventResults->Flush();
		}

	}

	protected function GeneralSystemCheck()
	{
		/**
		 * Magic quotes leaves many security issues (particularly injection attacks) open.
		 * Halt software in the case magic_quotes is enabled.
		 */
		if (get_magic_quotes_gpc() == 1)
		{
			throw new InvalidSystemException("Magic_Quotes_GPC is enabled.  Please disable to continue.");
		}

		if (get_magic_quotes_runtime() == 1)
		{
			throw new InvalidSystemException("Magic_Quotes_Runtime is enabled.  Please disable to continue.");
		}
	}

	protected function ApplicationSystemCheck()
	{

		//Is an application specific system check defined?
		if (class_exists("SystemTest", true))
		{
			$sysTest = new SystemTest();
			$sysTest->PerformTest();
		}

	}

	protected function BuildCookieAndSessionArrays()
	{
		//Cookie
		$this->_cookie = new DIarray();
		foreach($_COOKIE as $name=>$value)
		{
			$this->_cookie[$name] = $value;
		}

		//Session
		$this->_session = new DIarray();
		foreach($_SESSION as $name=>$value)
		{
			$this->_session[$name] = $value;
		}

	}

	protected function LicenseCheck($EventParameters = Array())
	{
		//Single or multi account?
		if ($this->Registry->IsMultiAccount == 1)
		{
			//Multi-account system

			//Only do an account check if we are not headed for a login page
			if (Routing::GetIsUtilityFileRule() == false)
			{
				if (is_set($_SESSION['AccountID']) == false)
				{
					if (is_set($this->Cookie['DItoken']))
					{
						$currentAccountID = $this->LoadAccountIDfromToken($this->Cookie['DItoken']);

						if (is_set($currentAccountID))
						{
							$this->SetSessionVariable("AccountID", $currentAccountID);
						}
					}
					elseif (is_set($EventParameters['subdomain']))
					{
						$success = Application::SelectAccount($EventParameters['subdomain']);

						if ($success == false)
						{
							$returnValue = $this->Fire404response();
						}
					}
				}

				if (is_set($this->Session['AccountID']))
				{
					$this->_license = new License($_SESSION['AccountID']);

					if ($this->_license->IsValid == false)
					{
						throw new InvalidLicenseException("Account ID: {$_SESSION['AccountID']} does not have a valid license.");
					}
				}
				else
				{
					if (is_set($returnValue) == false)
					{
						//No account ID set, redirect to the login page.
						$returnValue = $this->HandleLoginOr403($EventParameters);
					}
				}
			}
		}
		else
		{
			//Single Account System

			//Do we have a specified account ID to use?
			if (is_set($this->Registry->AccountID))
			{
				$this->_license = new License($this->Registry->AccountID);
			}
			else
			{
				//We default to AccountID 1
				$this->_license = new License(1);
			}

			if ($this->_license->IsValid == false)
			{
				throw new InvalidLicenseException("Account ID: {$_SESSION['AccountID']} does not have a valid license.");
			}
		}

		$this->_isLicenseCheckComplete = true;

		return $returnValue;

	}

	protected function HandleLoginOr403($EventParameters)
	{

		switch ($EventParameters['filetype'])
		{
			case "htm":
				$this->LoginRedirect($EventParameters);
				break;

			case "rss":
			case "xml":
			case "csv":
			case "txt":

				if (is_set($this->_license))
				{
					$EventParameters['filetype'] = "AUTH301";
					$this->LoginRedirect($EventParameters);
				}
				else
				{
					$returnValue = $this->Fire403response();
				}
				break;
				
			case "term":
				// Don't fire 403, instead just select account 1
				$this->_license = new License(1);
				break;

			default:
				$returnValue = $this->Fire403response();
		}

		return $returnValue;

	}

	protected function LoginRedirect($EventParameters)
	{
		Application::SetSessionVariable("TargetRoutingString", $EventParameters['routing']);

		Application::Redirect(Routing::BuildURLbyRule("login", Array(), $EventParameters['filetype']));
	}

	protected function Fire403response()
	{
		$eventParameters['page'] = "Error403";
		$eventParameters['pageclass'] = "Error403Page";
		$eventParameters['filetype'] = "htm403";
		$eventParameters['event'] = "GET";

		$targetPage = new $eventParameters['pageclass'] ();

		$returnValue = $targetPage->RaiseEvent($eventParameters);

		return $returnValue;
	}

	protected function Fire404response()
	{
		$eventParameters['page'] = "Error404";
		$eventParameters['pageclass'] = "Error404Page";
		$eventParameters['filetype'] = "htm404";
		$eventParameters['event'] = "GET";

		$targetPage = new $eventParameters['pageclass'] ();

		$returnValue = $targetPage->RaiseEvent($eventParameters);

		return $returnValue;
	}

	protected function LoadAccountIDfromToken($Token)
	{

		$query = new Query();

		$query->SQL = "	SELECT	AccountID
						FROM	core_UserToken
						WHERE	Token = {$query->SetTextField($Token)}";

		$query->Execute();

		if ($query->SelectedRows > 0)
		{
			$returnValue = $query->SingleRowResult['AccountID'];
		}

		return $returnValue;
	}

	public function ProcessRaiseEvent($EventParameters)
	{

		//If there is a logged in user, load them
		$this->LoadCurrentUser();

		//Create an object of the page's type
		$targetPage = new $EventParameters['pageclass'] ();

		//Check authorization.
		$returnValue = $this->AuthenticateUser($targetPage, $EventParameters);

		if (is_set($returnValue) == false)
		{
			//Everything is fine, process the event
			$returnValue = $targetPage->RaiseEvent($EventParameters);
		}

		return $returnValue;

	}

	protected function LoadCurrentUser()
	{

		if (is_set($this->Cookie['DItoken']))
		{
			if (is_set($this->_license))
			{
				$this->_currentUser = new User($this->Cookie['DItoken']);

				if ($this->_currentUser->IsLoaded == false)
				{
					$this->_currentUser = null;
				}
			}
			else
			{
				if (Routing::GetIsUtilityFileRule() == false)
				{
					//Somehow we have a cookie, but it didn't reslove to give us an account.  
					//We can't load a user, so clear the cookie, yank it from the session, and don't load a user.
					$this->ProcessClearCookie('DItoken');
					$this->ProcessClearSessionVariable('DItoken');
				}
			}
		}

		if (is_set($this->_currentUser) == false && is_set($this->Session['DItoken']))
		{
			$this->_currentUser = new User($this->Session['DItoken']);

			if ($this->_currentUser->IsLoaded == false)
			{
				$this->_currentUser = null;
			}
		}

	}

	protected function AuthenticateUser($TargetPage, $EventParameters)
	{
		//First, does this page require a logged in user?
		if ($TargetPage->IsLoginRequired)
		{
			//Do we have a logged in user?
			if (is_set($this->_currentUser))
			{
				//Are there required roles?
				if (count($TargetPage->AllowedRoleIDs) > 0)
				{
					//If the current user is in the admin role,
					//we automatically pass.
					if ($this->_currentUser->IsInRole(new Role(2)) == false)
					{
						$roleFound == false;

						foreach($TargetPage->AllowedRoleIDs as $tempID)
						{
							if ($this->_currentUser->IsInRole(new Role($tempID)))
							{
								$roleFound = true;
							}
						}

						if ($roleFound == false)
						{
							$returnValue = $this->Fire403response();
						}
					}
				}
			}
			else
			{
				//No logged in user which is required - force to the login page.
				$returnValue = $this->HandleLoginOr403($EventParameters);
			}
		}

		return $returnValue;
	}

	public function ProcessSelectAccount($AccountName)
	{

		$this->_license = new License();

		$this->_license->LookupByName($AccountName);

		$returnValue = $this->_license->IsValid;

		if ($returnValue == true)
		{
			$this->ProcessSetSessionVariable("AccountID", $this->_license->AccountID);
		}

		return $returnValue;

	}

	public function ProcessSetCookie($Name, $Value, $LifeTime = null)
	{

		if (strlen($Name) > 0)
		{
			if (is_set($LifeTime))
			{
				$expires = time() + $LifeTime;
			}
			else
			{
				//Default to 30 days
				$expires = time()+60*60*24*30;
			}
			
			$domain = $this->DetermineCookieDomain();

			setcookie($Name, $Value, $expires, "/", $domain);

			$this->_cookie[$Name] = $Value;
		}

		return $returnValue;

	}

	public function ProcessClearCookie($Name)
	{
		if (strlen($Name) > 0)
		{
			$domain = $this->DetermineCookieDomain();

			setcookie($Name,"",time() - 3600,"/", $domain);
			unset($this->_cookie[$Name]);
		}
	}

	protected function DetermineCookieDomain()
	{
    $replacements = array("http://", "https://");
		$returnValue = str_replace($replacements, "", Application::BaseURL());

		if (substr_count($returnValue, "/") > 0)
		{
			$returnValue = substr($returnValue, 0, strpos($returnValue, "/"));
		}

		return $returnValue;
	}

	public function ProcessSetSessionVariable($Name, $Value)
	{
		if (strlen($Name) > 0)
		{
			$this->_session[$Name] = $Value;
			$_SESSION[$Name] = $Value;
		}
	}

	public function ProcessClearSessionVariable($Name)
	{
		if (strlen($Name) > 0)
		{
			unset($this->_session[$Name]);
			unset($_SESSION[$Name]);
		}
	}

	public function ProcessSetInfoCacheVariable($Name, $Value)
	{
		if (strlen($Name) > 0)
		{
			$this->_infoCache[$Name] = $Value;
		}
	}

	public function ProcessClearInfoCacheVariable($Name)
	{
		if (strlen($Name) > 0)
		{
			unset($this->_infoCache[$Name]);
		}
	}

	public function ProcessDatabaseConnection($ConfigArray = null)
	{

		if (is_array($ConfigArray) || $ConfigArray instanceof DIarray)
		{
			//We were passed an array - use it.
			$dbConfig = $ConfigArray;
		}
		else if (is_set($ConfigArray))
		{
			//We were passed a config name, check to see if we have settings
			$dbConfig = Application::Registry()->$ConfigArray;

			if (is_set($dbConfig) == false)
			{
				//Didn't find one, default to the application
				$dbConfig = Application::Registry()->ApplicationDB;
			}

		}
		else
		{
			//None specified, use the application
			$dbConfig = Application::Registry()->ApplicationDB;
		}

		//Build the key for this connection
		$key = $dbConfig['DBhost'] . "|" . $dbConfig['DBname'] . "|" . $dbConfig['DBuser'];

		//Do we have an active connection for this Config?
		if (array_key_exists($key, $this->_dbConnections) && $this->_dbConnections[$key] instanceof DIConnection)
		{
			$returnValue = $this->_dbConnections[$key];
		}
		else
		{
			//Supress warnings here, since we are going to check for an error manually,
			//and throw an exception if something has gone wrong
			@ $returnValue = new DIConnection($dbConfig['DBhost'], $dbConfig['DBuser'], $dbConfig['DBpass'], $dbConfig['DBname']);

			$errorNumber = mysqli_connect_errno();

			if (is_set($errorNumber) && $errorNumber > 0)
			{
				//Some database connection error!
				$msg = mysqli_connect_error();
				throw new DatabaseConnectionException($msg, $errorNumber, $dbConfig);
			}
			else
			{
				$this->_dbConnections[$key] = $returnValue;
			}

		}

		return $returnValue;

	}

	protected function SetupErrorHandling()
	{
		// Setup Error Reporting Level, Multiple Choices
		# error_reporting (E_ALL);
		error_reporting (E_ALL & ~ (E_NOTICE | E_USER_NOTICE));

		set_error_handler ('DIErrorHandler');
	}

}
?>
