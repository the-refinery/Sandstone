<?php

class OutputToBash
{
	static public function NewLine()
	{
		return "\n";
	}

	static public function BlankLine()
	{
		return "\n\n";
	}

	static public function Text($Text)
	{
		return $Text;
	}

	static public function ColoredText($Color, $Text)
	{
		$defaultColor = "\033[37m";
		$colorLegend = array('red'    => "\033[0;31m",
												 'yellow' => "\033[0;33m");

		$colorEscape = $colorLegend[strtolower($Color)];	

		return $colorEscape . $Text . $defaultColor;
	}
}
