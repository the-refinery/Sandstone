<?php
/**
Routing Base Class File

@package Sandstone
@subpackage Routing
*/

Namespace::Using("Sandstone.Utilities.XML");

class RoutingBase extends Module
{

	protected $_routingRules;

	protected $_parsedEventParameters;

	protected function __construct()
	{
		$this->SetupRules();
	}

	static public function Instance()
	{
		static $routing;

		if (is_set($routing) == false)
		{
			$routing = new Routing();
		}

		return $routing;
	}

	static public function Display()
	{
    	$routing = Routing::Instance();

		$routing->ProcessDisplay();
	}

	static public function ParseRequest()
	{

		$routing = Routing::Instance();

		$returnValue = $routing->ProcessParseRequest();

		return $returnValue;
	}

	static public function GetFileTypeURL($FileType)
	{
		$routing = Routing::Instance();

		$returnValue = $routing->ProcessGetFileTypeURL($FileType);

		return $returnValue;
	}

	static public function GetRequestedURL()
	{
		$routing = Routing::Instance();

		$returnValue = $routing->ProcessGetRequestedURL();

		return $returnValue;
	}

	static public function GetPageBaseURL()
	{
		$routing = Routing::Instance();

		$returnValue = $routing->ProcessGetPageBaseURL();

		return $returnValue;
	}

	static public function GetIsUtilityFileRule()
	{
		$routing = Routing::Instance();

		$returnValue = $routing->ProcessGetIsUtilityFileRule();

		return $returnValue;

	}

	static public function BuildURLbyRule($RuleName, $Parameters = Array(), $FileType = "htm", $IsFullFormat = false)
	{

		$routing = Routing::Instance();

		$returnValue = $routing->ProcessBuildURLbyRule($RuleName, $Parameters, $FileType, $IsFullFormat);

		return $returnValue;

	}

	static public function BuildURLbyEntity($Entity, $Action, $Parameters = Array(), $FileType = "htm", $IsFullFormat = false)
	{
		$routing = Routing::Instance();

		$returnValue = $routing->ProcessBuildURLbyEntity($Entity, $Action, $Parameters, $FileType, $IsFullFormat);

		return $returnValue;

	}

	public function ProcessDisplay()
	{
		echo "<pre>";

		di_var_dump($this->_routingRules);

		echo "</pre>";

	}

	public function ProcessParseRequest()
	{

		//This will build the EventParameters array
		//including the Page, Event and other parameters from
		//the routing rules.

		//Start off pulling the parameters from the GET array
		$returnValue = $this->ParseGETparameters();
		//Do we have a routing element?
		if (array_key_exists("routing", $returnValue) == false)
		{
			$returnValue["routing"] = "";
		}

		//Determine if our string matches a routing rule.
		$routingRules = $this->EvaluateRoutingRules($returnValue['routing']);

		$returnValue = array_merge($returnValue, $routingRules);

		//Now that we have routing complete, parse the FILE parameters
		$file = $this->ParseFILESparameters();
		$returnValue = array_merge($returnValue, $file);

		//And the post parameters
		$post = $this->ParsePOSTparameters();

		//Make sure we don't overwrite a previously set event
		if (is_set($returnValue['event']))
		{
			unset($post['event']);
		}

		$returnValue = array_merge($returnValue, $post);


		//If an event isn't by this point, we use the default of GET.
		if (is_set($returnValue['event']) == false)
		{
			$returnValue['event'] = "GET";
		}

		//Save our results
		$this->_parsedEventParameters = $returnValue;

		return $returnValue;

	}

	protected function ParseGETparameters()
	{
		$returnValue = Array();

        foreach ($_GET as $key=>$value)
		{
			$returnValue[strtolower($key)] = $value;
		}

		//Remove any event setting if it's present since we
		//expect this to start out unset, and don't want to allow
		//events from any query parameters.
		unset($returnValue['event']);

		return $returnValue;
	}

	protected function ParseFILESparameters()
	{
		$returnValue = Array();

		if (count($_FILES) > 0)
		{
			//Pull everything from the FILE
			foreach ($_FILES as $key=>$value)
			{
				$returnValue[strtolower($key)] = $value;
			}

			//If we have FILES parameters, this must be a post.
			$returnValue['event'] = "POST";
		}

		return $returnValue;

	}

