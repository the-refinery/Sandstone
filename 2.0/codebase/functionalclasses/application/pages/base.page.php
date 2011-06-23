<?php
/**
 * Page Class File
 * @package Sandstone
 * @subpackage Application
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 */

SandstoneNamespace::Using("Sandstone.SEO");
SandstoneNamespace::Using("Sandstone.Smarty");
SandstoneNamespace::Using("Sandstone.Traffic");
SandstoneNamespace::Using("Sandstone.Utilities.Encryption");

class BasePage extends ControlContainer
{
	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_isTrafficLogged = true;
	protected $_forceSSL = false;
	
	protected $_requestedURL;

	protected $_smartyTemplateName = null;

	protected $_diSmarty;
	protected $_onLoadFunction;

	protected $_forms;
	protected $_postedForm;

	protected $_activePageName;

	public function __construct()
	{
		parent::__construct();

		$this->_forms = new DIarray();

		// Flash Message Control
		$this->FlashMessage = new FlashMessageControl();

		$this->_isRawValuePosted = false;
	}

	public function __get($Name)
	{
		if (array_key_exists($Name, $this->_forms))
		{
			$returnValue = $this->_forms[$Name];
		}
		else
		{
			//If we don't have it as a form, let our parent getter handle it.
			$returnValue = parent::__get($Name);
		}

		return $returnValue;
	}

	public function __set($Name,$Value)
	{
		if ($Value instanceof PageForm)
		{

			//This is a form, we should add it to our forms array
			$Value->Name = $Name;
			$Value->ParentContainer = $this;
			$this->_forms[$Name] = $Value;
			
		}
		else
		{
			parent::__set($Name, $Value);
		}
	}

	public function __toString()
	{

		//If there are any controls, render them
     	if (count($this->_controlOrder) > 0)
		{
			$returnValue .= $this->RenderControls();
		}

		return $returnValue;
	}


	/**
	 * IsLoginRequired property
	 * 
	 * @return boolean
	 */
	public function getIsLoginRequired()
	{
		return $this->_isLoginRequired;
	}

	/**
	 * AllowedroleIDs property
	 * 
	 * @return array
	 */
	public function getAllowedRoleIDs()
	{
		return $this->_allowedRoleIDs;
	}
	
	/**
	 * IsTrafficLogged property
	 * 
	 * @return boolean
	 */
	public function getIsTrafficLogged()
	{
		return $this->_isTrafficLogged;
	}
	
	public function getForceSSL()
	{
		return $this->_forceSSL;
	}
	
	/**
	 * ActivePageName property
	 * 
	 * @return string
	 */
	final public function getActivePageName()
	{
		return $this->_activePageName;
	}

	/**
	 * RequestedURL property
	 * 
	 * @return string
	 */	
	final public function getRequestedURL()
	{
		if (is_set($this->_requestedURL))
		{
			$returnValue = $this->_requestedURL;
		}
		else 
		{
			//$returnValue = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REDIRECT_URL']}";
			if ($this->_forceSSL == true)
			{
				$returnValue = Application::SecureURL() . Application::RoutingPath();
			}	
			else
			{
				$returnValue = Application::BaseURL() . Application::RoutingPath();
			}
		}
		
		return $returnValue;
	}
	
	final public function setRequestedURL($Value)
	{
		if (strlen($Value) > 0)
		{
			if (strtolower(substr($Value, 0, 4)) == "http")
			{
				$this->_requestedURL = $Value;
			}
			else 
			{
				if (substr($Value, 0, 1) == "/")
				{
					$Value = substr($Value, 1, strlen($Value - 1));
				}
				
				$this->_requestedURL = "{$this->BaseURL}{$Value}";				
			}
		}
		else 
		{
			$this->_requestedURL = null;
		}
	}

	final public function getBaseURL()
	{
		return Application::CurrentBaseURL();		
	}
	
	public function getOnLoadFunction()
	{
		return $this->_onLoadFunction;
	}
	
	public function setOnLoadFunction($Value)
	{
		$this->_onLoadFunction = $Value;
	}

	/**
	 * Redirects to the SSL version of this page, losing any post parameters
	 **/
	protected function ForceSSL()
	{
		$redirectURL = Application::SecureURL() . Application::RoutingPath();
		
		header("Location: $redirectURL");
	}

