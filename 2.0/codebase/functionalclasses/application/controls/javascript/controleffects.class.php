<?php
/**
 * Control Effects Class File
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

SandstoneNamespace::Using("Sandstone.Utilities.Javascript");

class ControlEffects extends Module
{

	protected $_control;

	protected $_scriptaculous;

	protected $_scope;

	public function __construct($Control)
	{
		$this->_control = $Control;

		$this->_scope = md5(microtime());

		$this->_scriptaculous = new Scriptaculous();
		$this->_scriptaculous->Scope = $this->_scope;
	}

	public function __get($Name)
	{
		$getter='get'.$Name;

		if(method_exists($this,$getter))
		{
			$returnValue =  $this->$getter();
		}
		else if (strtolower(substr($Name, -5)) == "block")
		{
			//Make sure we have a property we can work from
			$getter = 'get' . substr($Name, 0, strlen($Name)- 5);

			if (method_exists($this,$getter))
			{
				$returnValue = JavascriptFunctions::FormatJavascriptBlock($this->$getter());
			}
			else
			{
				throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
			}
		}
		else
		{
			throw new InvalidPropertyException("No Readable Property: $Name", get_class($this), $Name);
		}

		return $returnValue;
	}

	/**
	 * Control property
	 *
	 * @return Control
	 */
	public function getControl()
	{
		return $this->_control;
	}

	/**
	 * Scope property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getScope()
	{
		return $this->_scope;
	}

	public function setScope($Value)
	{
		if (strlen($Value) > 0)
		{
			$this->_scope = $Value;
		}
		else
		{
			$this->_scope = md5(microtime());
		}

		$this->_scriptaculous->Scope = $this->_scope;

	}

	public function getInnerHTML()
	{
		//Escape any single quotes in strings
		$innerHTML = str_replace("'", "\'", $this->_control->InnerHTML);

		//Clean up any double escaped single quotes
		$innerHTML = str_replace("\\\\'", "\'", $innerHTML);

		$returnValue = "\$('{$this->_control->MasterControlDOMid}').innerHTML = '{$innerHTML}'; ";

		return $returnValue;
	}

	/**
	 * Highlight property
	 *
	 * @return string
	 */
	 public function getHighlight()
	 {

	 	foreach($this->_control->HighlightDOMids as $tempID)
	 	{
			$returnValue .= $this->_scriptaculous->Highlight($tempID);
	 	}

	 	 return $returnValue;

	 }

	 public function getBlindDown()
	 {
		return $this->_scriptaculous->BlindDown($this->_control->MasterControlDOMid);
	 }

     public function getBlindUp()
	 {
		return $this->_scriptaculous->BlindUp($this->_control->MasterControlDOMid);
	 }

     public function getPuff()
	 {
		return $this->_scriptaculous->Puff($this->_control->MasterControlDOMid);
	 }

     public function getSwitchOff()
	 {
		return $this->_scriptaculous->SwitchOff($this->_control->MasterControlDOMid);
	 }

     public function getShow()
     {
         return $this->_scriptaculous->Show($this->_control->MasterControlDOMid);
     }

     public function getHide()
     {
        return $this->_scriptaculous->Hide($this->_control->MasterControlDOMid);
     }

	 public function getScrollTo()
	 {
	 	return $this->_scriptaculous->ScrollTo($this->_control->MasterControlDOMid);
	 }

}
?>
