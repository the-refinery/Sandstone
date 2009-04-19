<?php

Namespace::Using('Sandstone.Curl');
Namespace::Using('Sandstone.ScreenScrape');

class ScreenScrapeSpec extends SpecBase
{
	protected $_screenScraper;

	public function BeforeAll()
	{
		$curl = new DICurl();
		$html = $curl->FetchURL('http://www.google.com');

		$this->_screenScraper = new ScreenScraper($html);
	}

	public function ItShouldReadAWebPage()
	{
		Check($this->_screenScraper->HTML)->ShouldNotBeNull();
	}	

	public function ItShouldFindADomElementByXpath()
	{
		Check(count($this->_screenScraper->Find('/html/body/center/img')))->ShouldBeGreaterThanOrEqualTo(1);
	}
}

?>
