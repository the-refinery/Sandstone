<?php
/**
Template Class File

@package Sandstone
@subpackage Application
 */

class Template extends Module
{

  const CACHE_FILE_NAME = "templatedirs.cache";

  protected $_parentObject;
  protected $_requestFileType;

  protected $_fileName;

  protected $_templateString;

  protected $_isMasterLayoutUsed;
  protected $_masterLayoutFileName;

  protected $_templateVariables = Array();

  protected $_renderOutput;
  protected $_includedFiles = Array();

  protected $_isDebugMode;

  protected $_isRendered;

  protected $_additionalTemplatePath;

  public function __construct($ParentObject)
  {
    if ($ParentObject instanceof Renderable)
    {
      $this->_parentObject = $ParentObject;
      $this->_templateVariables["debugparentobjecttype"] = get_class($this->_parentObject);
    }

    $this->_isRendered = true;
  }

  // Lets us set template variables like properties
  public function __get($Name)
  {
    $getter='get'.$Name;

    if(method_exists($this,$getter))
    {
      $returnValue =  $this->$getter();
    }
    else if (array_key_exists(strtolower($Name), $this->_templateVariables))
    {
      $returnValue = $this->_templateVariables[strtolower($Name)];
    }
    else
    {
      throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
    }

    return $returnValue;
  }

  public function __set($Name,$Value)
  {
    $setter='set'.$Name;

    if(method_exists($this,$setter))
    {
      $this->$setter($Value);
    }
    else if(method_exists($this,'get'.$Name))
    {
      throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
    }
    else
    {
      //Add it to our TV array
      $this->_templateVariables[strtolower($Name)] = $Value;
    }
  }

  /*
  ParentContainer property

  @return ControlContainer
   */
  public function getParentObject()
  {
    return $this->_parentObject;
  }

  /*
  RequestFileType property

  @return string
  @param string $Value
   */
  public function getRequestFileType()
  {
    return $this->_requestFileType;
  }

  public function setRequestFileType($Value)
  {
    $this->_requestFileType = strtolower($Value);

    //Cascade this value through any sub controls if our
    //parent is a control container
    if ($this->_parentObject instanceof ControlContainer)
    {
      foreach ($this->_parentObject->Controls as $tempControl)
      {
        $tempControl->Template->RequestFileType = $this->_requestFileType;
      }
    }
  }

  /*$this->_template = new Template($this);

  FileName property

  @return string
  @param string $Value
   */
  public function getFileName()
  {
    return $this->_fileName;
  }

  public function setFileName($Value)
  {
    $this->_fileName = $Value;
  }

  /*
  TemplateString property

  @return string
  @param string $Value
   */
  public function getTemplateString()
  {
    return $this->_templateString;
  }

  public function setTemplateString($Value)
  {
    $this->_templateString = $Value;
  }

    /*
    IsMasterLayoutUsed property

    @return boolean
    @param boolean $Value
     */
  public function getIsMasterLayoutUsed()
  {
    return $this->_isMasterLayoutUsed;
  }

  public function setIsMasterLayoutUsed($Value)
  {
    $this->_isMasterLayoutUsed = $Value;
  }

    /*
    MasterLayoutFileName property

    @return string
    @param string $Value
     */
  public function getMasterLayoutFileName()
  {
    return $this->_masterLayoutFileName;
  }

  public function setMasterLayoutFileName($Value)
  {
    $this->_masterLayoutFileName = $Value;
  }

  /*
  IsDebugMode property

  @return boolean
  @param boolean $Value
   */
  public function getIsDebugMode()
  {
    return $this->_isDebugMode;
  }

  public function setIsDebugMode($Value)
  {
    $this->_isDebugMode = $Value;
  }

  /*
  IsRendered property

  @return boolean
  @param boolean $Value
   */
  public function getIsRendered()
  {
    return $this->_isRendered;
  }

  public function setIsRendered($Value)
  {
    $this->_isRendered = $Value;
  }

  /*
  TemplateVariables property

  @return array
   */
  public function getTemplateVariables()
  {
    return $this->_templateVariables;
  }

