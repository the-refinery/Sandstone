<?php

class Interleaved2of5barcode extends BarCodeBase
{
  public function __construct()
  {

    $this->_typeName = "Interleaved 2 of 5";

    $this->_allowedCharactersString = "0123456789";

    parent::__construct();
  }

  protected function SetupCharacterCodesArray()
  {
    $this->_characterCodes = new DIarray();

    $this->_characterCodes[] = "00110";
    $this->_characterCodes[] = "10001";
    $this->_characterCodes[] = "01001";
    $this->_characterCodes[] = "11000";
    $this->_characterCodes[] = "00101";
    $this->_characterCodes[] = "10100";
    $this->_characterCodes[] = "01100";
    $this->_characterCodes[] = "00011";
    $this->_characterCodes[] = "10010";
    $this->_characterCodes[] = "01010";

  }

  protected function CalculateBarcodeWidth()
  {
    $singleCharacterWidth = ($this->ComputeBarWidth(0) * 3) + ($this->ComputeBarWidth(1) * 2);
    $totalCharacterWidth = strlen($this->_value) * $singleCharacterWidth;

    $startCodeWidth = $this->ComputeBarWidth(0) * 4;  //Start Code: 0000
    $stopCodeWidth = $this->ComputeBarWidth(1) + ($this->ComputeBarWidth(0) * 2); //Stop Code: 100

    $this->_barcodeWidth = $startCodeWidth + $totalCharacterWidth + $stopCodeWidth;
  }

  protected function ComputeBarWidth($Code)
  {
    if ($Code == 0)
    {
      //Narrow
      $returnValue = $this->_resolution;
    }
    else
    {
      //Wide
      $returnValue = $this->_resolution * 2;
    }

    return $returnValue;
  }

  protected function PrepareValue()
  {
    if ((strlen($this->_value) % 2) != 0)
    {
      //Value length must be even.  We'll pad with a leading zero
      $this->_value = "0" . $this->_value;
    }

    $this->_valueCharacters = str_split($this->_value, 2);
  }

  protected function ValidateValue()
  {
    $returnValue = true;
    $this->_rejectedCharacters = Array();

    //We re-split this since our main _valueCharacters array is split into groups of 2
    $valueChars = str_split($this->_value);

    foreach($valueChars as $tempCharacter)
    {
      if (is_numeric($tempCharacter) == false)
      {
        $this->_rejectedCharacters[$tempCharacter] = $tempCharacter;
        $returnValue = false;
      }
    }

    return $returnValue;
  }

  protected function DrawSpecifcBarcode()
  {

    $currentPosition = $this->ProcessDrawStartCode();

    //Draw the value
    foreach ($this->_valueCharacters as $tempCharacter)
    {
      $code = $this->CalculateCodeForCharacter($tempCharacter);

      $currentPosition = $this->DrawCode($code, $currentPosition);
    }

    $currentPosition = $this->ProcessDrawStopCode($currentPosition);

  }

  protected function ProcessDrawStartCode()
  {
    //Start Code is 0000
    $y = self::DEFAULT_MARGIN_Y1;

    $this->DrawSingleBar($this->BarcodeStartPosition, $y, $this->ComputeBarWidth(0), $this->BarcodeHeight);
    $currentPosition = $this->BarcodeStartPosition + $this->ComputeBarWidth(0) * 2;

    $this->DrawSingleBar($currentPosition, $y, $this->ComputeBarWidth(0), $this->BarcodeHeight);
    $currentPosition += $this->ComputeBarWidth(0) * 2;

    return $currentPosition;
  }

  protected function ProcessDrawStopCode($CurrentPosition)
  {
    //Start Code is 100
    $y = self::DEFAULT_MARGIN_Y1;

    $this->DrawSingleBar($CurrentPosition, $y, $this->ComputeBarWidth(1), $this->BarcodeHeight);
    $CurrentPosition += $this->ComputeBarWidth(1) + $this->ComputeBarWidth(0);

    $this->DrawSingleBar($CurrentPosition, $y, $this->ComputeBarWidth(0), $this->BarcodeHeight);
    $CurrentPosition += $this->ComputeBarWidth(0);

    return $CurrentPosition;
  }

  protected function CalculateCodeForCharacter($Character)
  {
    //Do the actual interleave
    $barIndex = substr($Character, 0, 1);
    $spaceIndex = substr($Character, 1, 1);

    $barCharacterCode = $this->_characterCodes[$barIndex];
    $spaceCharacterCode = $this->_characterCodes[$spaceIndex];

    for ($i = 0; $i < 5; $i++)
    {
      $returnValue[] = $barCharacterCode[$i];
      $returnValue[] = $spaceCharacterCode[$i];
    }

    return $returnValue;
  }

  protected function DrawCode($Code, $CurrentPosition)
  {

    $y = self::DEFAULT_MARGIN_Y1;

    foreach ($Code as $position=>$tempCode)
    {
      $widths[$position] = $this->ComputeBarWidth($tempCode);
    }

    $this->DrawSingleBar($CurrentPosition, $y, $widths[0] , $this->BarcodeHeight);
    $CurrentPosition += $widths[0] + $widths[1];

    $this->DrawSingleBar($CurrentPosition, $y, $widths[2] , $this->BarcodeHeight);
    $CurrentPosition += $widths[2] + $widths[3];

    $this->DrawSingleBar($CurrentPosition, $y, $widths[4] , $this->BarcodeHeight);
    $CurrentPosition += $widths[4] + $widths[5];

    $this->DrawSingleBar($CurrentPosition, $y, $widths[6] , $this->BarcodeHeight);
    $CurrentPosition += $widths[6] + $widths[7];

    $this->DrawSingleBar($CurrentPosition, $y, $widths[8] , $this->BarcodeHeight);
    $CurrentPosition += $widths[8] + $widths[9];

    return $CurrentPosition;
  }


}
