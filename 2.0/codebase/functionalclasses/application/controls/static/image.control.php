<?php
/**
 * Image Control Class File
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

class ImageControl extends StaticBaseControl
{

	protected $_url;
	protected $_altText;
	protected $_height;
	protected $_width;

	public function __construct()
	{

		parent::__construct();

		//Setup the default style classes
		$this->_bodyStyle->AddClass('image_body');

	}

	/**
	 * URL property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getURL()
	{
		return $this->_url;
	}

	public function setURL($Value)
	{
		$this->_url = $Value;
	}

	/**
	 * AltText property
	 *
	 * @return string
	 *
	 * @param string $Value
	 */
	public function getAltText()
	{
		return $this->_altText;
	}

	public function setAltText($Value)
	{
		$this->_altText = $Value;
	}

	/**
	 * Height property
	 *
	 * @return integer
	 *
	 * @param integer $Value
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	public function setHeight($Value)
	{
		if (is_numeric($Value))
		{
			$this->_height = $Value;
		}
		else
		{
			$this->_height = null;
		}

	}

	/**
	 * Width property
	 *
	 * @return integer
	 *
	 * @param integer $Value
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function setWidth($Value)
	{
		if (is_numeric($Value))
		{
			$this->_width = $Value;
		}
		else
		{
			$this->_width = null;
		}

	}

	/**
	 * InnerHTML property
	 *
	 * @return string
	 */
	public function getInnerHTML()
	{
		return "";
	}

	public function RenderControlBody()
	{
		if (is_set($this->_url))
		{

			$id = "id=\"{$this->Name}\"";
			$src = "src=\"{$this->_url}\"";

			if (is_set($this->_altText))
			{
				$alt = "alt=\"{$this->_altText}\"";
			}

			if (is_set($this->_height))
			{
				$height = "height=\"{$this->_height}\"";
			}

			if (is_set($this->_width))
			{
				$width = "width=\"{$this->_width}\"";
			}

			$returnValue = "<img {$id} {$src} {$alt} {$height} {$width} {$this->_bodyStyle->Classes} {$this->_bodyStyle->Style} />";
		}
		else
		{
			$returnValue = "";
		}

		return $returnValue;

	}

}
?>
