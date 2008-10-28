<?php
/**
 * Application Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

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
	
	protected $_dbConnections = Array();	
	
	protected function __construct()
	{
		//Build a dynamic "magic" namespace for the application's
		//pages.
		if (file_exists("namespaces/application.pages.ns"))
		{
			NameSpace::Using("Application.Pages");	
		}
		else 
		{
			$this->BuildApplicationPageNamespace();	
		}
		
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

	static public function DBconfig()
	{
		$App = Application::Instance();	
		
		return $App->License->DBconfigArray;
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
	
	static public function Registry()
	{
		$App = Application::Instance();	
		
		return $App->Registry;		
	}
	
	static public function DatabaseConnection($ConfigArray)
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
			$App = Application::Instance();
			$location = $App->BaseURL() . $url;
		}
		
		// Do the redirect.
		header("Location: " . $location);
	}
	
	/**
	 * License property
	 * 
	 * @return 
	 */
	public function getLicense()
	{
		return $this->_license;
	}
	
	/**
	 * CurrentUser property
	 * 
	 * @return 
	 */
	public function getCurrentUser()
	{
		return $this->_currentUser;
	}
	
	/**
	 * Cookie property
	 * 
	 * @return 
	 */
	public function getCookie()
	{
		return $this->_cookie;
	}
	
	/**
	 * Session property
	 * 
	 * @return 
	 */
	public function getSession()
	{
		return $this->_session;
	}
	
	/**
	 * Registry property
	 * 
	 * @return 
	 */
	public function getRegistry()
	{
		if (is_set($this->_registry) == false)
		{
			$this->_registry = new Registry();
		}
		
		return $this->_registry;
	}

	/**
	 * BaseURL property
	 * 
	 * @return string
	 */	
	public function getBaseURL()
	{
		
		if (is_set(Application::License()->BaseURL))
		{
			// We've overwritten this setting in License.
			$returnValue = Application::License()->BaseURL;
		}
		else
		{
			$returnValue = "http://{$_SERVER['HTTP_HOST']}";
		
			$uriCount = substr_count($_SERVER['REQUEST_URI'], "/");
			$fullPathCount = substr_count($_SERVER['SCRIPT_FILENAME'], "/");
			$levelsToRoot =  $uriCount - ($fullPathCount - 3);

			$uriArray = explode("/", $_SERVER['REQUEST_URI']);
			$loopCount = (Count($uriArray) - 1) - $levelsToRoot;
				
			for ($i=1; $i < $loopCount; $i++)
			{
				 $returnValue .= "/" . $uriArray[$i];
			}
		
			$returnValue .= "/";
		}
		
		
		return $returnValue;
	}
	
	public function getSecureURL()
	{
		if (is_set(Application::License()->SecureURL))
		{
			// We've overwritten this setting in License.
			$returnValue = Application::License()->SecureURL;
		}
		else
		{
			$baseURL = $this->BaseURL;
			$returnValue = substr_replace($baseURL, 'https', 0, 4);
		}
		
		
		return $returnValue;
	}

	public function getCurrentBaseURL()
	{
		if ($_SERVER['SERVER_PORT'] == 80)
		{
			$returnValue = $this->getBaseURL();
		}
		else
		{
			$returnValue = $this->getSecureURL();
		}
		
		return $returnValue;
	}
	
	// The value after Base or Secure URL
	public function getRoutingPath()
	{
		return $this->_routing->RoutingURL;
	}

	public function ProcessRun()
	{
		$this->SetupErrorHandling();
		
		try 
		{			
			$this->GeneralSystemCheck();
			$this->LicenseCheck();
			$this->ApplicationSystemCheck();
			$this->BuildCookieAndSessionArrays();
			$EventParameters = $this->BuildParametersArray();
			
			$eventResults = $this->ProcessRaiseEvent($EventParameters['page'], $EventParameters['event'], $EventParameters);
			$eventResults->Flush();
			
		}
		catch (Exception $e)
		{
			//Redirect to the Error Display Page
			$EventParameters =  Array("Exception" => $e);
			$eventResults = $this->ProcessRaiseEvent('Error', 'Load', $EventParameters);
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
		$this->_cookie = Array();
		foreach($_COOKIE as $name=>$value)
		{
			$this->_cookie[$name] = $value;
		}
		
		//Session
		$this->_session = Array();
		foreach($_SESSION as $name=>$value)
		{
			$this->_session[$name] = $value;
		}

	}
	
	protected function BuildParametersArray()
	{
		//Pull everything from the GET
		foreach ($_GET as $key=>$value)
		{
			$returnValue[strtolower($key)] = $value;
		}
			
		// Parse the Routing URL into the Event Parameters
		$this->_routing = new Routing(strtolower($returnValue['routing']));
		$routingParameters = $this->_routing->RouteURL();
		
		if (is_array($routingParameters))
		{
			foreach ($routingParameters as $key=>$value)
			{
				$returnValue[strtolower($key)] = $value;
			}
		}
			
		if (count($_FILES))
		{
			//Pull everything from the FILE
			foreach ($_FILES as $key=>$value)
			{
				$returnValue[strtolower($key)] = $value;
			}			
		}
				
		//Pull everything from the POST.  Any matches overwrite GET values.
		foreach ($_POST as $key=>$value)
		{
			$returnValue[strtolower($key)] = $value;
		}

		//Set the flag to indicate we are in a post situation
		if (count($_POST) > 0 || count($_FILES) > 0)
		{
			$isPost = true;
		}
		else 
		{
			$isPost = false;
		}
		
		$returnValue['IsPost'] = $isPost;
		
		//Make sure there is a page.  If none is found, the default is "Home" 
		if (is_set($returnValue['page']) == false)
		{
			$returnValue['page'] = $this->_routing->DefaultPage;
		}
		
		//Make sure there is an event.  If none is found, the default is "Load" or "Post" depending
		//on our post state.
		if (is_set($returnValue['event']) == false)
		{
			if ($isPost)
			{
				$returnValue['event'] = "Post";
			}
			else 
			{
				$returnValue['event'] = "Load";
			}
		}
		
		return $returnValue;
	}
	
	protected function LicenseCheck()
	{			
		$this->_license = new License($_SESSION['AccountID']);
		
		if ($this->_license->IsValid == false)
		{
			throw new InvalidLicenseException("Account ID: {$_SESSION['AccountID']} does not have a valid license.");
		}
		
	}
	
	protected function FindPage($PageName, &$EventParameters)
	{
		//Force the given page name back into the event parameters
		$EventParameters['page'] = $PageName;
		
		$pageClassName = $PageName . "Page";
		
		if (class_exists($pageClassName, true))
		{
			$returnValue = new $pageClassName();
		}
		else 
		{
			//Look for an SEO page with this name
			try 
			{
				$seoPage = new SEOpage($PageName);	
				$isSEOpageFound = $seoPage->IsLoaded;
			}
			catch (Exception $e)
			{
				$isSEOpageFound = false;
			}
			
			//Did we find one?
			if ($isSEOpageFound)
			{
				$pageClassName = $seoPage->PageFileName . "Page";
				$EventParameters['SEOpage'] = $seoPage;
				
				//We have an SEO page, do we have it's specific page class?
				if (class_exists($pageClassName, true))
				{
					$returnValue = new $pageClassName();
				}
				else 
				{
					//404 - Couldn't find the page class for the SEO page.
					$returnValue = null;
				}
			}
			else 
			{
				//404 - no SEO page by this name either
				$returnValue = null;
			}
		}

		
		return  $returnValue;
	}
	
	protected function AuthenticateUser($TargetPage)
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
					if (array_key_exists(2, $this->_currentUser->Roles))
					{
						$returnValue = true;
					}
					else 
					{
						//See if this user has any of the required roles
						$returnValue = false;
						
						foreach($TargetPage->AllowedRoleIDs as $tempID)
						{
							if (array_key_exists($tempID, $this->_currentUser->Roles))
							{
								$returnValue = true;
							}
						}
					}
				}
				else 
				{
					//There are no specified roles, so every logged
					//in user has access
					$returnValue = true;
				}
			}
			else 
			{
				//No logged in user which is required.
				$returnValue = false;
			}
		}
		else 
		{
			//Since this page doesn't require a logged in user,
			//it passes authentication.
			$returnValue = true;	
		}
		
		return $returnValue;
	}
	
	public function ProcessRaiseEvent($PageName, $EventName, &$EventParameters)
	{
		//Find the page to work with.  
		$targetPage = $this->FindPage($PageName, $EventParameters);
		
		if (is_set($targetPage))
		{
			//If there is a logged in user, load them
			$this->LoadCurrentUser();
			
			//Check authorization.
			$isAuthenticated = $this->AuthenticateUser($targetPage);
			
			if ($isAuthenticated)
			{
				//Everything is fine, process the event
				$returnValue = $targetPage->RaiseEvent($EventName, $EventParameters);
			}
			else 
			{
				//Current User Credentials are not authorized for this action
				$EventParameters['page'] = $PageName;
				$EventParameters['event'] = $EventName;
				$returnValue = $targetPage->RaiseEvent("Unauthorized", $EventParameters);				
			}			
		}
		else 
		{
			$targetPage = new BasePage();
			$returnValue = $targetPage->RaiseEvent("Page404", $EventParameters);
		}
		
		return $returnValue;
		
	}
	
	protected function LoadCurrentUser()
	{
				
		if (is_set($this->Cookie['DItoken']))
		{
			$token = $this->Cookie['DItoken'];
		}
		elseif (is_set($this->Session['DItoken']))
		{
			$token = $this->Session['DItoken'];
		}
	
		if (is_set($token))
		{
			$this->_currentUser = new User($token);
		
			//If we didn't load one successfully from that token,
			//clear the field
			if ($this->_currentUser->IsLoaded == false)		
			{
				$this->_currentUser = null;
			}
		}
		else 
		{
			$this->_currentUser = null;
		}
		
	}
	
	protected function BuildApplicationPageNamespace()
	{
		$fileList = glob("pages/*.page.php");
		
		NameSpace::AddFiles($fileList);
		
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
			
			setcookie($Name, $Value, $expires, "/");
			$this->_cookie[$Name] = $Value;
		}
		
	}
	
	public function ProcessClearCookie($Name)
	{
		
		if (strlen($Name) > 0)
		{
			setcookie($Name,"",time() - 5000,"/");
			unset($this->_cookie[$Name]);
		}

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
	
	public function ProcessDatabaseConnection($ConfigArray)
	{
		
		//Build the key for this connection
		$key = $ConfigArray['DBhost'] . "|" . $ConfigArray['DBname'] . "|" . $ConfigArray['DBuser'];
		
		//Do we have an active connection for this Config?
		if (array_key_exists($key, $this->_dbConnections))
		{
			$returnValue = $this->_dbConnections[$key];
		}
		else 
		{
			$conn = NewADOConnection('mysql');
			$conn->Connect($ConfigArray['DBhost'], $ConfigArray['DBuser'], $ConfigArray['DBpass'], $ConfigArray['DBname']);
			
			$this->_dbConnections[$key] = $conn;
			$returnValue = $conn;
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