	protected function ParsePOSTparameters()
	{
		$returnValue = Array();

		if (count($_POST) > 0)
		{
			//Pull everything from the POST.
			foreach ($_POST as $key=>$value)
			{
				$returnValue[strtolower($key)] = $value;
			}

			$returnValue['event'] = "POST";

		}

		return $returnValue;

	}

	protected function EvaluateRoutingRules($RoutingString)
	{

		//Init this so that it's always ready to go.
		$ruleParameters = Array();

		//Parse the routing string to something we can work with
		//(and determine a file extension)
		$returnValue = $this->ParseRoutingString($RoutingString);

		//Attempt to find a rule that matches the parsed routing string
		$matchingRule = $this->FindRoutingRule($returnValue['routingstring']);

		//Did we match one?
		if (is_set($matchingRule))
		{
			$ruleParameters = $this->BuildParametersFromRoutingRule($matchingRule, $returnValue['routingstring']);
		}
		else
		{
			//No routing rule match, as long as we have just a file name, see if it
			//matches an SEO page or simple page name.
			if (strpos($returnValue['routingstring'], "/") === false)
			{
				$allowedRootSEOpageClasses = $this->GetAllowedSEOrootClassesArray();

				if (is_set($allowedRootSEOpageClasses) && count($allowedRootSEOpageClasses) > 0)
				{
					//Check for an SEO page

					if (Application::IsLicenseCheckComplete())
					{
						$seoPage = new SEOpage();
						$isSEOpageFound = $seoPage->LoadByName($returnValue['routingstring']);

						if ($isSEOpageFound)
						{
							//Make sure the SEO Page's associted entity type is allowed
							//as a root SEO page
							if (array_search($seoPage->AssociatedEntityType, $allowedRootSEOpageClasses) === false)
							{
								//Not allowed as root
								$isSEOpageFound = false;
							}
							else
							{
								//Get the rule parameters for the Routing Rule associated
								//with this SEO page
								$ruleParameters = $this->BuildParametersFromRoutingRule(strtolower($seoPage->RoutingRuleName), $returnValue['routingstring']);

								//Now add some info from the SEO page itself
								$ruleParameters['seopagename'] = $returnValue['routingstring'];
								$ruleParameters['associatedentitytype'] = $seoPage->AssociatedEntityType;
								$ruleParameters['associatedentityid'] = $seoPage->AssociatedEntityID;
							}
						}
					}
				}
				else
				{
					$isSEOpageFound = false;
				}

				if ($isSEOpageFound == false)
				{
					//We'll look for a direct page reference
					$searchPageClassName = str_replace("-", "_", $returnValue['routingstring']);
					$ruleParameters['page'] = $searchPageClassName;
					$ruleParameters['matchedrule'] = "directpagereference";
				}
			}
		}

		//Make sure the selected page class exists, if not, 404
		$pageClassName = $ruleParameters['page'] . "Page";

		if (class_exists($pageClassName, true))
		{
			$ruleParameters['pageclass'] = $pageClassName;
		}
		else
		{
			//Page class not found.. we are 404
			$page404Parameters = $this->Build404parameters($returnValue['event']);

			$ruleParameters = array_merge($ruleParameters, $page404Parameters);
		}

		//Add the rule parameters to our return value
		$returnValue = array_merge($returnValue, $ruleParameters);

		return $returnValue;
	}

	protected function GetAllowedSEOrootClassesArray()
	{

		$returnValue = Application::Registry()->AllowedRootSEOpageClass;

		if (is_array($returnValue) == false)
		{
			$returnValue = Array($returnValue);
		}

		return $returnValue;
	}

