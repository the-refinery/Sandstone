<?php
/*
Select Control Element Class File

@package Sandstone
@subpackage Application
*/

class SelectControlElementGroup extends Renderable
{
	protected $_control;

	protected $_elements;

	protected $_groupName;

	public function __construct($GroupName, $Control)
	{
		parent::__construct();

		$this->_control = $Control;
		$this->_groupName = $GroupName;

		$this->_elements = Array();

		$this->_template->FileName = "selectelementgroup";
		$this->_template->RequestFileType = $Control->Template->RequestFileType;
	}

	/*
	GroupName property

	@return string
	@param string $Value
	 */
	public function getGroupName()
	{
		return $this->_groupName;
	}

	public function setGroupName($Value)
	{
		$this->_groupName = $Value;
	}

	public function AddElement($Key, $Element)
	{
		$this->_elements[$Key] = $Element;
	}

	public function ClearElements()
	{
		$this->_elements = Array();
	}

	public function Render()
	{

		$this->_template->GroupName = $this->_groupName;

        foreach($this->_elements as $tempElement)
		{
			$tempElement->Template->RequestFileType = $this->_template->RequestFileType;
			$options .= $tempElement->Render() . "\n";
		}

		$this->_template->Elements = $options;

		$returnValue = parent::Render();

		return $returnValue;
	}

}
?>