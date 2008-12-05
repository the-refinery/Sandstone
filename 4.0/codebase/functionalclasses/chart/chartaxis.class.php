<?php

class ChartAxis extends Module
{

	const LEFT_LABEL_ALIGN = -1;
	const CENTER_LABEL_ALIGN = 0;
	const RIGHT_LABEL_ALIGN = 1;

	protected $_labels;
	protected $_color;
	protected $_fontSize;
	protected $_alignment;

	public function __construct($Labels = null, $Color = null, $FontSize = null, $Alignment=ChartAxis::CENTER_LABEL_ALIGN)
	{

		if (is_set($Labels))
		{
			$this->AddLabel($Labels);
		}

		$this->_color = $Color;
		$this->_fontSize = $FontSize;
		$this->_alignment = $Alignment;
	}

	/*
	Labels property

	@return array
	 */
	public function getLabels()
	{
		return $this->_labels;
	}

	/*
	Color property

	@return string
	@param string $Value
	 */
	public function getColor()
	{
		return $this->_color;
	}

	public function setColor($Value)
	{
		$this->_color = $Value;
	}

	/*
	FontSize property

	@return integer
	@param integer $Value
	 */
	public function getFontSize()
	{
		return $this->_fontSize;
	}

	public function setFontSize($Value)
	{
		$this->_fontSize = $Value;
	}

	/*
	Alignment property

	@return integer
	@param integer $Value
	 */
	public function getAlignment()
	{
		return $this->_alignment;
	}

	public function setAlignment($Value)
	{
		$this->_alignment = $Value;
	}

	public function getFormattedLabels()
	{
		$labels = Array();

		foreach ($this->_labels as $tempLabel)
		{
			$labels[] = urlencode($tempLabel);
		}

		$returnValue = implode("|", $labels);

		return $returnValue;
	}

	public function getFormattedStyle()
	{
		if (is_set($this->Color))
		{
			$styleData = $this->Color;

			if (is_set($this->FontSize))
			{
				$styleData .= ",{$this->FontSize}";

				if (is_set($this->Alignment))
				{
					$styleData .= ",{$this->Alignment}";
				}
			}

			$returnValue = $styleData;
		}
		else
		{
			$returnValue = null;
		}

		return $returnValue;

	}

	public function AddLabel($Label)
	{
		if (is_array($Label))
		{
			$this->_labels = $Label;
		}
		elseif($Label instanceof DIarray)
		{
			foreach($Label as $tempLabel)
			{
				$this->_labels[] = $tempLabel;
			}
		}
		else
		{
			$this->_labels[] = $Label;
		}
	}

}
?>