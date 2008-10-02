<?php
/*
Code 128A Barcode Class File

@package Sandstone
@subpackage Barcode
 */


class Code128Abarcode extends Code128barcodeBase
{

    public function __construct($Width = null, $Height = null, $Resolution = null)
    {
        $this->_typeName = "Code 128-A";

        $this->_allowedCharactersString = " !\"#$%&'()*+´-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_";
        $this->_checkCodeSumStart = 103;
        $this->_startCode = Array(2,1,1,4,1,2);

        parent::__construct($Width, $Height, $Resolution);

    }

    protected function PrepareValue()
    {
        $this->_value = strtoupper($this->_value);

        parent::PrepareValue();
    }

}
?>