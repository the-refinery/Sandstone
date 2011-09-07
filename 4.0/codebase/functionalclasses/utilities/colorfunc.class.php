<?php
/*
String Functions Abstract Class File

@package Sandstone
@subpackage Utilities
 */

class ColorFunc
{
  /*
  This returns a random hex Color with the ability to limit the range of light/dark.
   */
  static function GenerateRandomColor($Dark = 0, $Light = 255)
  {
    $color['red']= mt_rand($Dark, $Light);
    $color['green'] = mt_rand($Dark, $Light);
    $color['blue'] = mt_rand($Dark, $Light);

    $returnValue = self::RGBtoHTML($color);

    return $returnValue;
  }

  static function AdjustHTMLcolor($Adjustment, $Color)
  {
    $rgb = self::HTMLtoRGB($Color);

    $newRGB = self::AdjustRGBcolor($Adjustment, $rgb);

    $returnValue = self::RGBtoHTML($newRGB);

    return $returnValue;
  }

  static function AdjustRGBcolor($Adjustment, $Red, $Green = -1 , $Blue =-1)
  {
    if (is_array($Red) && sizeof($Red) == 3)
    {
      $rgb = $Red;
    }
    else
    {
      $rgb['red'] = $Red;
      $rgb['green'] = $Green;
      $rgb['blue'] = $Blue;
    }

    $adjustmentPercent = 1 + ($Adjustment / 100);

    foreach ($rgb as $key=>$value)
    {
      $newValue = round($value * $adjustmentPercent);
 
      if ($newValue <= 255)
      {
        $rgb[$key] = $newValue;
      }
      else
      {
        $rgb[$key] = 255;
      }
    }

    return $rgb;

  }

  static function HTMLtoRGB($Color)
  {
    $returnValue = Array();

    if ($Color[0] == '#')
    {
      $Color = substr($Color, 1);
    }

    if (strlen($Color) == 6)
    {
      $red = $Color[0].$Color[1];
      $green = $Color[2].$Color[3];
      $blue = $Color[4].$Color[5];
    }
    elseif (strlen($Color) == 3)
    {
      $red = $Color[0].$Color[0];
      $green = $Color[1].$Color[1];
      $blue = $Color[2].$Color[2];
    }

    $returnValue['red'] = hexdec($red);
    $returnValue['green'] = hexdec($green);
    $returnValue['blue'] = hexdec($blue);

    return $returnValue;
  }

  static function RGBtoHTML($Red, $Green = -1 , $Blue =-1)
  {

    if (is_array($Red) && sizeof($Red) == 3)
    {
      $rgb = $Red;
    }
    else
    {
      $rgb['red'] = $Red;
      $rgb['green'] = $Green;
      $rgb['blue'] = $Blue;
    }

    foreach ($rgb as $key=>$value)
    {
      if ($value > 255)
      {
        $rgb[$key] = 255;
      }
    }

    $returnValue = sprintf("%02X%02X%02X", $rgb['red'], $rgb['green'], $rgb['blue']);

    return $returnValue;

  }
}