	final public function RaiseEvent($EventName, $EventParameters)
	{
		if (strlen($EventName) > 0)
		{
			// Force SSL if needed.
			// Note.  	Any $EventParameters set will disappear
			// 			if the SSL redirect is necessary, unless
			//			those $EventParameters are set by the RoutingURL.
			if (($this->_forceSSL == true) && ($_SERVER['SERVER_PORT'] == 80))
			{
				$this->ForceSSL();
			}
			
			//If we are passed an ENV string, use that as our event parameters
			if (is_set($EventParameters['env']))
			{
				$EventParameters = array_merge($EventParameters, $this->BuildEventParamertsFromEnvrionmentString($EventParameters['env']));
			}

			//Save our active page name from the event parameters' page element
			$this->_activePageName = $EventParameters['page'];

			//Build the names of the PreProcessor, Handler and PostProcessor functions
			$preProcessorFunctionName = $EventName . "_PreProcessor";
			$handlerFunctionName = $EventName . "_Handler";
			$postProcessorFunctionName = $EventName . "_PostProcessor";

			//Do we have a handler for this event?		
			if (method_exists($this, $handlerFunctionName))
			{
				
				$this->Generic_PreProcessor($EventParameters);
				
				//Do we have a PreProcessor for this event?
				if (method_exists($this, $preProcessorFunctionName))
				{
					$this->$preProcessorFunctionName($EventParameters);
				}

				//Handle the Event	
				$returnValue = $this->$handlerFunctionName($EventParameters);

				//If the event results returned are marked as processing complete,
				//we skip any post handlers
				if ($returnValue->IsProcessingComplete == false)
				{
					//Do we have a PostProcessor for this event?
					if (method_exists($this, $postProcessorFunctionName))
					{
						$returnValue = $this->$postProcessorFunctionName($returnValue);
					}

					$returnValue = $this->Generic_PostProcessor($returnValue);
				}

			}
			else
			{
				//We don't have a handler for this event
				$returnValue = $this->UnhandledEvent_Handler($EventParameters);
			}
		}
		else 
		{
			//No event was passed
			$EventParameters['Event'] = "NULL";
			$returnValue = $this->UnhandledEvent_Handler($EventParameters);
		}

		return $returnValue;
	}

	public function InitializeSmarty()
	{
		
		$this->_diSmarty = new DISmarty();
		$this->_diSmarty->compile_dir = "compile";
		$this->_diSmarty->template_dir = "templates";
	}

	public function RenderSmarty()
	{
		// Display
		$this->_diSmarty->display($this->_smartyTemplateName);
	}

	protected function Flash($Message, $Mode)
	{
		
	}
	
	protected function Generic_PreProcessor(&$EventParameters)
	{
		
	}

	protected function Generic_PostProcessor($EventResults)
	{
		return $EventResults;
	}
		
	final protected function Page404_Handler($EventParameters)
	{
		$isAJAX = $EventParameters['ajax'];
		$pageName = $EventParameters['page'];
		$seoPageName = $EventParameters['SEOpage']->Name;
		
		if ($isAJAX)
		{
			$returnValue = new EventResults();
			
			echo "<script>alert('You\'ve called a page that doesn\'t exist.');</script>";
			
			$returnValue->Value = false;
			$returnValue->Complete();
		}
		else 
		{
			if (is_set($seoPageName))
			{
				throw new Page404exception("The requested URL was not found on this server.", $seoPageName, $pageName);
			}
			else 
			{
				throw new Page404exception("The requested URL was not found on this server.", $pageName);
			}
		}
		
		
		return $returnValue;
	}
	
	final protected function UnhandledEvent_Handler($EventParameters)
	{
		$isAJAX = $EventParameters['ajax'];
		$pageName = substr(get_class($this), 0, -4);
		$eventName = $EventParameters['event'];
		
		if ($isAJAX)
		{
			
			$returnValue = new EventResults();
			
			echo "<script>alert('You\'ve called an event that doesn\'t exist.');</script>";
			
			$returnValue->Value = false;
			$returnValue->Complete();			
			
		}
		else 
		{
			throw new UnhandledEventException("Unhandled Event Raised", $pageName, $eventName);
		}
		
		
		return $returnValue;	
	}