  /*
  AdditionalTemplatePath property

  @return string
  @param string $Value
   */
  public function getAdditionalTemplatePath()
  {
    return $this->_additionalTemplatePath;
  }

  public function setAdditionalTemplatePath($Value)
  {
    $this->_additionalTemplatePath = $Value;
  }

  public function Display()
  {
    echo $this->BuildDisplayOutput();
    die();
  }

  public function BuildDisplayOutput()
  {

    $divColor = "#9cc";
    $liColor = "#ffc";
    $liBorder = "#fcc";

    $templateFound = $this->SetupTemplateString();
    $templateOutput = $this->Render();

    $returnValue = "<div style=\"border: 0; background-color: {$divColor}; padding: 6px; margin-top: 10px;\">";
    $returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">Template</h1>";

    $returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

    $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";
    $returnValue .= "<strong>File Name: </strong> {$this->_fileName}";
    $returnValue .= "</li>";

    $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";
    $returnValue .= "<strong>Request File Type: </strong> {$this->_requestFileType}";
    $returnValue .= "</li>";

    $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";
    $returnValue .= "<strong>Template Content: </strong> ( {$this->_fileName}.{$this->_requestFileType}.template )<br />";
    $returnValue .=  "<textarea rows=\"10\" cols=\"100\">{$this->_templateString}</textarea>";
    $returnValue .= "</li>";

    $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";
    $returnValue .= "<strong>Rendered Output: </strong> <br />";
    $returnValue .=  "<textarea rows=\"10\" cols=\"100\">{$templateOutput}</textarea>";
    $returnValue .= "</li>";

    $returnValue .= "</ul>";

    $returnValue .= $this->BuildTemplateVariableDisplayOutput();

    $returnValue .= "</div>";

    return $returnValue;

  }

  protected function BuildTemplateVariableDisplayOutput()
  {
    $divColor = "#9cc";
    $liColor = "#ffc";
    $liBorder = "#fcc";

    $returnValue .= "<h1 style=\"padding: 0; margin: 0; border-bottom: 1px solid #000;\">Template Variables</h1>";
    $returnValue .= "<ul style=\"list-style: none; margin: 4px;\">";

    foreach ($this->_templateVariables as $key=>$value)
    {
      if (substr($key, 0, 5) != "debug")
      {
        $returnValue .= "<li style=\"border: 1px solid {$liBorder}; margin: 2px; padding: 4px; background-color: {$liColor};\">";

        $returnValue .= "<strong>{$key}: </strong> ";

        if ($value instanceof DIarray)
        {
          $returnValue .= $value->__toString();
        }
        elseif (is_object($value))
        {
          $returnValue .= $value->__toString();
        }
        else
        {
          if (is_numeric($value) || $value == "null")
          {
            $returnValue .= "{$value}";
          }
          else
          {
            $returnValue .= "\"{$value}\"";
          }
        }

        $returnValue .= "</li>";
      }
    }

    $returnValue .= "</ul>";

    return $returnValue;
  }

  public function Render()
  {

    if ($this->_isRendered)
    {
      Benchmark::Log("Template","Render - ({$this->_requestFileType}) {$this->_fileName}");

      $this->_templateVariables["debugtvdisplay"] = $this->BuildTemplateVariableDisplayOutput();


      //Do we need to load a template file?
      if (strlen($this->_templateString) == 0)
      {
        $isTemplateFound = $this->SetupTemplateString();
      }
      else
      {
        $isTemplateFound = true;
      }

      //Are we to use a layout file?
      if ($this->_isMasterLayoutUsed && $isTemplateFound)
      {
        $layoutContents = $this->LoadMasterLayout();

        $this->_templateString = str_replace("{Content}", $this->_templateString, $layoutContents);
      }

      if ($isTemplateFound && is_set($this->_parentObject))
      {
        $returnValue = $this->ParseTemplate();
      }
      else
      {
        $returnValue = null;
      }
    }
    else
    {
      $returnValue = null;
    }

    return $returnValue;
  }

