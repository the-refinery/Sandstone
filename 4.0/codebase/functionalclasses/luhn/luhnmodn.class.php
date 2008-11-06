<?php
/**
Luhn Mod N Class File

@package Sandstone
@subpackage Luhn
*/

class LuhnModN extends Module
{

    protected $_validTotalStringLength;

    /*
    ValidStringLength property

    @return integer
    @param integer $Value
     */
    public function getValidTotalStringLength()
    {
        return $this->_validTotalStringLength;
    }

    public function setValidTotalStringLength($Value)
    {
        $this->_validTotalStringLength = $Value;
    }

    public function GenerateFullString($InputString)
    {
        if (strlen($InputString) == $this->_validTotalStringLength - 1)
        {

            $checkCharacter = $this->GenerateCheckCharacter($InputString);

            $returnValue = $InputString . $checkCharacter;

        }

        return $returnValue;
    }

    protected function GenerateCheckCharacter($InputString)
    {
        $factor = 2;
        $sum = 0;
        $n = $this->_validTotalStringLength - 1;

        // Starting from the right and working leftwards is easier since
        // the initial "factor" will always be "2"
        for ($i = strlen($InputString) - 1; $i >=0; $i--)
        {
            $char = substr($InputString, $i, 1);
            $codePoint = $this->CodePointFromCharacter($char);

            $addend = $factor * $codePoint;

            // Alternate the "factor" that each "codePoint" is multiplied by
            if ($factor == 2)
            {
                $factor = 1;
            }
            else
            {
                $factor = 2;
            }

            // Sum the digits of the "addend" as expressed in base "n"
            $addend = floor($addend / $n) + ($addend % $n);

            $sum += $addend;
        }

        // Calculate the number that must be added to the "sum"
        // to make it divisible by "n"
        $remainder = $sum % $n;
        $checkCodePoint = $n - $remainder;
        $checkCodePoint %= $n;

        $returnValue = $this->CharacterFromCodePoint($checkCodePoint);

        return $returnValue;


    }

    public function ValidateCheckCharacter($FullString)
    {
        $factor = 1;
        $sum = 0;
        $n = $this->_validTotalStringLength - 1;

         // Starting from the right, work leftwards
        // Now, the initial "factor" will always be "1"
        // since the last character is the check character
        for ($i = strlen($FullString) - 1; $i >= 0; $i--)
        {
            $char = substr($FullString, $i, 1);
            $codePoint = $this->CodePointFromCharacter($char);

            $addend = $factor * $codePoint;

            // Alternate the "factor" that each "codePoint" is multiplied by
            if ($factor == 2)
            {
                $factor = 1;
            }
            else
            {
                $factor = 2;
            }

            // Sum the digits of the "addend" as expressed in base "n"
            $addend = floor($addend / $n) + ($addend % $n);
            $sum += $addend;
        }

        $remainder = $sum % $n;

        if ($remainder == 0)
        {
            $returnValue = true;
        }
        else
        {
            $returnValue = false;
        }

        return $returnValue;

    }

    protected function CodePointFromCharacter($Character)
    {

//        if (is_numeric($Character))
//        {
//            $returnValue = $Character;
//        }
//        else
//        {
//            $Character = strtoupper($Character);

//            $returnValue = ord($Character) - 55;
//        }


        $returnValue = ord($Character) - 65;


        return $returnValue;
    }

    protected function CharacterFromCodePoint($CodePoint)
    {

//        if ($CodePoint < 10)
//        {
//            $returnValue = $CodePoint;
//        }
//        else
//        {
//            $returnValue = chr($CodePoint + 55);
//        }

        $returnValue = chr($CodePoint + 65);

        return $returnValue;
    }

}
?>