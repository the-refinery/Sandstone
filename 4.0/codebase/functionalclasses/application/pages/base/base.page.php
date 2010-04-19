<?php
/*
Page Class File

@package Sandstone
@subpackage Application
 */

NameSpace::Using("Sandstone.SEO");
NameSpace::Using("Sandstone.Smarty");
NameSpace::Using("Sandstone.Utilities.Encryption");

class BasePage extends ControlContainer
{
	protected $_isLoginRequired = false;
	protected $_allowedRoleIDs = Array();
	protected $_forceSSL = false;

	protected $_onLoadFunction;

	protected $_forms;
	protected $_postedForm;
	protected $_apiForm;

	protected $_activePageName;

	protected $_templateSearchPath;

	protected $_isOKtoLoadControls = true;

	public function __construct()
	{
		parent::__construct();

		$className = get_class($this);
		$this->_name = substr($className, 0, strlen($className) - 4);

		$this->_forms = new DIarray();

		$this->_template->IsMasterLayoutUsed = true;

		$this->_isRawValuePosted = false;

	}

	public function __get($Name)
	{
		if (array_key_exists(strtolower($Name), $this->_forms))
		{
			$returnValue = $this->_forms[strtolower($Name)];
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
			$Value->Template->RequestFileType = $this->_template->RequestFileType;
			$this->_forms[strtolower($Name)] = $Value;

			if (is_set($this->_apiForm) == false)
			{
				$this->_apiForm = $Value;
			}

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

	/*
	IsLoginRequired property

	@return boolean
	 */
	public function getIsLoginRequired()
	{
		return $this->_isLoginRequired;
	}

	/*
	AllowedroleIDs property

	@return array
	 */
	public function getAllowedRoleIDs()
	{
		return $this->_allowedRoleIDs;
	}

	public function getForceSSL()
	{
		return $this->_forceSSL;
	}

	/*
	Forms property

	@return Array
	 */
	public function getForms()
	{
		return $this->_forms;
	}

	/*
	ActivePageName property

	@return string
	 */
	final public function getActivePageName()
	{
		return $this->_activePageName;
	}

		/*
		TemplateSearchPath property

		@return string
		 */
	final public function getTemplateSearchPath()
	{
		if (is_set($this->_templateSearchPath) == false)
		{
			$target = NameSpace::NamespaceEnviromentBase("application") . Namespace::PageSpace(get_class($this)) . "templates/";

			$templateDirs = Template::FindDirectoriesWithTemplates($target);

			$this->_templateSearchPath = implode(PATH_SEPARATOR, $templateDirs);
		}

		return $this->_templateSearchPath;
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

	public function getPageBaseURLforTemplate()
	{
		return Routing::GetPageBaseURL();
	}

	public function getOnLoadFunction()
	{
		return $this->_onLoadFunction;
	}

	public function setOnLoadFunction($Value)
	{
		$this->_onLoadFunction = $Value;
	}

	/*
	Redirects to the SSL version of this page, losing any post parameters
	 */
	protected function ForceSSL()
	{
		$redirectURL = Application::SecureURL() . Application::RoutingPath();

		header("Location: $redirectURL");
	}

	final public function RaiseEvent($EventParameters)
	{
		Benchmark::Log("Page","({$EventParameters['event']}) {$EventParameters['page']}");

		//Set the file type for the template
		$this->_template->RequestFileType = $EventParameters['filetype'];

		//If we are passed an ENV string, use that as our event parameters
		if (is_set($EventParameters['env']))
		{
			$EventParameters = array_merge($EventParameters, $this->BuildEventParamertsFromEnvrionmentString($EventParameters['env']));
		}

		//Save our active page name from the event parameters' page element
		$this->_activePageName = $EventParameters['page'];

		$handlerFunctionName = $EventParameters['event'] . "_Handler";

		//Do we have a handler for this event?
		if (method_exists($this, $handlerFunctionName))
		{
			$returnValue = $this->ProcessRaisedEvent($EventParameters, $handlerFunctionName);
		}
		else
		{
			//We don't have a handler for this event
			$returnValue = $this->UnhandledEvent_Handler($EventParameters);
		}

		return $returnValue;
	}

	protected function ProcessRaisedEvent($EventParameters, $HandlerFunctionName)
	{
		$this->Generic_PreProcessor($EventParameters);

		$EventParameters = $this->SetupPageControls($EventParameters);

		$this->AttemptEventPreProcessor($EventParameters);

		$returnValue = $this->$HandlerFunctionName($EventParameters);

		$returnValue = $this->AttemptPostProcessors($EventParameters, $returnValue);

		return $returnValue;
	}

	protected function SetupPageControls($EventParameters)
	{
		$this->BuildControlArray($EventParameters);

		if ($this->_isOKtoLoadControls)
		{
			if (method_exists($this, "LoadControlData"))
			{
				$this->LoadControlData($EventParameters);
			}

			$EventParameters = $this->SetupAPIformControlValues($EventParameters);
		}
		else
		{
			if (method_exists($this, "SetupControlsNoData"))
			{
				$this->SetupControlsNoData($EventParameters);
			}
		}

		return $EventParameters;
	}

	protected function SetupAPIformControlValues($EventParameters)
	{
		if (Application::IsAPImode() && is_set($this->_apiForm))
		{
			$EventParameters['formname'] = $this->_apiForm->Name;
			$this->_postedForm = $this->_apiForm;

			foreach ($this->_apiForm->Controls as $tempControl)
			{
				$localName = strtolower($tempControl->LocalName);
				$fullName = strtolower($tempControl->Name);

				if (is_set($EventParameters[$localName]))
				{
					$EventParameters[$fullName] = $EventParameters[$localName];
				}
			}

			$this->_apiForm->EventParameters = $EventParameters;
		}

		return $EventParameters;
	}

	protected function AttemptEventPreProcessor($EventParameters)
	{
		$preProcessorFunctionName = $EventParameters['event'] . "_PreProcessor";

		if (method_exists($this, $preProcessorFunctionName))
		{
			$this->$preProcessorFunctionName($EventParameters);
		}
	}

	protected function AttemptPostProcessors($EventParameters, $EventResults)
	{
		$returnValue = $EventResults;

		if ($returnValue->IsProcessingComplete == false)
		{
			$postProcessorFunctionName = $EventParameters['event'] . "_PostProcessor";

			if (method_exists($this, $postProcessorFunctionName))
			{
				$returnValue = $this->$postProcessorFunctionName($returnValue);
			}

			$returnValue = $this->Generic_PostProcessor($returnValue);
		}

		return $returnValue;
	}

	protected function Generic_PreProcessor(&$EventParameters)
	{

	}

	protected function Generic_PostProcessor($EventResults)
	{
		return $EventResults;
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

	final protected function GET_Handler($EventParameters)
	{
		$returnValue = new EventResults();

		$session = Application::Session();

		//Set the Header Content Type
		switch ($EventParameters['filetype'])
		{
		case "htm":
			header('Content-Type: text/html; charset=utf-8');
			break;

		case "txt":
			case "csv":
				case "term":
					header('Content-Type: text/plain');
					break;

				case "xml":
					case "rss":
						header('Content-Type: text/xml');
						break;

					case "css":
						header('Content-Type: text/css');
						break;

					case "js":
						header('Content-Type: text/javascript');
						break;

					case "htm403":
						case "txt403":
							header('HTTP/1.1 403 Forbidden');
							break;

						case "htm404":
							case "txt404":
								header('HTTP/1.1 404 Not Found');
								break;

							case "cron":
								//For cron processes - make sure they come in via the
								//cron.php entry point.  Otherwise just die
								if (array_key_exists("IsCronEntryPoint", $session) == false)
								{
									die();
								}
								break;

							default:
								break;
		}

		//Get any Notification Message that's been set, and push it into a template variable
		$this->_template->NotificationMessage = $session['notificationmessage'];
		$this->_template->NotificationMessageType = $session['notificationmessagetype'];

		//Do we have a specific file type processor?
		$processorName = $EventParameters['filetype'] . "_Processor";

		if (method_exists($this, $processorName))
		{
			$this->$processorName($EventParameters);
		}

		//Did the specific type processor echo anything to the buffer?
		if (strlen(ob_get_contents()) == 0)
		{
			//No, so render the page
			echo $this->Render();
		}

		//Since we've used it, let's clear the NotificationMessage
		Application::ClearSessionVariable("notificationmessage");
		Application::ClearSessionVariable("notificationmessagetype");

		$returnValue->Value = true;
		$returnValue->Complete();

		return $returnValue;

	}

	protected function HTM_Processor($EventParameters)
	{
		$html = $this->Render();

		echo $this->CompressHTML($html);
	}

	protected function JS_Processor($EventParameters)
	{
		$pageJavascript = $this->Render();

		$observers = $this->RenderObservers($pageJavascript);

		$finalOutput = $pageJavascript;

		if (is_set($observers))
		{
			//We have observers we need to register.  Build the function to register them
			$finalOutput .= "\n";
			$finalOutput .= "$(document).ready(function()\n";
			$finalOutput .= "{\n";
			$finalOutput .= $observers;
			$finalOutput .= "})\n\n";
		}

		//Echo our final output
		echo $this->CompressJavascript($finalOutput);

	}

	protected function CSS_Processor($EventParameters)
	{
		$css = $this->Render();

		echo $this->CompressCSS($css);
	}

	protected function TERM_Processor($EventParameters)
	{
		$term = $this->Render();

		echo $this->Terminalize($term);
	}

	final protected function POST_Handler($EventParameters)
	{

		$success = $this->ValidatePostedForm();

		if ($success)
		{
			$this->ProcessPostedForm($EventParameters);
		}

		if ($success)
		{
			$returnValue = $this->HandleSuccessfulFormProcessing($EventParameters);
		}
		else
		{
			$returnValue = $this->HandleUnsuccessfulFormProcessing($EventParameters);
		}

		return $returnValue;
	}

	final protected function ProcessPostedForm($EventParameters)
	{
		$this->_postedForm->RedirectTarget = $EventParameters['routing'];

		$returnValue = $this->AttemptFormPreProcessor($EventParameters);

		if ($returnValue == true)
		{
			$returnValue = $this->AttemptFormProcessor($EventParameters);
		}

		if ($returnValue == true)
		{
			$returnValue = $this->AttemptFormPostProcessor($EventParameters);
		}

		return $returnValue;

	}

	final protected function AttemptFormPreProcessor($EventParameters)
	{
		$returnValue = true;

		$preProcessorMethodName = $this->_postedForm->Name . "_PreProcessor";

		if (method_exists($this, $preProcessorMethodName))
		{
			$returnValue = $this->$preProcessorMethodName($EventParameters);
		}
		
		return $returnValue;
	}

	final protected function AttemptFormProcessor($EventParameters)
	{
		$returnValue = false;

		$processorMethodName = $this->_postedForm->Name . "_Processor";

		if (method_exists($this, $processorMethodName))
		{
			$returnValue = $this->$processorMethodName($EventParameters);
		}
		elseif (is_set($this->_postedForm->EntityObject))
		{
			$returnValue = $this->FormEntitySave_Processor($EventParameters);
		}

		return $returnValue;

	}

	final protected function FormEntitySave_Processor($EventParameters)
	{

		foreach ($this->_postedForm->Controls as $controlName=>$control)
		{

			if (is_set($control->AssociatedEntityPropertyName))
			{
				$propertyName = $control->AssociatedEntityPropertyName;
			}
			else
			{
				$propertyName = $controlName;
			}

			if ($this->_postedForm->EntityObject->hasProperty($propertyName))
			{
				$this->_postedForm->EntityObject->$propertyName = $control->Value;
			}
		}

		$returnValue = $this->_postedForm->EntityObject->Save();

		if ($returnValue == true)
		{
			//Set the notification (if any)
			if (is_set($this->_postedForm->EntitySaveSuccessNotification))
			{
				Application::SetSessionVariable('notificationmessage', $this->_postedForm->EntitySaveSuccessNotification);
				Application::SetSessionVariable('notificationmessagetype', "success");
			}

			//Set the Redirect Target (if an action is specified)
			if (is_set($this->_postedForm->EntitySaveSuccessRoutingAction))
			{
				$this->_postedForm->RedirectTarget = Routing::BuildURLbyEntity($this->_postedForm->EntityObject, $this->_postedForm->EntitySaveSuccessRoutingAction);
			}
		}
		else
		{
			//Set the notification (if any)
			if (is_set($this->_postedForm->EntitySaveFailureNotification))
			{
				Application::SetSessionVariable('notificationmessage', $this->_postedForm->EntitySaveFailureNotification);
				Application::SetSessionVariable('notificationmessagetype', "error");
			}

		}

		return $returnValue;
	}

	final protected function AttemptFormPostProcessor($EventParameters)
	{
		$returnValue = true;

		$postProcessorMethodName = $this->_postedForm->Name . "_PostProcessor";

		if (method_exists($this, $postProcessorMethodName))
		{
			$returnValue = $this->$postProcessorMethodName($EventParameters);
		}

		return $returnValue;
	}

	final protected function HandleSuccessfulFormProcessing($EventParameters)
	{
		$returnValue = new EventResults();

		if (Application::IsAPImode())
		{
			$returnValue = $this->HandleUnsuccessfulFormProcessing($EventParameters);
		}
		else
		{
			Application::Redirect($this->_postedForm->RedirectTarget);
		}

		return $returnValue;
	}

	final protected function HandleUnsuccessfulFormProcessing($EventParameters)
	{
		if (method_exists($this, "GET_PreProcessor"))
		{
			$this->GET_PreProcessor($EventParameters);
		}

		$returnValue = $this->GET_Handler($EventParameters);

		if (method_exists($this, "GET_PostProcessor"))
		{
			$returnValue = $this->GET_PostProcessor($returnValue);
		}

		return $returnValue;
	}

	final protected function AJAX_Handler($EventParameters)
	{
		$returnValue = new EventResults();

		if (strtolower($EventParameters['target']) == "page")
		{
			$target = $this;
			$targetName = $this->LocalName;
		}
		else
		{
			//We have a control name, find the control by that name
			$targetName = str_replace("_", "->", $EventParameters['target']);

			$cmd = "\$target = \$this->{$targetName};";
			eval($cmd);

			//Do we use the target's normal type, or reference its parent's type for the template?
			if ($target->IsParentTemplateUsed)
			{
				$targetType = strtolower(get_parent_class($target));
			}
			else
			{
				$targetType = strtolower(get_class($target));
			}

			$targetName = str_replace("control", "", $targetType);
		}

		$processor = new AJAXprocessor($this, $target, $EventParameters['method'], $EventParameters);
		$processor->Template->FileName = strtolower("{$targetName}_{$EventParameters['method']}");;

		//Call the method and echo the results
		header('Content-Type: text/javascript');

		$results = $processor->Render();
		echo $this->CompressJavascript($results, false);

		$returnValue->Value = true;
		$returnValue->Complete();

		return $returnValue;
	}

	final protected function API_Handler($EventParameters)
	{
		$returnValue = new EventResults();

		$returnValue->Value = true;
		$returnValue->Complete();

		return $returnValue;
	}

	final protected function ValidatePostedForm()
	{

		//Do we have any controls to validate?
		if (count($this->_postedForm->Controls) > 0)
		{

			//Begin with a true value, and set it to false if
			//any validation fails.
			$returnValue = true;

			//Validate each control
			foreach ($this->_postedForm->Controls as $tempControl)
			{
				//Attemt the validation
				$success = $tempControl->Validate();

				//Were we successful?
				if ($success == false)
				{
					$returnValue = false;
				}
			}
		}
		else
		{
			//There are no controls, so default to success
			$returnValue = true;
		}

		if ($returnValue == false)
		{
			//Used for Javascript checks of a validation pass/fail
			$this->_postedForm->Template->Validation = 'false';
		}

		return $returnValue;
	}

	final protected function SetResponseCode($ResponseCode, &$EventParameters)
	{
		if (is_numeric($ResponseCode))
		{

			$EventParameters['filetype'] .= $ResponseCode;
			$this->_template->RequestFileType = $EventParameters['filetype'];

			$returnValue = true;
		}
		else
		{
			$returnValue = false;
		}

		return $returnValue;
	}

	protected function BuildControlArray($EventParameters)
	{
		$this->EventParameters = $EventParameters;

		// Identifiy the posted form
		if (is_set($EventParameters['formname']))
		{
			$this->_postedForm = $this->_forms[strtolower($EventParameters['formname'])];
		}
	}

	public function Render()
	{
		$this->_template->PageBaseURL = $this->PageBaseURLforTemplate;
		$this->_template->RequestedURL = Routing::GetRequestedURL();
		$this->_template->JavascriptFileURL = Routing::GetFileTypeURL("js");
		$this->_template->CSSFileURL = Routing::GetFileTypeURL("css");
		$this->_template->PageAJAXurl = Routing::GetFileTypeURL("ajax");

		$returnValue = parent::Render();

		return $returnValue;
	}


	public function RenderObservers($Javascript)
	{

		//First Loop any forms and append thier Observers
		if (count($this->_forms) > 0)
		{
			foreach ($this->_forms as $tempForm)
			{
				$returnValue .= $tempForm->RenderObservers($Javascript);
			}
		}

		//Now Loop any controls and append thier Observers
		if (count($this->_controls) > 0)
		{
			foreach ($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->RenderObservers($Javascript);
			}
		}

		//Now look for any "sub elements" of this control which aren't part of our controls or forms arrays
		$pattern = "/function ([A-Za-z]+)_On([A-Za-z]+)\(.*\)/";
		preg_match_all($pattern, $Javascript, $functions, PREG_SET_ORDER);

		//Did we find any?
		if (count($functions) > 0)
		{
			//We have some, so register the observers
			foreach ($functions as $tempFunction)
			{
				$elementName = $tempFunction[1];
				$eventName = strtolower($tempFunction[2]);
				$endOfFunctionName = strpos($tempFunction[0], "(");
				$functionName = substr(substr($tempFunction[0], 0, $endOfFunctionName), 9);

				//Is this element a control?
				if (array_key_exists(strtolower($elementName), $this->Controls) == false && array_key_exists(strtolower($elementName), $this->Forms) == false && strtolower($elementName) != 'page')
				{
					//(check in JS on the client side to make sure the DOM elements exist)
					$returnValue .= "\tif (\$('#{$elementName}')) \$('#{$elementName}').bind('{$eventName}', {$functionName});\n";
				}

			}
		}


		return $returnValue;
	}

}
?>