  protected function SetupTemplateString()
  {

    //Have we been given a specific file name?
    if (is_set($this->_fileName))
    {
      //Look for a template file with the custom name.
      $returnValue = $this->LoadTemplateByName($this->_fileName);
    }
    else
    {
      //No Custom name to look for
      $returnValue = false;
    }

    //Do we have an object name to look for?
    if ($returnValue == false)
    {
      //If our parent is some type of control container, look for a template with its name.
      if ($this->_parentObject instanceof ControlContainer)
      {
        //We don't yet have a template file loaded, look for one by our parent's object name
        $returnValue = $this->LoadTemplateByName(strtolower($this->_parentObject->LocalName));
      }
      else
      {
        $returnValue = false;
      }
    }

    if ($returnValue == false)
    {
      if ($this->_parentObject instanceof EntityPage)
      {
        $templateFilename = $this->_parentObject->DetermineEntityTemplateFilename();
        $returnValue = $this->LoadTemplateByName($templateFilename);
      }
      else if ($this->_parentObject instanceof BasePage)
      {
        //Since pages are each their own class type, to simply use get_class would be
        //redundant with the search by name.  We'll default to "basepage" as the filename
        //we are looking for.
        $returnValue = $this->LoadTemplateByName("basepage");
      }
      else if ($this->_parentObject instanceof BaseControl)
      {
        //Do we use this control's type, or a parent's type?
        if ($this->_parentObject->IsParentTemplateUsed)
        {
          $controlType = strtolower(get_parent_class($this->_parentObject));
        }
        else
        {
          $controlType = strtolower(get_class($this->_parentObject));
        }

        //We always strip the "control" from the end of the class when
        //we are looking for
        $controlType = str_replace("control", "", $controlType);

        $returnValue = $this->LoadTemplateByName($controlType);
      }
      else
      {
        //Everybody other than pages uses their class.
        $returnValue = $this->LoadTemplateByName(strtolower(get_class($this->_parentObject)));
      }
    }

    return $returnValue;
  }

  protected function LoadTemplateByName($FileName)
  {

    $fullTemplateFileName = $FileName . ".{$this->_requestFileType}.template";

    //Get the file's contents
    $templateContents = $this->LoadTemplateFile($fullTemplateFileName);

    if (strlen($templateContents) > 0)
    {
      $this->_templateString = $templateContents;
      $this->_fileName = $FileName;
      $returnValue = true;
    }
    else
    {
      //No Template or Layout found!
      $returnValue = false;
    }

    return $returnValue;

  }

  protected function LoadMasterLayout()
  {
    //If we weren't given a filespec, use the default
    if (strlen($this->_masterLayoutFileName) == 0)
    {
      $this->_masterLayoutFileName = "default";
    }

    $masterLayoutFileSpec = "resources/layouts/{$this->_masterLayoutFileName}.{$this->_requestFileType}.layout";

    //Make sure the file exists.
    if (file_exists_incpath($masterLayoutFileSpec))
    {
      $returnValue = file_get_contents($masterLayoutFileSpec, FILE_USE_INCLUDE_PATH);
    }
    else
    {
      //Give back a string that just includes the contents token.  This will allow everything
      //else to work without having to check the return value of this function.
      $returnValue = "{Content}";
    }

    return $returnValue;
  }

  protected function ParseTemplate()
  {
    //Render process employs the following order of operations:
    //
    // 1) Include Files {!
    // 2) Comments  {#
    // 3) Template Variables {$
    // 4) Forms and Controls {+
    // 5) URL Builders [=>

    $this->_renderOutput = $this->_templateString;

    $this->_renderOutput = $this->ParseConditionalTemplateIncludes($this->_renderOutput);
    $this->_renderOutput = $this->ParseTemplateIncludes($this->_renderOutput);
    $this->_renderOutput = $this->ParseTemplateComments($this->_renderOutput);
    $this->_renderOutput = $this->ParseTemplateVariables($this->_renderOutput);
    $this->_renderOutput = $this->ParseTemplateFormsAndControls($this->_renderOutput);
    $this->_renderOutput = $this->ParseTemplateURLbuilders($this->_renderOutput);

    $returnValue = $this->_renderOutput;

    return $returnValue;
  }

