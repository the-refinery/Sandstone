<?php
/**
 * Label Control Class File
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

class LabelControl extends StaticBaseControl
{

	protected $_text;
	protected $_targetControlName;

	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('label_body');

	}

	/**
	 * Text property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getText()
	{
		return $this->_text;
	}

	public function setText($Value)
	{
		$this->_text = $Value;
	}

	/**
	 * TargetControlName property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getTargetControlName()
	{
		return $this->_targetControlName;
	}

	public function setTargetControlName($Value)
	{
		$this->_targetControlName = $Value;
	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{
		return DIescape($this->_text);
	}

    /**
	 * HighlightDOMids property
	 *
	 * @return array
	 */
	public function getHighlightDOMids()
	{
		//We don't highlight this
		$returnValue = Array();

		return $returnValue;
	}

	public function RenderControlBody()
	{

		if (is_set($this->_text))
		{
			if (is_set($this->_targetControlName))
			{
				$for = "for=\"{$this->_targetControlName}\"";
			}

			$id = "id=\"{$this->Name}\"";

			$returnValue = "<label {$id} {$for} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>{$this->InnerHTML}:</label>";
		}
		else
		{
			$returnValue = "";
		}

		return $returnValue;

	}

}
?>
