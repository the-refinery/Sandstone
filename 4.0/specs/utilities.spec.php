<?php

Namespace::Using("Sandstone.Utilities.String");

class UtilitiesSpec extends SpecBase
{
	public function ItShouldStripNonNumericCharactersFromString()
	{
		$testCase = "??*6lka3asd0ef9.23lkasdg";
		$testCase = StringFunc::MakeDecimal($testCase);
		
		Check($testCase)->ShouldBeEqualTo(6309.23);		
	}
	
	public function ItShouldTurnCamelCaseIntoASentance()
	{
		// Setup Test Here
		$testCase = "thisIsATestCase";
		$testCase = StringFunc::CamelCaseToSentance($testCase);
		
		Check($testCase)->ShouldBeEqualTo('This is a test case');		
	}
	
	public function ItShouldRemoveAllPunctuation()
	{
		$testCase = "ab)*c(d)e^f%g1)(2)3";
		$testCase = StringFunc::RemovePunctuation($testCase);
		
		Check($testCase)->ShouldNotBeEqualTo('abcdefg123');		
	}

	public function ItShouldFormatAsAValidFilename()
	{
		$testCase = "**Atest -filename.txt";
		$testCase = StringFunc::FormatFilename($testCase);
		
		Check($testCase)->ShouldBeEqualTo('Atest-filename.txt');		
	}
	
	public function ItShouldFormatCurrencyCorrectly()
	{
		$testCase = 35.40;
		$testCase = StringFunc::FormatCurrency($testCase);
		
		Check($testCase)->ShouldBeEqualTo("\$35.40");
	}
	
	public function ItShouldFormatNullCurrencyAsNull()
	{
		$testCase = StringFunc::FormatCurrency(null);
		
		Check($testCase)->ShouldBeNull();
	}
	
	public function ItShouldFormatDecimalsWithPrecision()
	{
		$testCase = "35.20500";
		$testCase = StringFunc::FormatPrecision($testCase);
		
		Check($testCase)->ShouldBeEqualTo(35.205);
	}

	public function ItShouldFormatIntegerWithPrecision()
	{
		$testCase = "25";
		$testCase = StringFunc::FormatPrecision($testCase);
		
		Check($testCase)->ShouldBeEqualTo(25.0);
	}
	
	public function ItShouldFormatNullAsZeroWithPrecision()
	{
		$testCase = StringFunc::FormatPrecision(null);
		
		Check($testCase)->ShouldBeEqualTo(0.0);
	}
	
	public function ItShouldFormatAStringAsASentanceCase()
	{
		$testCase = "Testing A Sentance Case Conversion.";
		$testCase = StringFunc::FormatSentanceCase($testCase);
		
		Check($testCase)->ShouldBeEqualTo("Testing a sentance case conversion.");
	}
	
	public function ItShouldFormatA9DigitStringAsASocialSecurityNumber()
	{
		$testCase = "111223333";
		$testCase = StringFunc::Reformat($testCase,'###-##-####');

		Check($testCase)->ShouldBeEqualTo('111-22-3333');		
	}

	public function ItShouldFormatA10DigitStringAsAPhoneNumber()
	{
		$testCase = "4407994202";
		$testCase = StringFunc::Reformat($testCase,'(###) ###-####');

		Check($testCase)->ShouldBeEqualTo('(440) 799-4202');		
	}
	
}

?>