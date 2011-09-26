<?php

class BarCodeBase extends Module
{

  protected $_typeName;
  protected $_image;

  protected function SetPropertyValueFromArray($Key, $Array)
  {
    if (is_set($Array[strtolower($Key)]))
    {
      $propertyName = "_" . $Key;
      $this->$propertyName = $Array[strtolower($Key)];
    }
  }

  protected function SetBooleanPropertyValueFromArray($Key, $Array)
  {
    if (is_set($Array[strtolower($Key)]))
    {
      $propertyName = "_" . $Key;
      $this->$propertyName = $Array[strtolower($Key)];
    }
  }

  public function Destroy()
  {
    if (is_set($this->_image))
    {
      ImageDestroy($this->_image);
    }
  }

  //-------------------------------
  // These functions must be overridden
  //-------------------------------
  public function GenerateBarcode($Value)
  {
    //This will need to be overridden for each type
  }

}
