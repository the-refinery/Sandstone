<?php

class Code128Bbarcode extends Code128barcodeBase
{

  public function __construct($InitialPropertyValues)
  {
    $this->_typeName = "Code 128-B";

    $this->_allowedCharactersString = " !\"#$%&'()*+´-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{ }~";
    $this->_checkCodeSumStart = 104;
    $this->_startCode = Array(2,1,1,2,1,4);

    parent::__construct($InitialPropertyValues);

  }

}