	protected function Unauthorized_Handler($EventParameters)
	{
		$isAJAX = $EventParameters['ajax'];
		$pageName = $EventParameters['page'];
		$eventName = $EventParameters['event'];
		
		if ($isAJAX)
		{

			$returnValue = new EventResults();

			echo "<script>alert('You\'ve called an event that you are not authorized to raise.');</script>";

			$returnValue->Value = false;
			$returnValue->Complete();

		}
		else 
		{
			throw new Page403exception("User Not Authorized", $pageName, $eventName);
		}
		
		return $returnValue;	
	}

	final protected function Load_PreProcessor(&$EventParameters)
	{

		//Do we need to log traffic?
		if ($this->_isTrafficLogged)
		{
			$this->_traffic = new Traffic($_SERVER['HTTP_USER_AGENT']);

			$currentUser = Application::CurrentUser();

			if (is_set($currentUser))
			{
				$this->_traffic->UserID = $currentUser;
			}

			$this->_traffic->SEOPage = $EventParameters['SEOpage'];

			$this->_traffic->LogVisit();
		}

		//Were we passed a specific RequestURL to use?
		$this->RequestedURL = $EventParameters['RequestedURL'];


		//Does this page use a smarty template?
		if (is_set($this->_smartyTemplateName))
		{
			//Init Smarty
			$this->InitializeSmarty();
		}
		
		// Flash Message Control Configuration
		$this->FlashMessage->InnerHTML = $EventParameters['FlashMessage'];
		$this->FlashMessage->Mode = $EventParameters['FlashMessageMode'];
		
		//Make sure our controls are loaded
		if (count($this->_forms) == 0)
		{
			$this->BuildControlArray($EventParameters);
		}
		
		
	}

	final protected function Load_PostProcessor($EventResults)
	{
		//Does this page use a smarty template?  If so, init smarty.
		if (is_set($this->_smartyTemplateName))
		{
			$returnValue = new EventResults();

			$this->SetDefaultTemplateVariables();

			$this->RenderSmarty();
			
			$returnValue->Value = $EventResults->Value;
			$returnValue->Complete();
		}
		else
		{
			$returnValue = $EventResults;
		}

		return $returnValue;		
	}

	final protected function Post_PreProcessor(&$EventParameters)
	{
		//Setup our Control Array
		$this->BuildControlArray($EventParameters);
		
		//Validate our controls & add the results to our Event Parameters
		$validationResults = $this->RaiseEvent("Validate", $EventParameters);
		$EventParameters['ValidationResults'] = $validationResults;

	}

	final protected function Post_Handler($EventParameters)
	{
		//This handles the event whenever something is posted directly back on the page,
		//rather than to a specific event.  Normally this will be used for full form posts.
		//AJAX event calls will NOT go though this, as they always specify an event.

		//Did we pass validation?
		if ($EventParameters['ValidationResults']->Value == true)
		{			
			$postEvent = "{$this->_postedForm->Name}Post";

			$postFunctionName = $postEvent . "_Handler";

			if (method_exists($this, $postFunctionName))
			{
				$returnValue = $this->RaiseEvent($postEvent, $EventParameters);
			}
			else
			{
				//If there isn't a post event handler defined, default to the load
				$returnValue = $this->RaiseEvent("Load", $EventParameters);
			}
		}
		else
		{
			//Validation Failed, return the validation results
			$returnValue = $EventParameters['ValidationResults'];
		}

		return $returnValue;

	}

	final protected function Validate_Handler($EventParameters)
	{

		$returnValue = new EventResults();

		//First, make sure we have our controls loaded
		if (count($this->_forms) == 0)
		{
			$this->BuildControlArray($EventParameters);
		}

		$isAJAX = $EventParameters['ajax'];

		//Do we have any controls to validate?
		if (count($this->_postedForm->Controls) > 0)
		{
			
			//Begin with a true value, and set it to false if
			//any validation fails.
			$returnValue->Value = true;
			
			//Validate each control
			foreach ($this->_postedForm->Controls as $tempControl)
			{
				//Attemt the validation				
				$success = $tempControl->Validate();
								
				//Were we successful?
				if ($success == false)
				{
					//if this is an AJAX call, we'll echo
					//the javascript return here
					if ($isAJAX)
					{
						echo $tempControl->ValidationFailureJavascript;
					}

					$returnValue->Value = false;
				}
				else
				{
					//if this is an AJAX call, we'll echo
					//the javascript return here
					if ($isAJAX)
					{
						echo $tempControl->ValidationSuccessJavascript;
					}
				}
			}
			
			//If this isn't an AJAX call, and we have a validation failure,
			//we will simply reload the page.
			if ($returnValue->Value == false && $isAJAX == false)
			{
				$loadResults = $this->RaiseEvent("Load", $EventParameters);
				$loadResults->Flush();
			}
						
		}
		else
		{
			//There are no controls, so default to success
			$returnValue->Value = true;
		}

		$returnValue->Complete();
		
		return $returnValue;

	}

