<?php

class Code128barcodeBase extends SimpleBarCodeBase
{
  protected $_checkCodeSumStart;
  protected $_checkCode;

  protected $_startCode;
  protected $_endCode;

  public function __construct($InitialPropertyValues)
  {
    parent::__construct($InitialPropertyValues);

    $this->_endCode = Array(2,3,3,1,1,1,2);
  }

  public function getCheckCode()
  {
    if (is_set($this->_checkCode) == false)
    {
      $sum = $this->_checkCodeSumStart;

      foreach ($this->_valueCharacters as $characterKey => $tempCharacter)
      {
        $index = array_search($tempCharacter, $this->_validCharacters);

        $sum += $index * ($characterKey + 1);
      }

      $checkCodeIndex = $sum % 103;

      $this->_checkCode = str_split($this->_characterCodes[$checkCodeIndex]);
    }

    return $this->_checkCode;
  }

  protected function SetupCharacterCodesArray()
  {
    $this->_characterCodes = new DIarray();

    $this->_characterCodes[] = "212222";
    $this->_characterCodes[] = "222122";
    $this->_characterCodes[] = "222221";
    $this->_characterCodes[] = "121223";
    $this->_characterCodes[] = "121322";
    $this->_characterCodes[] = "131222";
    $this->_characterCodes[] = "122213";
    $this->_characterCodes[] = "122312";
    $this->_characterCodes[] = "132212";
    $this->_characterCodes[] = "221213";
    $this->_characterCodes[] = "221312";
    $this->_characterCodes[] = "231212";
    $this->_characterCodes[] = "112232";
    $this->_characterCodes[] = "122132";
    $this->_characterCodes[] = "122231";
    $this->_characterCodes[] = "113222";
    $this->_characterCodes[] = "123122";
    $this->_characterCodes[] = "123221";
    $this->_characterCodes[] = "223211";
    $this->_characterCodes[] = "221132";
    $this->_characterCodes[] = "221231";
    $this->_characterCodes[] = "213212";
    $this->_characterCodes[] = "223112";
    $this->_characterCodes[] = "312131";
    $this->_characterCodes[] = "311222";
    $this->_characterCodes[] = "321122";
    $this->_characterCodes[] = "321221";
    $this->_characterCodes[] = "312212";
    $this->_characterCodes[] = "322112";
    $this->_characterCodes[] = "322211";
    $this->_characterCodes[] = "212123";
    $this->_characterCodes[] = "212321";
    $this->_characterCodes[] = "232121";
    $this->_characterCodes[] = "111323";
    $this->_characterCodes[] = "131123";
    $this->_characterCodes[] = "131321";
    $this->_characterCodes[] = "112313";
    $this->_characterCodes[] = "132113";
    $this->_characterCodes[] = "132311";
    $this->_characterCodes[] = "211313";
    $this->_characterCodes[] = "231113";
    $this->_characterCodes[] = "231311";
    $this->_characterCodes[] = "112133";
    $this->_characterCodes[] = "112331";
    $this->_characterCodes[] = "132131";
    $this->_characterCodes[] = "113123";
    $this->_characterCodes[] = "113321";
    $this->_characterCodes[] = "133121";
    $this->_characterCodes[] = "313121";
    $this->_characterCodes[] = "211331";
    $this->_characterCodes[] = "231131";
    $this->_characterCodes[] = "213113";
    $this->_characterCodes[] = "213311";
    $this->_characterCodes[] = "213131";
    $this->_characterCodes[] = "311123";
    $this->_characterCodes[] = "311321";
    $this->_characterCodes[] = "331121";
    $this->_characterCodes[] = "312113";
    $this->_characterCodes[] = "312311";
    $this->_characterCodes[] = "332111";
    $this->_characterCodes[] = "314111";
    $this->_characterCodes[] = "221411";
    $this->_characterCodes[] = "431111";
    $this->_characterCodes[] = "111224";
    $this->_characterCodes[] = "111422";
    $this->_characterCodes[] = "121124";
    $this->_characterCodes[] = "121421";
    $this->_characterCodes[] = "141122";
    $this->_characterCodes[] = "141221";
    $this->_characterCodes[] = "112214";
    $this->_characterCodes[] = "112412";
    $this->_characterCodes[] = "122114";
    $this->_characterCodes[] = "122411";
    $this->_characterCodes[] = "142112";
    $this->_characterCodes[] = "142211";
    $this->_characterCodes[] = "241211";
    $this->_characterCodes[] = "221114";
    $this->_characterCodes[] = "413111";
    $this->_characterCodes[] = "241112";
    $this->_characterCodes[] = "134111";
    $this->_characterCodes[] = "111242";
    $this->_characterCodes[] = "121142";
    $this->_characterCodes[] = "121241";
    $this->_characterCodes[] = "114212";
    $this->_characterCodes[] = "124112";
    $this->_characterCodes[] = "124211";
    $this->_characterCodes[] = "411212";
    $this->_characterCodes[] = "421112";
    $this->_characterCodes[] = "421211";
    $this->_characterCodes[] = "212141";
    $this->_characterCodes[] = "214121";
    $this->_characterCodes[] = "412121";
    $this->_characterCodes[] = "111143";
    $this->_characterCodes[] = "111341";
    $this->_characterCodes[] = "131141";
    $this->_characterCodes[] = "114113";
    $this->_characterCodes[] = "114311";
    $this->_characterCodes[] = "411113";
    $this->_characterCodes[] = "411311";
    $this->_characterCodes[] = "113141";
    $this->_characterCodes[] = "114131";
    $this->_characterCodes[] = "311141";
    $this->_characterCodes[] = "411131";

  }

  protected function ComputeBarWidth($Code)
  {
    if ($Code >= 1 && $Code <= 4)
    {
      $returnValue = $this->_resolution * $Code;
    }

    return $returnValue;
  }

  protected function CalculateBarcodeWidth()
  {
    //Width of the value itself, since every code sums to 11, we can easily compute this
    $valueWidth = (count($this->_valueCharacters) * 11) * $this->_resolution;

    //Width of the check character
    $checkCodeWidth = 11 * $this->_resolution;

    //Width of the start code (211412)
    $startCodeWidth = 11 * $this->_resolution;

    //Width of the end code (2331112)
    $endCodeWidth = 13 * $this->_resolution;

    $this->_barcodeWidth = $startCodeWidth + $valueWidth + $checkCodeWidth + $endCodeWidth;

  }

  protected function DrawSpecifcBarcode()
  {
    //Draw the Start Code
    $currentPosition = $this->DrawCode($this->_startCode, $this->BarcodeStartPosition);

    //Draw the value
    foreach ($this->_valueCharacters as $tempCharacter)
    {
      $code = $this->CalculateCodeForCharacter($tempCharacter);

      $currentPosition = $this->DrawCode($code, $currentPosition);
    }

    //Draw the check code
    $currentPosition = $this->DrawCode($this->CheckCode, $currentPosition);

    //Draw the end code
    $currentPosition = $this->DrawCode($this->_endCode, $currentPosition);

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

    //This is really just for the close code.
    if (count($Code) == 7)
    {
      $this->DrawSingleBar($CurrentPosition, $y, $widths[6] , $this->BarcodeHeight);
    }

    return $CurrentPosition;

  }


}