	protected function ParseRoutingString($RoutingString)
	{

		//First, if there is a trailing slash, remove it.
		if (substr($RoutingString, -1) == "/")
		{
			$RoutingString = substr($RoutingString, 0, strlen($RoutingString) - 1);
		}

		//Is a file extension specified (look for the dot)
		$dotPosition = strpos($RoutingString, ".");
		if ($dotPosition === false)
		{
			//No file extension, use the default.
			$returnValue['filetype'] = "htm";
		}
		else
		{
			//There is an extension, save it
			$returnValue['filetype'] = substr($RoutingString, $dotPosition + 1);

			//Trim it from our string
			$RoutingString = substr($RoutingString, 0, $dotPosition);

			//html will always route to htm
			if ($returnValue['filetype'] == "html")
			{
				$returnValue['filetype'] = "htm";
			}
		}

		$returnValue['routingstring'] = $RoutingString;

		//If we can derive the event from the file extension, set it now
		switch ($returnValue['filetype'])
		{
			case "ajax":
				$returnValue['event'] = "AJAX";
				break;

			case "api":
				$returnValue['event'] = "API";
				break;
		}

		return $returnValue;

	}

	protected function FindRoutingRule($RoutingString)
	{

		$returnValue = null;

		//Loop the routing rules and see if we find a match
		$isMatched = false;

		//If we have an empty routing string, don't bother looking,
		//just return the default rule
		if(strlen($RoutingString) == 0)
		{
			$returnValue = "default";
		}
		else
		{
			foreach($this->_routingRules as $tempRuleName=>$tempRule)
			{
				if ($isMatched == false)
				{
					if (preg_match($tempRule['pattern'], $RoutingString))
					{
						$isMatched = true;
						$returnValue = $tempRuleName;
					}
				}
			}
		}

		return $returnValue;
	}

	protected function BuildParametersFromRoutingRule($RuleName, $RoutingString)
	{

		$returnValue['matchedrule'] = $RuleName;

		$matchedRule = $this->_routingRules[$RuleName];

		//Does this rule have a page value?
		if (strlen($matchedRule['page']) > 0)
		{
			$returnValue['page'] = $matchedRule['page'];
		}
		else
		{
			//If there was no page set, use the default page
			$returnValue['page'] = $this->_routingRules['default']['page'];
		}

		//Add the parms from the URL
		$rule = explode("/", $matchedRule['url']);
		$routing = explode("/", $RoutingString);

		foreach ($rule as $index=>$tempPart)
		{
			if (substr($tempPart, 0, 1) == "#" || substr($tempPart, 0, 1) == "{")
			{
				$key = substr($tempPart, 1, strlen($tempPart) - 2);

				$returnValue[$key] = $routing[$index];
			}
		}

		//Add the parms from the rule
		if (count($matchedRule['eventparameters']) > 0)
		{
			foreach ($matchedRule['eventparameters'] as $name=>$value)
			{
				$returnValue[$name] = $value;
			}
		}

		if (is_set($matchedRule['sslrequired']))
		{
			if (strlen($_SERVER['HTTPS']) == 0 && Application::Registry()->DevMode <> 1)
			{
				$returnValue['sslredirect'] = Routing::BuildSecureURL() . $RoutingString;
			}
		}

		return $returnValue;
	}

	protected function Build404parameters($SelectedEvent)
	{

		$returnValue['page'] = "Error404";
		$returnValue['pageclass'] = "Error404Page";
		$returnValue['event'] = "GET";

		switch ($SelectedEvent)
		{
			case "AJAX":
			case "API":
				$returnValue['filetype'] = "txt404";
				break;

			default:
				$returnValue['filetype'] = "htm404";
				break;
		}


		return $returnValue;
	}

	protected function SetupRules()
	{

		//Load all available routing rules
		$this->LoadRules();

		//Now process the loaded rules, making sure all are
		//valid, and setting the regex pattern for each
		$this->ValidateRules();

	}

	protected function LoadRules()
	{

		//First Load the Sandstone default rules
		$this->_routingRules = $this->LoadRulesDirectory("routing/");

		//Next Load the application's rules & add them to the array
		$applicationRules = $this->LoadRulesDirectory("resources/routing/");

		foreach ($applicationRules as $key=>$value)
		{
			$this->_routingRules[$key] = $value;
		}

		//Finally load any development rules & add them to the array
		$devRules = $this->LoadRulesDirectory("dev/");

		foreach ($devRules as $key=>$value)
		{
			$this->_routingRules[$key] = $value;
		}


	}