  protected function ParseConditionalTemplateIncludes($Template)
  {

    $returnValue = $Template;

    $pattern = '/(\{!\?)(.*)(\})/';
    preg_match_all($pattern, $Template, $files, PREG_SET_ORDER);

    //Now loop through and attempt to include each file
    foreach ($files as $tempFile)
    {
      $tempToken = $tempFile[0];
      $tempTokenParameters = explode(",", $tempFile[2]);
      $tempTVname = strtolower($tempTokenParameters[0]);
      $tempFileName = $tempTokenParameters[1];

      //Does this Template Variable exist?
      if (array_key_exists($tempTVname, $this->_templateVariables))
      {
        $tvValue = $this->_templateVariables[$tempTVname];
      }
      else
      {
        //No TV by that name. Evaluate False
        $tvValue = false;
      }

      //Did the TV evaluate to true?
      if ($tvValue == true)
      {

        //Only include files we haven't already processed
        if (array_key_exists($tempFileName, $this->_includedFiles) == false)
        {
          //Mark it as used
          $this->_includedFiles[$tempFileName] = true;

          //Pull in it's contents
          $newFileContents = $this->LoadTemplateFile($tempFileName);

          if (strlen($newFileContents) > 0)
          {
            //Check for any cascading Includes
            $newFileContents = $this->ParseConditionalTemplateIncludes($newFileContents);
            $newFileContents = $this->ParseTemplateIncludes($newFileContents);
          }

          //Replace the token with the file contents (limit of 1 replacement)
          $preg_token = '/\{!\?' . $tempFile[2] . '\}/';
          $returnValue = preg_replace($preg_token, $newFileContents, $returnValue, 1);
        }

      }

      //Now replace any remaining copies of the token with nulls
      $returnValue = str_replace($tempToken, "", $returnValue);

    }

    return $returnValue;
  }

  protected function ParseTemplateIncludes($Template)
  {

    $returnValue = $Template;

    $pattern = '/(\{!)(.*)(\})/';
    preg_match_all($pattern, $Template, $files, PREG_SET_ORDER);

    //Now loop through and attempt to include each file
    foreach ($files as $tempFile)
    {
      $tempToken = $tempFile[0];
      $tempFileName = $tempFile[2];

      //Only include files we haven't already processed
      if (array_key_exists($tempFileName, $this->_includedFiles) == false)
      {
        //Mark it as used
        $this->_includedFiles[$tempFileName] = true;

        //Pull in it's contents
        $newFileContents = $this->LoadTemplateFile($tempFileName);

        if (strlen($newFileContents) > 0)
        {
          //Check for any cascading Includes
          $newFileContents = $this->ParseConditionalTemplateIncludes($newFileContents);
          $newFileContents = $this->ParseTemplateIncludes($newFileContents);
        }

        //Replace the token with the file contents (limit of 1 replacement)
        $preg_token = '/\{!' . $tempFileName . '\}/';
        $returnValue = preg_replace($preg_token, $newFileContents, $returnValue, 1);
      }

      //Now replace any remaining copies of the token with nulls
      $returnValue = str_replace($tempToken, "", $returnValue);

    }

    return $returnValue;
  }

  protected function LoadTemplateFile($FileName)
  {
    //Save the Application IncludePath
    $applicationIncludePath = get_include_path();

    $templateIncludePath = $this->BuildTemplateIncludePath();

    //Set the actual PHP include path
    set_include_path($templateIncludePath);

    //Look for the file
    if (file_exists_incpath($FileName))
    {
      //Found it!
      $returnValue = file_get_contents($FileName, FILE_USE_INCLUDE_PATH);
    }
    else
    {
      //404
      $returnValue = null;
    }

    //Restore the application Include Path
    set_include_path($applicationIncludePath);

    return $returnValue;
  }