	final protected function ControlEvent_Handler($EventParameters)
	{

   		//First, make sure we have our controls loaded
		if (count($this->_forms) == 0)
		{
			$this->BuildControlArray($EventParameters);
		}

		$ControlEventName = $EventParameters['controlevent'];

		$targetControl = $this->_postedForm->AllActiveControls[$EventParameters['control']];

		//Build the names of the PreProcessor and PostProcessor functions
		$preProcessorFunctionName = $targetControl->Name . "_" . $ControlEventName . "_PreProcessor";
		$postProcessorFunctionName = $targetControl->Name . "_" . $ControlEventName . "_PostProcessor";

		//Do we have a PreProcessor for this event?
		if (method_exists($this, $preProcessorFunctionName))
		{
			$this->$preProcessorFunctionName($EventParameters);
		}

		//Raise the event on the control
		$returnValue = $targetControl->RaiseEvent($ControlEventName, $EventParameters);

		//If we don't get a value back, it's an Unhandled Event
		if (is_set($returnValue) == false)
		{
			$returnValue = $this->UnhandledEvent_Handler($EventParameters);
		}

		//Do we have a PostProcessor for this event?
		if (method_exists($this, $postProcessorFunctionName))
		{
			$returnValue = $this->$postProcessorFunctionName($returnValue);
		}

		return $returnValue;

	}

	final protected function Javascript_Handler($EventParameters)
	{

		$returnValue = new EventResults();

		$this->BuildControlArray($EventParameters);

		// Return in plain text
		header('Content-Type: text/plain');
		
		// Check for an onload handler
		if (is_set($this->OnLoadFunction))
		{
			echo "window.onload = {$this->OnLoadFunction};\n\n";
		}
		
		//Loop through the forms, and export their javascript
		foreach ($this->_forms as $tempForm)
		{
			echo $tempForm->RequiredJavascriptFunctions;
		}

		$returnValue->Value = false;
		$returnValue->Complete();

		return $returnValue;
	}

	final protected function SetDefaultTemplateVariables()
	{
		//Some functional values we use all over the place.
		$this->_diSmarty->BaseURL = $this->BaseUrl;
		$this->_diSmarty->User = Application::CurrentUser();
		$this->_diSmarty->PageName = $this->_activePageName;
		$this->_diSmarty->CurrentPage = $this;

		//If we at least one form defined, add it to smarty
		if (count($this->_forms) > 0)
		{
			$this->_diSmarty->Forms = $this->_forms;
		}

		$this->_diSmarty->Controls = $this->_controls;

	}

	public function BuildEnvironmentString($EventParameters)
	{

		//First trim any objects and non "environment" settings like page and event from the EPs
		if (count($EventParameters) > 0) 
		{
			foreach($EventParameters as $key=>$value)
			{
				if (is_object($value) || $key == "page" || $key == "event" || $key == "controlevent" || $key == "formname" || $key = "control");
				{
					unset($EventParameters[$key]);
				}
			}

			//Convert the Event Parameters array into a simple string
			$returnValue = serialize($EventParameters);

			//Now encrypt it
			$returnValue = DIencrypt::Encrypt($returnValue);
		}

		return $returnValue;
	}

	public function BuildEventParamertsFromEnvrionmentString($EnvironmentString)
	{
		if (is_set($EnvironmentString) && strlen($EnvironmentString) > 0)
		{
        	$returnValue = DIencrypt::Decrypt($EnvironmentString);
			$returnValue = unserialize($returnValue);
		}
		else
		{
			$returnValue = Array();
		}

		return $returnValue;
	}

	protected function BuildControlArray($EventParameters)
	{
		//Identifiy the posted form
		if (is_set($EventParameters['formname']))
		{
			$this->_postedForm = $this->_forms[$EventParameters['formname']];
		}

		//By default set the environment variable for the context we just used to build the
		//control array
		if (is_set($this->_smartyTemplateName))
		{
			$this->_diSmarty->Environment = "?env={$this->BuildEnvironmentString($EventParameters)}";
		}
	}

}

?>