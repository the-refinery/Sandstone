<?php

class QRcodeBarcode extends BarCodeBase
{
  public function __construct($InitialPropertyValues)
  {
    $this->_typeName = "QR Code";
  }

  public function GenerateBarcode($Value)
  {
    di_echo("Barcode For: $Value");
  }

}