  protected function BuildTemplateIncludePath()
  {
    //First, let's build a custom include path for templates
    if ($this->_parentObject->hasProperty("Page"))
    {
      $returnValue = $this->_parentObject->Page->TemplateSearchPath;

      if (strlen($returnValue) > 0)
      {
        $returnValue .= PATH_SEPARATOR;
      }
    }

    //Now add the path for the application, sandstone and any used top level namespaces
    $returnValue .= Namespace::TemplateSearchPath();

    //If we have some additional path, add it to the end
    if (is_set($this->_additionalTemplatePath))
    {
      $returnValue .= PATH_SEPARATOR . $this->_additionalTemplatePath;
    }

    return $returnValue;
  }

  protected function ParseTemplateComments($Template)
  {
    //This will remove all comments.
    $pattern = '/\{#.*\}/';

    $returnValue = $this->_renderOutput = preg_replace($pattern, "", $Template);

    return $returnValue;
  }

  protected function ParseTemplateVariables($Template)
  {

    $returnValue = $Template;

    $pattern = '/(\{\$)([A-Za-z0-9\-\>\[\]]+)(\})/';
    preg_match_all($pattern, $Template, $tvs, PREG_SET_ORDER);

    foreach ($tvs as $tempTV)
    {
      $tempToken = $tempTV[0];
      $tempTVname = strtolower($tempTV[2]);

      //Do we have a property reference?
      if (strpos($tempTVname, "->") !== false)
      {
        //Reference to a property of the TV, determine what we should replace it with
        $tvValue = $this->ParseTVobjectProperty($tempTVname);
      }
      else
      {
        //Simple TV - Do we have it defined?
        if (array_key_exists($tempTVname, $this->_templateVariables))
        {
          $tvValue = $this->_templateVariables[$tempTVname];
        }
        else
        {
          //No TV by that name. Remove it
          $tvValue = "";
        }
      }

      //Replace the TV reference with whatever value has been calculated
      $returnValue = str_replace($tempToken, $tvValue, $returnValue);

    }

    return $returnValue;
  }

  protected function ParseTVobjectProperty($FullTVname)
  {
    //What is our base TV?
    $propertyReferenceStart = strpos($FullTVname, "->");

    $tvName = substr($FullTVname, 0, $propertyReferenceStart);



    //Is it defined?
    if (array_key_exists($tvName, $this->_templateVariables))
    {
      $propertyReference = substr($FullTVname, $propertyReferenceStart);

      //Build a string to evaluate
      $evalString = "\$returnValue = \$this->_templateVariables[\$tvName]{$propertyReference};";

      //Determine the property Value
      eval($evalString);

    }
    else
    {
      //Not defined, so empty string is the replacement value
      $returnValue = "";
    }

    return $returnValue;

  }

  protected function ParseTemplateFormsAndControls($Template)
  {
    $returnValue = $Template;

    $pattern = '/(\{\+)([A-Za-z0-9]+)(\})/';
    preg_match_all($pattern, $Template, $controls, PREG_SET_ORDER);

    foreach ($controls as $tempControl)
    {

      $tempToken = $tempControl[0];
      $tempControlName = strtolower($tempControl[2]);

      //We can only process forms and templates if our parent is some type of control container
      if ($this->_parentObject instanceof ControlContainer)
      {
        //Does this Control Exist?
        if (array_key_exists($tempControlName, $this->_parentObject->Controls))
        {
          //This is a control
          $controlOutPut = $this->_parentObject->Controls[$tempControlName]->Render();
          $returnValue = str_replace($tempToken, $controlOutPut, $returnValue);
        }
        else if($this->_parentObject instanceof BasePage && array_key_exists($tempControlName, $this->_parentObject->Forms))
        {
          //this is a form
          $formOutput = $this->_parentObject->Forms[$tempControlName]->Render();
          $returnValue = str_replace($tempToken, $formOutput, $returnValue);
        }
        else
        {
          //No Control or Form by that name. Remove the token
          $returnValue = str_replace($tempToken, "", $returnValue);
        }
      }
      else
      {
        //Simply remove any form or control token
        $returnValue = str_replace($tempToken, "", $returnValue);
      }
    }

    return $returnValue;

  }

