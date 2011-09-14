<?php

class Code128Abarcode extends Code128barcodeBase
{

  public function __construct()
  {
    $this->_typeName = "Code 128-A";

    $this->_allowedCharactersString = " !\"#$%&'()*+´-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_";
    $this->_checkCodeSumStart = 103;
    $this->_startCode = Array(2,1,1,4,1,2);

    parent::__construct();

  }

  protected function PrepareValue()
  {
    $this->_value = strtoupper($this->_value);

    parent::PrepareValue();
  }

}
