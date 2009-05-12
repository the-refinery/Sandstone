<?php

class FormatStringSpec extends DescribesBehavior
{
	public function ItShouldConvertCamelCaseToEnglish()
	{
		$testString = "SplitThisStringIntoEnglish";
		$sentanceResult = FormatsString::CamelCaseToSentance($testString);
		
		return $this->Expects($sentanceResult)->ToBeEqualTo("Split this string into english");
	}	

	public function ItShouldConvertAStringToSentanceCase()
	{
		$testString = "this is a test sentance";
		$sentanceResult = FormatsString::FormatSentanceCase($testString);

		return $this->Expects($sentanceResult)->ToBeEqualTo("This is a test sentance");
	}
}
