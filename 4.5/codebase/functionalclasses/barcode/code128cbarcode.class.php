<?php

class Code128Cbarcode extends Code128barcodeBase
{

  public function __construct($InitialPropertyValues)
  {
    $this->_typeName = "Code 128-C";

    $this->_allowedCharactersString = "0123456789";

    $this->_checkCodeSumStart = 105;
    $this->_startCode = Array(2,1,1,2,3,2);

    parent::__construct($InitialPropertyValues);
  }

  protected function CalculateCodeForCharacter($Character)
  {
    $index = intval($Character);

    $returnValue = str_split($this->_characterCodes[$index]);

    return $returnValue;
  }

  public function getCheckCode()
  {
    if (is_set($this->_checkCode) == false)
    {
      $sum = $this->_checkCodeSumStart;

      foreach ($this->_valueCharacters as $characterKey => $tempCharacter)
      {
        $sum += $tempCharacter * ($characterKey + 1);
      }

      $checkCodeIndex = $sum % 103;

      $this->_checkCode = str_split($this->_characterCodes[$checkCodeIndex]);
    }

    return $this->_checkCode;
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

}
