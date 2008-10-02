<?php
/*
BarcodeBase Class File

@package Sandstone
@subpackage Barcode
 */


class BarCodeBase extends Module
{

	const DEFAULT_WIDTH = 250;
	const DEFAULT_HEIGHT = 100;
	const DEFAULT_RESOLUTION = 2;
	const DEFAULT_FONT_SIZE = 5;

	const DEFAULT_MARGIN_Y1 = 10;
	const DEFAULT_MARGIN_Y2 = 10;
	const DEFAULT_TEXT_OFFSET = 2;

    protected $_typeName;

	protected $_width;
	protected $_height;
	protected $_resolution;

	protected $_isBorderDrawn;
	protected $_isTransparent;
	protected $_isReverseColor;

	protected $_isTextDrawn;
	protected $_fontSize;

	//Image Drawing Tools
	protected $_image;
	protected $_backgroundColor;
	protected $_brush;

    protected $_barcodeWidth;
    protected $_barcodeHeight;

    protected $_value;
    protected $_valueCharacters;

    protected $_allowedCharactersString;
    protected $_validCharacters;
    protected $_rejectedCharacters;

    protected $_characterCodes;

	public function __construct($Width = null, $Height = null, $Resolution = null)
	{

		if (is_set($Width))
		{
			$this->_width = $Width;
		}
		else
		{
			$this->_width = self::DEFAULT_WIDTH;
		}


		if (is_set($Height))
		{
			$this->_height = $Height;
		}
		else
		{
			$this->_height = self::DEFAULT_HEIGHT;
		}

		if (is_set($Resolution))
		{
			$this->_resolution = $Resolution;
		}
		else
		{
			$this->_resolution = self::DEFAULT_RESOLUTION;
		}

		$this->_isBorderDrawn = false;
		$this->_isTransparent = false;
		$this->_isReverseColor = false;

		$this->_isTextDrawn = false;
		$this->_fontSize = self::DEFAULT_FONT_SIZE;

        //Setup our valid characters array
        $this->_validCharacters = str_split($this->_allowedCharactersString);

        $this->SetupCharacterCodesArray();

	}

	public function Destroy()
	{
		if (is_set($this->_image))
		{
			ImageDestroy($this->_image);
		}
	}

	/*
	IsBorderDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsBorderDrawn()
	{
		return $this->_isBorderDrawn;
	}

	public function setIsBorderDrawn($Value)
	{
		$this->_isBorderDrawn = $Value;
	}

	/*
	IsTransparent property

	@return boolean
	@param boolean $Value
	 */
	public function getIsTransparent()
	{
		return $this->_isTransparent;
	}

	public function setIsTransparent($Value)
	{
		$this->_isTransparent = $Value;

        //Not compatible with Reverse Color
        if ($Value == true)
        {
            $this->_isReverseColor = false;
        }
	}

	/*
	IsReverseColor property

	@return boolean
	@param boolean $Value
	 */
	public function getIsReverseColor()
	{
		return $this->_isReverseColor;
	}

	public function setIsReverseColor($Value)
	{
		$this->_isReverseColor = $Value;

        //Not compatible with Transparent
        if ($Value == true)
        {
            $this->_isTransparent = false;
        }

	}

	/*
	IsTextDrawn property

	@return boolean
	@param boolean $Value
	 */
	public function getIsTextDrawn()
	{
		return $this->_isTextDrawn;
	}

	public function setIsTextDrawn($Value)
	{
		$this->_isTextDrawn = $Value;
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
		if ($Value < 1)
		{
			$this->_fontSize = 1;
		}
		else if ($Value > 5)
		{
			$this->_fontSize = 5;
		}
		else
		{
			$this->_fontSize = $Value;
		}
	}

