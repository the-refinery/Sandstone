<?php
/**
 * Entity Relationship Control Class File
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

class EntityRelationshipControl extends MultiPartBaseControl
{

	protected $_parentEntityID;
	protected $_currentRelationsClass;
	protected $_currentRelationsFunction;

	protected $_displayValueFormat;
	protected $_displayTextFormat;

   	protected $_displayValueProperties;
	protected $_displayTextProperties;

	protected $_displayDataset;

	public function __construct()
	{
		parent::__construct();

		//Setup the default style classes
		$this->_styles->AddControlClass('entityrelationship_general');
		$this->_styles->AddMessageClass('entityrelationship_message');
		$this->_styles->AddLabelClass('entityrelationship_displayLabel');
		$this->_styles->AddItemClass('entityrelationship_item');

		$this->_styles->AddSubItem("DisplayDIV");
		$this->_styles->AddSubItem("SelectDIV");
		$this->_styles->AddSubItem("AddDIV");

		$this->_styles->AddSubItemClass("DisplayDIV", 'entityrelationship_displaydiv');
		$this->_styles->AddSubItemClass("SelectDIV", 'entityrelationship_selectdiv');
		$this->_styles->AddSubItemClass("AddDIV", 'entityrelationship_adddiv');

	}

	/**
	 * ParentEntityID property
	 *
	 * @return int
	 *
	 * @param int $Value
	 */
	public function getParentEntityID()
	{
		return $this->_parentEntityID;
	}

	public function setParentEntityID($Value)
	{
		$this->_parentEntityID = $Value;
	}

	/**
	 * CurrentRelationsClass property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getCurrentRelationsClass()
	{
		return $this->_currentRelationsClass;
	}

	public function setCurrentRelationsClass($Value)
	{
		$this->_currentRelationsClass = $Value;
	}

	/**
	 * CurrentRelationsFunction property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getCurrentRelationsFunction()
	{
		return $this->_currentRelationsFunction;
	}

	public function setCurrentRelationsFunction($Value)
	{
		$this->_currentRelationsFunction = $Value;
	}

	/**
	 * DisplayValueFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getDisplayValueFormat()
	{
		return $this->_displayValueFormat;
	}

	public function setDisplayValueFormat($Value)
	{
		$this->_displayValueFormat = $Value;
		$this->_displayValueProperties = $this->ParseFormatProperties($Value);
	}

	/**
	 * DisplayLabelFormat Property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getDisplayTextFormat()
	{
		return $this->_displayTextFormat;
	}

	public function setDisplayTextFormat($Value)
	{
		$this->_displayTextFormat = $Value;
		$this->_displayTextProperties = $this->ParseFormatProperties($Value);
	}

    protected function SetupPartNames()
	{

		if (is_set($this->_partNames) == false)
		{
			$this->_partNames['Display'] = "{$this->_name}_Display";
			$this->_partNames['Select'] = "{$this->_name}_Select";
			$this->_partNames['Add'] = "{$this->_name}_Add";

			parent::SetupPartNames();

			$this->GenerateControlJavascript();

		}

	}

	protected function GenerateControlJavascript()
	{
		$this->_JS['Control']->ShowSelect->Add("new Element.show('{$this->_partNames['Select']}');");
		$this->_JS['Control']->ShowSelect->Add("new Element.hide('{$this->_partNames['Display']}');");
		$this->_JS['Control']->ShowSelect->Add("new Element.hide('{$this->_partNames['Add']}');");

		$this->_JS['Control']->ShowDisplay->Add("new Element.show('{$this->_partNames['Display']}');");
		$this->_JS['Control']->ShowDisplay->Add("new Element.hide('{$this->_partNames['Select']}');");
		$this->_JS['Control']->ShowDisplay->Add("new Element.hide('{$this->_partNames['Add']}');");

		$this->_JS['Control']->ShowAdd->Add("new Element.show('{$this->_partNames['Add']}');");
		$this->_JS['Control']->ShowAdd->Add("new Element.hide('{$this->_partNames['Select']}');");
		$this->_JS['Control']->ShowAdd->Add("new Element.hide('{$this->_partNames['Display']}');");

	}

	protected function RenderControlBody()
	{

		$returnValue .= $this->RenderDisplayDIV();
		$returnValue .= $this->RenderSelectDIV();
		$returnValue .= $this->RenderAddDIV();

		return $returnValue;
	}

	protected function RenderDisplayDIV()
	{

		$id = "id=\"{$this->_partNames['Display']}\"";

		//Get the display dataset
		$methodCall = "\$this->_displayDataset = {$this->_currentRelationsClass}::{$this->_currentRelationsFunction}({$this->_parentEntityID});";
		eval($methodCall);

		$returnValue = "<div {$id}>";

		if(is_set($this->_displayTextFormat) && is_set($this->_displayValueFormat) && is_set($this->_displayDataset) && $this->_displayDataset)
		{

			$returnValue .= "<ul>";

			while ($tempItem = $this->_displayDataset->FetchItem())
			{
				$value = $this->FillFormatValues($this->_displayValueFormat, $this->_displayValueProperties, $tempItem);
				$text = $this->FillFormatValues($this->_displayTextFormat, $this->_displayTextProperties, $tempItem);

				$returnValue .= "<li>{$text}</li>";
			}

			$returnValue .= "</ul>";
		}

		$returnValue .= "<a href=\"javascript:void(0)\" onclick=\"{$this->_name}_ShowSelect();\">Add</a>";

		$returnValue .= "</div>";

		return $returnValue;
	}

	protected function RenderSelectDIV()
	{
		$id = "id=\"{$this->_partNames['Select']}\"";

		$returnValue = "<div {$id} style=\"display: none;\">";

		$returnValue .= "<h2>Select Area</h2>";

		$foo = new DropDownControl();
		$foo->Name = "{$this->_name}_AvailableDropdown";
		$foo->Page = $this->_page;
		$foo->Form = $this->_form;
		$foo->AddElementGroup("Goerlich");
		$foo->AddElement(1, "Dave");
		$foo->AddElement(2, "Lynne");
		$foo->AddElement(3, "Sam");
		$foo->AddElementGroup("Walsh");
		$foo->AddElement(4, "Josh");
		$foo->AddElement(5, "Nat");

		$returnValue .= $foo->__toString();

		$returnValue .= "<a href=\"javascript:void(0)\" onclick=\"{$this->_name}_ShowAdd();\">Create New</a><br>";
		$returnValue .= "<a href=\"javascript:void(0)\" onclick=\"{$this->_name}_ShowDisplay();\">Cancel</a>";

		$returnValue .= "</div>";

		return $returnValue;
	}

	protected function RenderAddDIV()
	{
		$id = "id=\"{$this->_partNames['Add']}\"";

		$returnValue = "<div {$id} style=\"display: none;\">";

		$returnValue .= "<h2>Add Area</h2>";
		$returnValue .= "<a href=\"javascript:void(0)\" onclick=\"{$this->_name}_ShowSelect();\">Cancel</a>";

		$returnValue .= "</div>";

		return $returnValue;
	}


}
?>
