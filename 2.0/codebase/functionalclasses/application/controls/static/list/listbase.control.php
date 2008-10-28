<?php
/**
 * List Base Control Class File
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

class ListBaseControl extends StaticBaseControl
{

	protected $_listType;

   	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('list_body');
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
		else if ($Value instanceof ListItemControl)
		{
			//We only allow ListItemControls to be added to a list.
			$Value->Name = $Name;
			$Value->ParentContainer = $this;
			$Value->EventParameters = $this->_eventParameters;

            //Add this to the master control array
			$this->_controls[strtolower($Name)] = $Value;
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
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{

		//Loop through and dump each of our LIs
		foreach($this->_controls as $tempLI)
		{
			$returnValue .= $tempLI->__toString();
		}

		return $returnValue;

	}

	public function RenderControlBody()
	{

		$id = "id=\"{$this->Name}\"";

		//Open the list
		$returnValue = "<{$this->_listType} {$id} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>";

		//Now add the LI's
		$returnValue .= $this->InnerHTML;

		//Close the list
		$returnValue .= "</{$this->_listType}>";

		return $returnValue;

	}

	public function AddItem($ID, $InnerHTML = null)
	{
		if (strlen($ID) > 0)
		{
			$this->$ID = new ListItemControl();
			$this->$ID->InnerHTML = $InnerHTML;
			$this->$ID->BodyStyle->AddClass("{$this->_listType}_item");
		}

	}

	public function RemoveItem($ID)
	{
		unset($this->_controls[$ID]);
	}

	public function ClearItems()
	{
		$this->_controls = Array();
	}

}

?>