	/*
	Width property

	@return integer
	@param integer $Value
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	public function setWidth($Value)
	{
		$this->_width = $Value;
	}

	/*
	Height property

	@return integer
	@param integer $Value
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	public function setHeight($Value)
	{
		$this->_height = $Value;
	}

	/*
	Resolution property

	@return integer
	@param integer $Value
	 */
	public function getResolution()
	{
		return $this->_resolution;
	}

	public function setResolution($Value)
	{
		$this->_resolution = $Value;
	}

	public function getFontHeight()
	{
		return ImageFontHeight($this->_fontSize);
	}

	public function getFontWidth()
	{
		return ImageFontWidth($this->_fontSize);
	}

    public function getBarcodeStartPosition()
    {
        return round(($this->_width - $this->_barcodeWidth) / 2);
    }

    public function getBarcodeHeight()
    {
        if (is_set($this->_barcodeHeight) == false)
        {
            $this->_barcodeHeight = $this->_height - (self::DEFAULT_MARGIN_Y1 + self::DEFAULT_MARGIN_Y2);

            if ($this->_isTextDrawn)
            {
                $this->_barcodeHeight -= $this->FontHeight;
            }
        }

        return $this->_barcodeHeight;
    }

    public function getDisplayInfo()
    {
        $returnValue =  "<h2>{$this->_typeName}</h2>";
        $returnValue .=  "<ul>";
        $returnValue .=  "<li><b>Width</b> {$this->_width}</li>";
        $returnValue .=  "<li><b>Height</b> {$this->_height}</li>";
        $returnValue .=  "<li><b>Resolution</b> {$this->_resolution}</li>";

        if ($this->_isTextDrawn)
        {
            $returnValue .=  "<li><b>Draw Text?</b> Yes</li>";
            $returnValue .=  "<li><b>Text Size</b> {$this->_fontSize}</li>";
        }
        else
        {
            $returnValue .=  "<li><b>Draw Text?</b> No</li>";
        }

        if ($this->_isBorderDrawn)
        {
            $returnValue .=  "<li><b>Draw Border?</b> Yes</li>";
        }
        else
        {
            $returnValue .=  "<li><b>Draw Border?</b> No</li>";
        }

        if ($this->_isReverseColor)
        {
            $returnValue .=  "<li><b>Reverse Color?</b> Yes</li>";
        }
        else
        {
            $returnValue .=  "<li><b>Reverse Color?</b> No</li>";
        }

        $returnValue .=  "</ul>";

        return $returnValue;
    }

	final public function GenerateBarcode($Value)
	{
        $this->_value = $Value;

		//Setup the image
		$this->_image = ImageCreate($this->_width, $this->_height);

		if ($this->_isReverseColor)
		{
			//White on Black
			$this->_backgroundColor = ImageColorAllocate($this->_image, 0, 0, 0);
			$this->_brush = ImageColorAllocate($this->_image, 255, 255, 255);
		}
		else
		{
			//Black on White
			$this->_backgroundColor = ImageColorAllocate($this->_image, 255, 255, 255);
			$this->_brush = ImageColorAllocate($this->_image, 0, 0, 0);
		}

		//Fill the background, if we aren't transparent
		if ($this->_isTransparent == false)
		{
			ImageFill($this->_image, $this->_width, $this->_height, $this->_backgroundColor);
		}

        if (strlen($this->_value) > 0)
        {
            $this->PrepareValue();

            $isValid = $this->ValidateValue();

            if ($isValid)
            {
                $this->CalculateBarcodeWidth();

                $this->DrawSpecifcBarcode();

                $this->ProcessTextOutput();
            }
            else
            {
                $this->ProcessInvalidValueOutput();
            }
        }
        else
        {
            $this->ProcessNullValueOutput();
        }


		//Draw the border, if requested
		if ($this->_isBorderDrawn)
		{
			ImageRectangle($this->_image, 0, 0, $this->_width - 1, $this->_height - 1, $this->_brush);
		}

		return $this->_image;

	}

    protected function PrepareValue()
    {
        $this->_valueCharacters = str_split($this->_value);
    }