  protected function ParseTemplateURLbuilders($Template)
  {

    $returnValue = $Template;

    $pattern = '/(\[=>)(.*?)(\])/';
    preg_match_all($pattern, $Template, $urls, PREG_SET_ORDER);

    foreach ($urls as $tempURL)
    {
      $tempToken = $tempURL[0];
      $tempURLdata = strtolower($tempURL[2]);

      //First let's pull out any named URL parameters
      $pattern = "/(\{)(.*)(\})/";
      preg_match_all($pattern, $tempURLdata, $parameterString, PREG_SET_ORDER);

      if (count($parameterString) > 0)
      {
        $tempURLdata = str_replace($parameterString[0][0], "PARAMETERS", $tempURLdata);

        //Build our array of parameters
        $urlParameters = $this->BuildURLparametersFromString($parameterString[0][2]);
      }

      //Now explode the URLbuilder parameters into an array
      $builderParameters = explode(",", $tempURLdata);

      //Determine which BuildURL method to call, and call it.
      if (count($builderParameters) == 1)
      {
        //This uses the rule name
        $urlOutput = $this->ProcessRuleNameType($builderParameters, $urlParameters);
      }
      else
      {

        //If the 2nd URL Builder parameter is the Parms Array, this is
        //a rulename format
        if (trim($builderParameters[1]) == "PARAMETERS")
        {
          $urlOutput = $this->ProcessRuleNameType($builderParameters, $urlParameters);
        }
        else
        {
          //This is an entity format
          $urlOutput = $this->ProcessEntityType($builderParameters, $urlParameters);
        }

      }

      $returnValue = str_replace($tempToken, $urlOutput, $returnValue);
    }

    return $returnValue;

  }

  protected function BuildURLparametersFromString($ParametersString)
  {
    $returnValue = Array();

    $keyValuePairs = explode(",", $ParametersString);

    foreach ($keyValuePairs as $tempPair)
    {
      $tempSplit = explode(":", $tempPair);

      $returnValue[trim($tempSplit[0])] = $tempSplit[1];
    }

    return $returnValue;
  }

  protected function ProcessRuleNameType($BuildParameters, $URLparameters)
  {

    //First, lets setup the builder parameters and make sure they
    //are formatted correctly
    $ruleName = trim($BuildParameters[0]);
    $fileType = trim($BuildParameters[2]);
    $isFullFormat = trim($BuildParameters[3]);

    //Convert to a true boolean value
    if (strtolower($isFullFormat)== "true" || $isFullFormat == 1)
    {
      $isFullFormat = true;
    }
    else
    {
      $isFullFormat = false;
    }

    //Now how do we need to call the function?
    switch (count($BuildParameters))
    {
    case 1:
      //Rule Name only
      $returnValue = Routing::BuildURLbyRule($ruleName);
      break;

    case 2:
      //Just name and parameter
      $returnValue = Routing::BuildURLbyRule($ruleName, $URLparameters);
      break;

    case 3:
      //Name, parameter and filetype
      $returnValue = Routing::BuildURLbyRule($ruleName, $URLparameters, $fileType);
      break;

    case 4:
      //Name, parameter, filetype and format
      $returnValue = Routing::BuildURLbyRule($ruleName, $URLparameters, $fileType, $isFullFormat);
      break;
    }

    return $returnValue;
  }

  protected function ProcessEntityType($BuildParameters, $URLparameters)
  {

    //First, lets setup the builder parameters and make sure they
    //are formatted correctly
    $entity = trim($BuildParameters[0]);
    $action = trim($BuildParameters[1]);
    $fileType = trim($BuildParameters[3]);
    $isFullFormat = trim($BuildParameters[4]);

    //Convert to a true boolean value
    if (strtolower($isFullFormat)== "true" || $isFullFormat == 1)
    {
      $isFullFormat = true;
    }
    else
    {
      $isFullFormat = false;
    }

    //Now check to see if a TV object was identified for the entity
    //versus just a class name
    if (substr($entity, 0, 1) == "\$")
    {
      $tvKey = strtolower(substr($entity, 1));
      $entity = $this->_templateVariables[$tvKey];
    }



    //Now how do we need to call the function?
    switch (count($BuildParameters))
    {
    case 2:
      //Entity and Action
      $returnValue = Routing::BuildURLbyEntity($entity, $action);
      break;

    case 3:
      //Entity, Action and Parameters
      $returnValue = Routing::BuildURLbyEntity($entity, $action, $URLparameters);
      break;

    case 4:
      //Entity, Action, Parameters and File Type
      $returnValue = Routing::BuildURLbyEntity($entity, $action, $URLparameters, $fileType);
      break;

    case 5:
      //Entity, Action, Parameters, File Type and IsFullFormat
      $returnValue = Routing::BuildURLbyEntity($entity, $action, $URLparameters, $fileType, $isFullFormat);
      break;
    }

    return $returnValue;
  }

