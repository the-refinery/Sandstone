<?php
/**
 * Control Container Class File
 * @package Sandstone
 * @subpackage Application
 *
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 *
 * @copyright 2007 Designing Interactive
 *
 */

class ControlContainer extends Module
{

	protected $_name;

	protected $_parentContainer;

   	protected $_controls;
	protected $_controlOrder;

	protected $_activeControls;
	protected $_staticControls;
	protected $_hiddenControls;

    protected $_template;

	protected $_JS;
    protected $_isControlJavascriptSetup;

	protected $_eventParameters;

	protected $_isRawValuePosted;

	public function __construct()
	{
		$this->_controls = new DIarray();
		$this->_staticControls = new DIarray();
		$this->_hiddenControls = new DIarray();
		$this->_controlOrder = new DIarray();

		$this->_eventParameters = Array();

		$this->_JS = new JavascriptFunctions($this);

		$this->_isRawValuePosted = true;

	}

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue =  $this->$getter();
		}
		else if (array_key_exists(strtolower($Name), $this->_controls))
		{
			$returnValue = $this->_controls[strtolower($Name)];
		}
		else if (array_key_exists(strtolower($Name), $this->_hiddenControls))
		{
			$returnValue = $this->_hiddenControls[strtolower($Name)];
		}
		else if (array_key_exists(strtolower($Name), $this->_staticControls))
		{
			$returnValue = $this->_staticControls[strtolower($Name)];
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
		else if ($Value instanceof BaseControl)
		{
			//This is a control we should add to our control array
			$Value->Name = $Name;
			$Value->ParentContainer = $this;
			$Value->EventParameters = $this->_eventParameters;

			//Make sure we don't have any existing controls of this name.
			unset($this->_activeControls[strtolower($Name)]);
			unset($this->_staticControls[strtolower($Name)]);
			unset($this->_hiddenControls[strtolower($Name)]);

			//Add this to the master control array
			$this->_controls[strtolower($Name)] = $Value;

			//Which control type array should we use?
			if ($Value instanceof StaticBaseControl)
			{
				$this->_staticControls[strtolower($Name)] = $Value;
			}
			else if ($Value instanceof HiddenControl)
			{
				$this->_hiddenControls[strtolower($Name)] = $Value;
			}
			else
			{
				$this->_activeControls[strtolower($Name)] = $Value;
			}

			//Save the order this control was added, so for automatic rendering, they are
			//displayed in the order they were added.
			$this->_controlOrder[] = $Value;

		}
		else if(method_exists($this,'get'.$Name))
		{
			throw new InvalidPropertyException("Property $Name is read only!", get_class($this), $Name);
		}
		else
		{
			throw new InvalidPropertyException("No Writeable Property: $Name", get_class($this), $Name);
		}
	}

	/**
	 * Name property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getName()
	{

		if (is_set($this->_parentContainer))
		{
			//We have a parent, does it have a name?
			$parentName = $this->_parentContainer->Name;

			if (is_set($parentName))
			{
				$returnValue = $parentName . "_" . $this->_name;
			}
			else
			{
				$returnValue = $this->_name;
			}
		}
		else
		{
			//This doesn't have a parent, we just return it's name
			$returnValue = $this->_name;
		}

		return $returnValue;
	}

	public function setName($Value)
	{
		$this->_name = $Value;
	}

	/**
	 * Local Name property
	 *
	 * @return string
	 */
	public function getLocalName()
	{
		return $this->_name;
	}

	/**
	 * ParentContainer property
	 *
	 * @return ControlContainer
	 *
	 * @param ControlContainer $Value
	 */
	public function getParentContainer()
	{
		return $this->_parentContainer;
	}

	public function setParentContainer($Value)
	{
		if ($Value instanceof ControlContainer)
		{
			$this->_parentContainer = $Value;
		}
		else
		{
			$this->_parentContainer = null;
		}
	}

	/**
	 * Controls property
	 *
	 * @return array
	 */
	public function getControls()
	{
		return $this->_controls;
	}

	/**
	 * ActiveControls property
	 *
	 * @return Array
	 */
	public function getActiveControls()
	{
		return $this->_activeControls;
	}

	/**
	 * StaticControls property
	 *
	 * @return Array
	 */
	public function getStaticControls()
	{
		return $this->_staticControls;
	}

	/**
	 * HiddenControls property
	 *
	 * @return array
	 */
	public function getHiddenControls()
	{
		return $this->_hiddenControls;
	}

    /**
     * Template property
     *
     * @return string
     *
     * @param string $Value
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    public function setTemplate($Value)
    {
        $this->_template = $Value;
    }

	/**
	 * JS property
	 *
	 * @return JavascriptFunctions
	 */
	public function getJS()
	{
        if ($this->_isControlJavascriptSetup == false)
        {
            $this->SetupControlJavascript();
        }

        return $this->_JS;
	}

	/**
	 * EventParameters property
	 *
	 * @return Array
	 *
	 * @param Array $Value
	 */
	public function getEventParameters()
	{
		return $this->_eventParameters;
	}

	public function setEventParameters($Value)
	{
		if (is_array($Value))
		{
			$this->_eventParameters = $Value;
		}
		else
		{
			$this->_eventParameters = Array();
		}

		//Loop any existing controls and pass down the current EP
		foreach ($this->_controls as $tempControl)
		{
			$tempControl->EventParameters = $this->_eventParameters;
		}

		//Now locally parse the EPs
		$this->ParseEventParameters();

	}

	/**
	 * Page property
	 *
	 * @return Page
	 *
	 * @param Page $Value
	 */
	public function getPage()
	{
		$returnValue = $this;

		while (($returnValue instanceof BasePage) == false)
		{
			$returnValue = $returnValue->ParentContainer;
		}

		return $returnValue;
	}

   	/**
	 * Form property
	 *
	 * @return PageForm
	 *
	 * @param PageForm $Value
	 */
	public function getForm()
	{
		$returnValue = $this;

		while (is_set($returnValue) && ($returnValue instanceof PageForm) == false)
		{
			$returnValue = $returnValue->ParentContainer;
		}

		return $returnValue;

	}

	/**
	 * AllActiveControls property
	 *
	 * @return array
	 */
	public function getAllActiveControls()
	{
		$returnValue = Array();

		//Loop through the controls
		foreach($this->_controls as $tempTopLevelControl)
		{

			if (($tempTopLevelControl instanceof HiddenControl) == false && ($tempTopLevelControl instanceof StaticBaseControl == false))
			{
				//Add this top level control to our return array
				$returnValue[$tempTopLevelControl->Name] = $tempTopLevelControl;
			}
			
			//Now add any of it's children
			$returnValue = array_merge($returnValue, $tempTopLevelControl->AllActiveControls);
		}

		return $returnValue;
	}

	/**
	 * RequiredJavascriptFunctions property
	 *
	 * @return string
	 */
	public function getRequiredJavascriptFunctions()
	{
		//First dump the Form level javascript
		$returnValue = $this->JS->__toString();

		//Then loop the controls, and dump their JS functions
		foreach($this->_controls as $tempControl)
		{
			$returnValue .= $tempControl->RequiredJavascriptFunctions;
		}

		return $returnValue;
	}

	/**
	 * PostControlValue property
	 *
	 * @return string
	 */
	public function getPostControlValue()
	{

		if (count($this->_controls) > 0)
		{
			//Post the values of the child controls.
            foreach($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->PostControlValue;
			}
		}

		if ($this->_isRawValuePosted)
		{
			//Post my value
			$returnValue = "+'&{$this->Name}='+escape(\$F('{$this->Name}'))";
		}

		return $returnValue;
	}

	/**
	 * PostHiddenControlValue property
	 *
	 * @return string
	 */
	public function getPostHiddenControlValues()
	{
		foreach($this->_hiddenControls as $tempControl)
		{
			$returnValue .= $tempControl->PostControlValue;
		}

		return $returnValue;
	}

	/**
	 * ControlValueSnippet property
	 *
	 * @return string
	 */
	public function getControlValueSnippet()
	{
		if (count($this->_activeControls) > 0 || count($this->_hiddenControls) > 0)
		{
			//Post the value snippets of the child controls.
         	foreach($this->_controls as $tempControl)
			{
				$returnValue .= $tempControl->ControlValueSnippet . "\n";
			}

		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;
	}

    protected function SetupControlJavascript()
    {
        $this->_isControlJavascriptSetup = true;
	}

    final protected function ParseTemplate()
    {

		$returnValue = Array();

        $pattern = '/({(?:[^{}]+(?:"[^"]*"|\'[^\']*\')?)+})/';
        $split = preg_split ($pattern, trim ($this->_template), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($split as $code)
        {
            if (substr($code, 0, 1) == "{")
            {
                $controlName = strtolower(str_replace(Array("{", "}"), "", $code));

                //Only save valid control names.
                if (array_key_exists($controlName, $this->_controls) || array_key_exists($controlName, $this->_staticControls))
                {
                    $returnValue[$controlName] = $code;
                }
            }
        }

        return $returnValue;

    }

	final protected function FillTemplateValues()
	{

    	$controlTags = $this->ParseTemplate();

        //Begin with the template
        $returnValue = $this->_template;

        //Then replace each tag
        foreach ($controlTags as $tempControlName=>$tempControlTag)
        {
            if (array_key_exists($tempControlName, $this->_controls))
            {
            	//This is an active control
                $renderedControl = $this->_controls[$tempControlName]->__toString();
            }
            else if (array_key_exists($tempControlName, $this->_staticControls))
            {
            	//This is a static control
                $renderedControl = $this->_staticControls[$tempControlName]->__toString();
            }
            else
            {
            	//This is a hidden control, replace it with null, since we always dump
            	//all hiddens at the end
            	$renderedControl = null;
            }

            $returnValue = str_replace($tempControlTag, $renderedControl, $returnValue);
        }

		//Finally add any hidden controls
		foreach ($this->_hiddenControls as $tempHiddenControl)
		{
			if ($tempHiddenControl->IsManuallyRendered == false)
			{
				$returnValue .= $tempHiddenControl->__toString();
			}
		}

		return $returnValue;

	}

    final protected function RenderControls()
    {
        if (is_set($this->_template))
        {
            $returnValue = $this->FillTemplateValues();
        }
        else
        {
            //There is no template, so just loop the controls
            //in order and render them.
            foreach ($this->_controlOrder as $tempControl)
            {
				//Ensure we don't paint the Message or Buffer DIVs here as
				//they are handled elsewhere.
				if ($tempControl->IsManuallyRendered == false)
				{
					$returnValue .= $tempControl->__toString();
				}
            }
        }

        return $returnValue;
    }

	protected function ParseEventParameters()
	{

	}

	protected function BuildChildPostControlValues()
	{

		foreach($this->_controls as $tempControl)
		{
			$returnValue .= $tempControl->PostControlValue;
		}

		return $returnValue;
	}

	protected function ReleaseControls()
	{

		foreach ($this->_controls as $tempControl)
		{
			if ($tempControl instanceof ControlContainer)
			{
				$tempControl->ReleaseControls();
			}
		}

		$this->_controls = new DIarray();
		$this->_staticControls = new DIarray();
		$this->_hiddenControls = new DIarray();
		$this->_controlOrder = new DIarray();

		$this->_eventParameters = Array();
	}

}
?>
