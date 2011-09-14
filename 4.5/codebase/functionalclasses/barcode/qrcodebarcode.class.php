<?php

class QRcodeBarcode extends BarCodeBase
{
  public function __construct()
  {
    $this->_typeName = "QR Code";

    $this->_allowedCharactersString = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%";

    parent::__construct();
  }

  protected function SetupCharacterCodesArray()
  {
    $this->_characterCodes = new DIarray();

    $this->_characterCodes[] = "000110100";
    $this->_characterCodes[] = "100100001";
    $this->_characterCodes[] = "001100001";
    $this->_characterCodes[] = "101100000";
    $this->_characterCodes[] = "000110001";
    $this->_characterCodes[] = "100110000";
    $this->_characterCodes[] = "001110000";
    $this->_characterCodes[] = "000100101";
    $this->_characterCodes[] = "100100100";
    $this->_characterCodes[] = "001100100";
    $this->_characterCodes[] = "100001001";
    $this->_characterCodes[] = "001001001";
    $this->_characterCodes[] = "101001000";
    $this->_characterCodes[] = "000011001";
    $this->_characterCodes[] = "100011000";
    $this->_characterCodes[] = "001011000";
    $this->_characterCodes[] = "000001101";
    $this->_characterCodes[] = "100001100";
    $this->_characterCodes[] = "001001100";
    $this->_characterCodes[] = "000011100";
    $this->_characterCodes[] = "100000011";
    $this->_characterCodes[] = "001000011";
    $this->_characterCodes[] = "101000010";
    $this->_characterCodes[] = "000010011";
    $this->_characterCodes[] = "100010010";
    $this->_characterCodes[] = "001010010";
    $this->_characterCodes[] = "000000111";
    $this->_characterCodes[] = "100000110";
    $this->_characterCodes[] = "001000110";
    $this->_characterCodes[] = "000010110";
    $this->_characterCodes[] = "110000001";
    $this->_characterCodes[] = "011000001";
    $this->_characterCodes[] = "111000000";
    $this->_characterCodes[] = "010010001";
    $this->_characterCodes[] = "110010000";
    $this->_characterCodes[] = "011010000";
    $this->_characterCodes[] = "010000101";
    $this->_characterCodes[] = "110000100";
    $this->_characterCodes[] = "011000100";
    $this->_characterCodes[] = "010010100";
    $this->_characterCodes[] = "010101000";
    $this->_characterCodes[] = "010100010";
    $this->_characterCodes[] = "010001010";
    $this->_characterCodes[] = "000101010";

  }

  protected function PrepareValue()
  {
    //Uppercase Only
    $this->_value = "QR" . strtoupper($this->_value);

    parent::PrepareValue();
  }

  protected function ValidateValue()
  {
    $returnValue = parent::ValidateValue();

    //Make sure the * isn't in the value, since it's the start/end character
    foreach($this->_valueCharacters as $tempCharacter)
    {
      if ($tempCharacter == "*")
      {
        $this->_rejectedCharacters["*"] = "*";
        $returnValue = false;
      }
    }

    return $returnValue;
  }

  protected function CalculateBarcodeWidth()
  {
    $singleCharacterWidth = ($this->ComputeBarWidth(0) * 6) + ($this->ComputeBarWidth(1) * 3);
    $singleSpaceWidth = $this->ComputeBarWidth(0);

    $totalCharacterWidth = strlen($this->_value) * $singleCharacterWidth;
    $totalSpaceWidth = (strlen($this->_value) - 1) * $singleSpaceWidth;
    $totalStartStopWidth = $singleCharacterWidth * 2;

    $this->_barcodeWidth = $totalStartStopWidth + $totalCharacterWidth + $totalSpaceWidth;
  }

  protected function DrawSpecifcBarcode()
  {

    $currentPosition = $this->ProcessDrawStartStopCode($this->BarcodeStartPosition);

    //Draw the value
    foreach ($this->_valueCharacters as $tempCharacter)
    {
      $code = $this->CalculateCodeForCharacter($tempCharacter);

      $currentPosition = $this->DrawCode($code, $currentPosition);
    }

    $currentPosition = $this->ProcessDrawStartStopCode($currentPosition);

  }

  protected function ProcessDrawStartStopCode($CurrentPosition)
  {
    //Start & Stop code is " * "
    $code = $this->CalculateCodeForCharacter("*");

    $CurrentPosition = $this->DrawCode($code, $CurrentPosition);

    return $CurrentPosition;
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
    $CurrentPosition += $widths[8];

    //Space between characters
    $CurrentPosition += $this->ComputeBarWidth(0);

    return $CurrentPosition;
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


}