  public function CloneTemplateVariables($SourceTemplate)
  {
    if ($SourceTemplate instanceof Template)
    {
      foreach ($SourceTemplate->TemplateVariables as $key=>$value)
      {
        if (array_key_exists($key, $this->_templateVariables) == false)
        {
          $this->_templateVariables[$key] = $value;
        }
      }
    }
  }

  public function DestroyTemplateVariables()
  {
    foreach ($this->_templateVariables as $tempTV)
    {
      if ($tempTV instanceof EntityBase || $tempTV instanceof DIarray || $tempTV instanceof EntityChildren)
      {
        $tempTV->Destroy();
      }

      $this->_templateVariables = null;
    }
  }

  public static function FindDirectoriesWithTemplates($Directory)
  {

    if (file_exists($Directory))
    {
      if (Application::Registry()->DevMode == 1)
      {
        $returnValue = Template::FindDirectoriesDevelopmentMode($Directory);
      }
      else
      {
        $returnValue = Template::FindDirectoriesProductionMode($Directory);
      }
    }
    else
    {
      $returnValue = Array();
    }

    return $returnValue;
  }

  protected static function FindDirectoriesProductionMode($Directory)
  {

    $cacheFileSpec = $Directory . Template::CACHE_FILE_NAME;

    if (file_exists($cacheFileSpec))
    {
      $returnValue = Template::FindDirectoriesFromCacheFile($Directory, $cacheFileSpec);
    }
    else
    {
      $returnValue = Template::FindDirectoriesFromFileSystem($Directory);

      //Build the cache File
      $handle = fopen($cacheFileSpec, "w");

      foreach ($returnValue as $tempDirectory)
      {
        if ($tempDirectory == $Directory)
        {
          $content = ".";
        }
        else
        {
          $content = substr($tempDirectory, strlen($Directory));
        }

        fwrite($handle, $content . "\n");
      }


    }

    return $returnValue;

  }

  protected static function FindDirectoriesDevelopmentMode($Directory)
  {
    $cacheFileSpec = $Directory . Template::CACHE_FILE_NAME;

    if (file_exists($cacheFileSpec))
    {
      unlink($cacheFileSpec);
    }

    $returnValue = Template::FindDirectoriesFromFileSystem($Directory);


    return $returnValue;
  }


  protected static function FindDirectoriesFromCacheFile($Directory, $CacheFileSpec)
  {
    $returnValue = Array();

    $directoryList = ReadFileContents($CacheFileSpec);

    foreach ($directoryList as $tempDirectory)
    {
      if ($tempDirectory == ".")
      {
        $returnValue[] = $Directory;
      }
      else
      {
        $returnValue[] = $Directory . $tempDirectory;
      }
    }

    return $returnValue;
  }

  protected static function FindDirectoriesFromFileSystem($Directory)
  {
    $returnValue = Array();

    //First, does the directory we are passed contain templates?
    $pattern = $Directory . "*.template";
    $templates = glob($pattern);

    if (count($templates) > 0)
    {
      //There are templates here
      $returnValue[] = $Directory;
    }

    //Second, are there any sub directories?
    $pattern = $Directory . "*";
    $subDirs = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);

    foreach ($subDirs as $tempDirectory)
    {
      $subPaths = Template::FindDirectoriesFromFileSystem($tempDirectory);
      $returnValue = array_merge($returnValue, $subPaths);
    }

    return $returnValue;

  }

}

?>
