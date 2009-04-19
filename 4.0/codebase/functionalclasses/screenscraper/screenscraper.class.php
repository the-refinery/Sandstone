<?php

class ScreenScraper extends Module
{
	protected $_dom;

	public function __construct($HTML)
	{
		$this->_dom = new DOMDocument();
		@$this->_dom->loadHTML($HTML);
	}

	public function getHTML()
	{
		return $this->_dom->saveHTML();
	}

	// Return an array of DOMElement objects from a given xpath
	public function Find($XPathString)
	{
		$xpath = new DOMXPath($this->_dom);
		
		$results = $xpath->evaluate($XPathString);

		for ($i = 0; $i < $results->length; $i++) 
		{
			$returnValue[] = $results->item($i);
		}

		return $returnValue;
	}
}

?>
