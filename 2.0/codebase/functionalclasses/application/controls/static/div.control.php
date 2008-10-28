<?php
/**
 * DIV Control Class File
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

class DIVcontrol extends StaticBaseControl
{

	protected $_innerHTML;

	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('div_body');

	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getInnerHTML()
	{

		//Any specific InnerHTML is rendered first, then any sub controls
		$returnValue = $this->_innerHTML;

        $returnValue .= $this->RenderControls();

		return $returnValue;
	}

	public function setInnerHTML($Value)
	{
		$this->_innerHTML = $Value;
	}

    /**
	 * HighlightDOMids property
	 *
	 * @return array
	 */
	public function getHighlightDOMids()
	{

		if (count($this->_activeControls) > 0)
		{
			//If this DIV contains active controls,
			//we don't highlight it.
			$returnValue = Array();
		}
		else
		{
			$returnValue[] = $this->Name;
		}


		return $returnValue;
	}

	public function RenderControlBody()
	{

		$id = "id=\"{$this->Name}\"";

		$returnValue = "<div {$id} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>";

		$returnValue .= $this->InnerHTML;

		$returnValue .= "</div>";

		return $returnValue;
	}

}
?>