 	protected function LoadRulesDirectory($Directory)
 	{

 		$returnValue = Array();

 		//Where does this directory live in the include path?
		$fullDirectorySpec = ResolveFullDirectoryPath($Directory);

		if (strlen($fullDirectorySpec) > 0)
		{
			//Are there any routing files there?
			$files = glob($fullDirectorySpec . "*.routing.xml");

			if ($files != false)
			{
				//Loop each file found
				foreach ($files as $tempFile)
				{
					//Load the file's contents
					$fileRules = $this->LoadRulesFromXML($tempFile);

					//If the file had content, add it to the return value.
					if (count($fileRules) > 0)
					{
						foreach ($fileRules as $key=>$value)
						{
							$returnValue[$key] = $value;
						}
					}
				}
			}
		}

 		return $returnValue;
 	}

	protected function LoadRulesFromXML($XMLfileSpec)
	{

		//Make sure the file exists
		if (file_exists($XMLfileSpec))
		{
			//Load it's contents
			$xml = file_get_contents($XMLfileSpec);

			//Convert to an array
			$returnValue = DIxml::XMLtoArray($xml);

			//Make sure all keys are lowercase
			if (is_array($returnValue))
			{
				$returnValue = DIarray::ForceLowercaseKeys($returnValue, true);
			}
		}
		else
		{
			$returnValue = Array();
		}

		return $returnValue;

	}

	protected function ValidateRules()
	{

		if (count($this->_routingRules) > 0)
		{
			foreach ($this->_routingRules as $tempRuleName=>$tempRule)
			{
				//Does this rule have the required elements (URL & Page)?
				if (array_key_exists("url", $tempRule) && array_key_exists("page", $tempRule))
				{
					//The rule is valid, build it's regex pattern

					//Handle any numeric parameters
					$pattern = '/(\#)([A-Za-z0-9\-\>\[\]]+)(\#)/';
					$rulePattern = preg_replace($pattern, "[0-9]+", $tempRule['url']);

					//Now handle any alphanumeric parameters
					$pattern = '/(\{)([A-Za-z0-9\-\>\[\]]+)(\})/';
					$rulePattern = preg_replace($pattern, "[A-Za-z0-9 _\-]+", $rulePattern);

					//Escape any forward slashes
					$rulePattern = str_replace("/", "\/", $rulePattern);

					//Convert empty rule patterns
					if ($rulePattern == "")
					{
						$rulePattern = "/[^A-Za-z0-9 _\-\/]/";
					}
					else
					{
                    	//Finally add the delimiters
						$rulePattern = "/^" . $rulePattern . "$/";
					}

					$this->_routingRules[$tempRuleName]['pattern'] = $rulePattern;

				}
				else
				{
					//Invalid rule.  Remove it from the array
					unset($this->_routingRules[$tempRuleName]);
				}

			}
		}

	}

	protected function ProcessGetFileTypeURL($FileType)
	{

		$FileType = strtolower($FileType);

		if (is_set($this->_parsedEventParameters) && strlen($FileType))
		{
			$dotPosition = strpos($this->_parsedEventParameters['routing'], ".");

			if ($dotPosition === false)
			{
				$returnValue = $this->_parsedEventParameters['routing'];

				if (strlen($returnValue) > 0)
				{
					// Remove trailing slash if necessary
					if (substr($returnValue, strlen($returnValue) - 1) == "/")
					{
						$returnValue = substr($returnValue, 0, strlen($returnValue) - 1);
					}
				}
				else
				{
					//pull the page name from our default rule
					$returnValue = $this->_routingRules['default']['page'];
				}

			}
			else
			{
				$returnValue = substr($this->_parsedEventParameters['routing'], 0, $dotPosition);
			}

			$returnValue .= ".{$FileType}";

		}

		return $returnValue;
	}

	protected function ProcessGetRequestedURL()
	{

		if (is_set($this->_parsedEventParameters))
		{
			$returnValue = $this->_parsedEventParameters['routing'];
		}

		return $returnValue;
	}