    protected function ValidateValue()
    {
        $returnValue = true;
        $this->_rejectedCharacters = Array();

        foreach($this->_valueCharacters as $tempCharacter)
        {
            if (array_search($tempCharacter, $this->_validCharacters) === false)
            {
                $this->_rejectedCharacters[$tempCharacter] = $tempCharacter;
                $returnValue = false;
            }
        }

        return $returnValue;
    }

    protected function ProcessInvalidValueOutput()
    {

        $this->_fontSize = 2;

        $outputString = "ERROR!  Invalid Characters in value:";
        $textWidth = $this->FontWidth * strlen($outputString);
        $x = round(($this->_width - $textWidth) / 2);
        $y = self::DEFAULT_MARGIN_Y1;
        $this->DrawText($x, $y, $outputString);

        $outputString = "['" . implode("', '", $this->_rejectedCharacters) . "']";
        $textWidth = $this->FontWidth * strlen($outputString);
        $x = round(($this->_width - $textWidth) / 2);
        $y = $this->FontHeight + (self::DEFAULT_MARGIN_Y1 * 2);
        $this->DrawText($x, $y, $outputString);

        $outputString = "For {$this->_typeName} barcode.";
        $textWidth = $this->FontWidth * strlen($outputString);
        $x = round(($this->_width - $textWidth) / 2);
        $y = ($this->FontHeight * 2) + (self::DEFAULT_MARGIN_Y1 * 3);
        $this->DrawText($x, $y, $outputString);

    }

    protected function ProcessNullValueOutput()
    {
        $outputString = "ERROR!  Null value.";
        $textWidth = $this->FontWidth * strlen($outputString);
        $x = round(($this->_width - $textWidth) / 2);
        $y = self::DEFAULT_MARGIN_Y1;
        $this->DrawText($x, $y, $outputString);
    }

    protected function ProcessTextOutput()
    {
        if ($this->_isTextDrawn)
        {
            $textWidth = $this->FontWidth * strlen($this->_value);

            $x = round(($this->_width - $textWidth) / 2);
            $y = $this->BarcodeHeight + self::DEFAULT_MARGIN_Y1 + self::DEFAULT_TEXT_OFFSET;

            $this->DrawText($x, $y, $this->_value);
        }

    }

	protected function DrawText($X, $Y, $Text)
	{
		ImageString($this->_image, $this->_fontSize, $X, $Y, $Text, $this->_brush);
	}

	protected function DrawSingleBar($X, $Y, $Width, $Height)
	{
		//Sanity Check #1 - make sure the X/Y are inside our bounds
		if ($X >= 0 && $X <= $this->_width && $Y >= 0 && $Y <= $this->_height)
		{
			//Sanity Check #2 - make sure we aren't going to draw outside the bounds
			if ($X + $Width <= $this->_width && $Y + $Height <= $this->_height)
			{
				//Everything is ok - we can draw this.
				for ($i=0; $i < $Width; $i++)
				{
					$startX = $X + $i;
					$startY = $Y;

					$endX = $X + $i;
					$endY = $Y + $Height;

					imageline($this->_image, $startX, $startY, $endX, $endY, $this->_brush);
				}
			}
		}
	}

    protected function CalculateCodeForCharacter($Character)
    {
        $index = array_search($Character, $this->_validCharacters);

        $returnValue = str_split($this->_characterCodes[$index]);

        return $returnValue;
    }

    public function Display()
    {
        echo $this->DisplayInfo;
    }

    //-------------------------------
    // These functions must be overridden
    //-------------------------------
    protected function DrawSpecifcBarcode()
    {
        //This will need to be overridden for each type
    }

    protected function SetupCharacterCodesArray()
    {
        //This will need to be overridden for each type
    }

    protected function CalculateBarcodeWidth()
    {
        //This will need to be overridden for each type
    }


}
?>