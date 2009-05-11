<?php

class OutputToBashSpec extends DescribesBehavior
{
	public function ItShouldRenderText()
	{
		return $this->Expects(OutputToBash::Text('foo bar'))->ToBeEqualTo('foo bar');
	}

	public function ItShouldRenderANewLine()
	{
		return $this->Expects(OutputToBash::NewLine())->ToBeEqualTo("\n");
	}

	public function ItShouldRenderABlankLine()
	{
		return $this->Expects(OutputToBash::BlankLine())->ToBeEqualTo("\n\n");
	}

	public function ItShouldOutputColoredText()
	{
		$coloredText = OutputToBash::ColoredText('Red',"This Should Be Red");
		return $this->Expects($coloredText)->ToBeEqualTo("\033[0;31mThis Should Be Red\033[37m");
	}
}