	protected function ProcessGetPageBaseURL()
	{
		if (is_set($this->_parsedEventParameters))
		{
			$matchedRule = $this->_parsedEventParameters['matchedrule'];

			if (is_set($matchedRule))
			{
				if (array_key_exists('sslrequired', $this->_routingRules[$matchedRule]) && $this->_routingRules[$matchedRule]['sslrequired'] == true)
				{
					$returnValue = $this->BuildSecureURL();
				}
				else
				{
					$returnValue = $this->BuildBaseURL();
				}
			}
			else
			{
				$returnValue = $this->BuildBaseURL();
			}
		}

		return $returnValue;

	}

	protected function BuildSecureURL()
	{
		$returnValue =  Application::SecureURL();

		return $returnValue;
	}

	protected function BuildBaseURL()
	{
		$returnValue = Application::BaseURL();

		return $returnValue;
	}

	protected function ProcessGetIsUtilityFileRule()
	{

		$returnValue = false;

		//Using a switch statement here in case we have more rules someday
		switch ($this->_parsedEventParameters['matchedrule'])
		{
			case 'javascript':
			case 'resource':
				$returnValue = true;
				break;

			case 'login':
			case 'accountlogin':
			case 'signup':
				$returnValue = true;
				break;
		}

		return $returnValue;

	}

	protected function ProcessBuildURLbyRule($RuleName, $Parameters, $FileType, $IsFullFormat)
	{

		//Do we have a rule by this name?
		if (array_key_exists(strtolower($RuleName), $this->_routingRules))
		{
			//We found a rule!
			$RuleName = strtolower($RuleName);

            //Parse any parameters
        	$url = $this->FillURLparameters($RuleName, $Parameters);

        	if (array_key_exists('isexplicithtm', $this->_routingRules[$RuleName]))
        	{
        		$isExplicitHTM = true;
        	}

		}
		else
		{
			//No Rule found, we will simply return a link to this native page name
			$url = $RuleName;
			$isExplicitHTM = false;
		}

		//Add the file type
		if ($FileType == "htm")
		{
			if ($isExplicitHTM)
			{
				$url .= ".htm";
			}
		}
		else
		{
			$url .= ".{$FileType}";
		}

		//Is this full format?
		if ($IsFullFormat)
		{
			$returnValue = $this->ProcessGetPageBaseURL() . $url;
		}
		else
		{
			$returnValue = $url;
		}

		return $returnValue;
	}

    protected function FillURLparameters($RuleName, $Parameters)
    {

        $returnValue = $this->_routingRules[$RuleName]['url'];
        $processParameters = Array();

        //First, make sure all Parameter keys are lowercase
        foreach ($Parameters as $key=>$value)
        {
            $processParameters[strtolower($key)] = $value;
        }

        //Build the array of URL parameters
		$this->GenerateURLparameters($RuleName);

		//Now replace each parameter
        foreach ($this->_routingRules[$RuleName]['urlparameters'] as $tempParameter)
        {
            $tempToken = $tempParameter[0];
            $tempParameterName = strtolower($tempParameter[2]);

            if (array_key_exists($tempParameterName, $processParameters))
            {
                $returnValue = str_replace($tempToken, $processParameters[$tempParameterName], $returnValue);
            }

        }

        return $returnValue;
    }

	protected function ProcessBuildURLbyEntity($Entity, $Action, $Parameters, $FileType, $IsFullFormat)
	{

        //What is the class we are dealing with?
        if (is_object($Entity))
        {
            $entityClass = get_class($Entity);
        }
        else
        {
            $entityClass = $Entity;
        }

		//Check to see if this is an allowed root SEO page
		$allowedRootSEOpageClasses = $this->GetAllowedSEOrootClassesArray();
		
		if (array_search(strtolower($entityClass), $allowedRootSEOpageClasses) !== false)
		{
			//Root SEO pages only applicable for VIEW actions
			if (strtolower($Action) == "view")
			{
				$isRootSEOpageAllowed = true;
			}
		}

		$isRootSEOpageFound = false;

		if ($isRootSEOpageAllowed)
		{
			if (is_object($Entity))
			{
				$associatedEntityID = $Entity->PrimaryIDproperty->Value;
			}
			else
			{
				$associatedEntityID = $Parameters['associatedentityid'];
			}

			$returnValue = $this->ProcessBuildURLbyRootSEOpage($entityClass, $associatedEntityID, $FileType, $IsFullFormat);

			if (is_set($returnValue))
			{
				$isRootSEOpageFound = true;
			}
		}

		//If we didn't find a root SEO page, look for a rule.
		if ($isRootSEOpageFound == false)
		{
	        //Find the rule for this class and action
	        $ruleName = $this->FindRuleByClassAndAction($entityClass, $Action);

	        if (is_object($Entity))
	        {
	            //Build any parameters from the object itself.
	            $Parameters = $this->GenerateParametersFromObject($Entity, $ruleName, $Parameters);
	        }

	        //Now that we have the rule and parameters, build the URL from them.
	        $returnValue = $this->ProcessBuildURLbyRule($ruleName, $Parameters, $FileType, $IsFullFormat);
		}

		return $returnValue;
	}

