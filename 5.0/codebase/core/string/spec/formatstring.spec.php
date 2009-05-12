<?php

class FormatStringSpec extends DescribeBehavior
{
	public function ItShouldConvertCamelCaseToEnglish()
	{
		$testString = "SplitThisStringIntoEnglish";
		$sentanceResult = FormatString::CamelCaseToSentance($testString);
		
		return $this->Expects($sentanceResult)->ToBeEqualTo("Split this string into english");
	}	

	public function ItShouldConvertAStringToSentanceCase()
	{
		$testString = "this is a test sentance";
		$sentanceResult = FormatString::FormatSentanceCase($testString);

		return $this->Expects($sentanceResult)->ToBeEqualTo("This is a test sentance");
	}
}
