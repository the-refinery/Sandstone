<?php
/**
 * Javascript Link Control Class File
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

class JavascriptLinkControl extends StaticBaseControl
{

	protected $_anchorText;

   	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('javascriptlink_body');

	}

	/**
	 * AnchorText property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getAnchorText()
	{
		return $this->_anchorText;
	}

	public function setAnchorText($Value)
	{
		$this->_anchorText = $Value;
		$this->LinkImage->AltText = $Value;
	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{

		$imageRender = $this->LinkImage->__toString();

		if (strlen($imageRender) > 0)
		{
			$returnValue = $imageRender;
		}
		else
		{
			//There's no Image, so use the Anchor Text
	        if (is_set($this->_anchorText))
	        {
	            $returnValue = DIescape($this->_anchorText);
	        }
	        else
	        {
	            $returnValue = " ";
	        }
		}

		return $returnValue;
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

	protected function SetupControls()
	{
		parent::SetupControls();
		
		$this->LinkImage = new ImageControl();
		$this->LinkImage->BodyStyle->AddStyle("javascriptlink_image");
	}

	public function RenderControlBody()
	{

		if (is_set($this->_anchorText))
		{

			$id = "id=\"{$this->Name}\"";
			$href = "href=\"javascript:void(0);\"";

			$returnValue .= "<a {$id} {$href} {$this->_JS->CallList} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style}>{$this->InnerHTML}</a>";

		}
		else
		{
			$returnValue = "";
		}

		return $returnValue;

	}

}
?>
