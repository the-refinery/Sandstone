<?php

SandstoneNamespace::Using("Sandstone.Barcode");

class BarcodePage extends BasePage
{
  protected $_isLoginRequired = false;
  protected $_allowedRoleIDs = Array();

  protected $_barcode;
  protected $_value;

  protected $_callbackQueryParms;

  protected function Generic_PreProcessor(&$EventParameters)
  {

    $this->_callbackQueryParms = Array();

    if (is_set($EventParameters['width']))
    {
      $width = $EventParameters['width'];
      $this->_callbackQueryParms[] = "width={$width}";
    }

    if (is_set($EventParameters['height']))
    {
      $height = $EventParameters['height'];
      $this->_callbackQueryParms[] = "height={$height}";
    }

    if (is_set($EventParameters['resolution']))
    {
      $resolution = $EventParameters['resolution'];
      $this->_callbackQueryParms[] = "resolution={$resolution}";
    }

    $className = $this->DetermineBarcodeClass($EventParameters['type']);
    $isFileTypeValid = $this->ValidateFileType($EventParameters['filetype']);

    if (is_set($className) && $isFileTypeValid && strlen($EventParameters['value']) > 0)
    {
      //Setup the barcode object
      $this->_barcode = new $className ($width, $height, $resolution);

      if (is_set($EventParameters['istextdrawn']))
      {
        $this->_barcode->IsTextDrawn = true;
        $this->_callbackQueryParms[] = "istextdrawn=1";

        if (is_set($EventParameters['textsize']))
        {
          $this->_barcode->FontSize = $EventParameters['textsize'];
          $this->_callbackQueryParms[] = "textsize={$EventParameters['textsize']}";
        }
      }

      if (is_set($EventParameters['isborderdrawn']))
      {
        $this->_barcode->IsBorderDrawn = true;
        $this->_callbackQueryParms[] = "isborderdrawn=1";
      }

      if (is_set($EventParameters['isreversecolor']))
      {
        $this->_barcode->IsReverseColor = true;
        $this->_callbackQueryParms[] = "isreversecolor=1";
      }

      $this->_value = $EventParameters['value'];

    }
    else
    {
      $EventParameters['filetype'] = "htm";
      $EventParameters['help'] = true;
    }
  }

  protected function DetermineBarcodeClass($TypeCode)
  {
    switch(strtolower($TypeCode))
    {
    case "c39":
      $returnValue = "Code39barcode";
      break;

    case "c128a":
      $returnValue = "Code128Abarcode";
      break;

    case "c128b":
      $returnValue = "Code128Bbarcode";
      break;

    case "c128c":
      $returnValue = "Code128Cbarcode";
      break;

    case "i2of5":
      $returnValue = "Interleaved2of5barcode";
      break;

    case "qr":
      $returnValue = "QRcodeBarcode";
      break;
    }

    return $returnValue;

  }

  protected function ValidateFileType($FileType)
  {

    switch(strtolower($FileType))
    {
    case "htm":
    case "png":
    case "jpeg":
    case "jpg":
    case "gif":
      $returnValue = true;
      break;

    default:
      $returnValue = false;
    }

    return $returnValue;
  }

  protected function PNG_Processor($EventParameters)
  {
    $imageData = $this->_barcode->GenerateBarcode($this->_value);

    Header("Content-Type: image/png");
    imagepng($imageData);

    $this->_barcode->Destroy();
  }

  protected function JPEG_Processor($EventParameters)
  {
    $imageData = $this->_barcode->GenerateBarcode($this->_value);

    header("Content-type: image/jpeg;");
    imagejpeg($imageData, "", 80);

    $this->_barcode->Destroy();
  }

  protected function JPG_Processor($EventParameters)
  {
    $this->JPEG_Processor($EventParameters);
  }

  protected function GIF_Processor($EventParameters)
  {
    $imageData = $this->_barcode->GenerateBarcode($this->_value);

    header("Content-type: image/gif;");
    imagegif($imageData);

    $this->_barcode->Destroy();
  }

  protected function HTM_Processor($EventParameters)
  {

    if ($EventParameters['help'])
    {
      $this->_template->FileName = "barcode_help";
      $this->_template->DefaultWidth = BarCodeBase::DEFAULT_WIDTH;
      $this->_template->DefaultHeight = BarCodeBase::DEFAULT_HEIGHT;
      $this->_template->DefaultResolution = BarCodeBase::DEFAULT_RESOLUTION;
      $this->_template->DefaultTextSize = BarCodeBase::DEFAULT_FONT_SIZE;
    }
    else
    {
      $this->_template->FileName = "barcode_display";

      $this->_template->BarcodeValue = $this->_value;
      $this->_template->BarcodeDetail = $this->_barcode->DisplayInfo;

      $this->_template->ImageSource = Routing::BuildURLbyRule($EventParameters['matchedrule'], Array('value'=>$this->_value), "jpg");

      $optionParms = implode("&", $this->_callbackQueryParms);

      if (strlen($optionParms) > 0)
      {
        $this->_template->ImageSource .= "?" . $optionParms;
      }

    }

  }

}