	protected function ProcessBuildURLbyRootSEOpage($AssociatedEntityType, $AssociatedEntityID, $FileType, $IsFullFormat)
	{

		$seoPage = new SEOpage();
		$seoPage->LoadByTypeAndID($AssociatedEntityType, $AssociatedEntityID);

		if ($seoPage->IsLoaded)
		{
			$url = $seoPage->Name;

			//Add the file type
			$url .= ".{$FileType}";

			//Is this full format?
			if ($IsFullFormat)
			{
				$returnValue = $this->ProcessGetPageBaseURL() . $url;
			}
			else
			{
				$returnValue = $url;
			}

		}


		return $returnValue;
	}

    protected function FindRuleByClassAndAction($ClassName, $Action)
    {

        $isFound = false;

        foreach ($this->_routingRules as $tempRuleName=>$tempRule)
        {
            if (array_key_exists('class', $tempRule))
            {
                if (strtolower($tempRule['class']) == strtolower($ClassName))
                {
                    //We match the class...
                    if (strtolower($tempRule['action']) == strtolower($Action))
                    {
                        //We match the action.  Is this rule inbound only?

                        if (array_key_exists('isinboundonly', $tempRule) == false)
                        {
	                        //This is the rule, as long as we haven't found a prior match
	                        if ($isFound == false)
	                        {
	                            $returnValue= $tempRuleName;
	                            $isFound = true;
	                        }
                        }
                    }
                }
            }
        }

        return $returnValue;
    }

    protected function GenerateParametersFromObject($Object, $RuleName, $Parameters)
    {

		$returnValue = Array();

		//Build the list of URL parameters
		$this->GenerateURLparameters($RuleName);

        foreach ($this->_routingRules[$RuleName]['urlparameters'] as $tempParameter)
        {
            $tempToken = $tempParameter[0];
            $tempParameterName = strtolower($tempParameter[2]);

			//The parameter seopagename is a special case
			if ($tempParameterName == "seopagename")
			{
				if ($Object->HasProperty("SEOpage"))
				{
					$returnValue[$tempParameterName] = $Object->SEOpage->Name;
				}
			}
			else
			{
				if ($Object->HasProperty($tempParameterName))
				{
					$returnValue[$tempParameterName] = $Object->$tempParameterName;
 				}
			}
        }

		$returnValue = array_merge($returnValue, $Parameters);

    	return $returnValue;
    }

	protected function GenerateURLparameters($RuleName)
	{

		if (array_key_exists('urlparameters', $this->_routingRules[$RuleName]) == false)
		{
			//Numerics
			$pattern = '/(\#)([A-Za-z0-9\-\>\[\]]+)(\#)/';
			preg_match_all($pattern, $this->_routingRules[$RuleName]['url'], $numericParameters, PREG_SET_ORDER);

			//Alphanumerics
			$pattern = '/(\{)([A-Za-z0-9\-\>\[\]]+)(\})/';
			preg_match_all($pattern, $this->_routingRules[$RuleName]['url'], $alphaParameters, PREG_SET_ORDER);

			$this->_routingRules[$RuleName]['urlparameters'] = array_merge($numericParameters, $alphaParameters);
		}

	}

}
?>
