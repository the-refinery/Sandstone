<?php

class HTTPRequestSpec extends DescribeBehavior
{
	protected $_httpRequest;

	public function ItShouldReadTheIncomingRequestMethod()
	{
		$mockServer = array('REQUEST_METHOD' => 'GET');
		$this->_httpRequest = new HTTPRequest($mockServer);

		$this->Expects($this->_httpRequest->Method)->ToBeEqualTo('GET');
	}

	public function ItShouldCheckThatWeAreNotOnHttps()
	{
		$mockServer = array('HTTPS' => '');
		$this->_httpRequest = new HTTPRequest($mockServer);

		$this->Expects($this->_httpRequest->IsHttps)->ToNotBeTrue();
	}

	public function ItShouldCheckThatWeAreOnHttps()
	{
		$mockServer = array('HTTPS' => 'on');
		$this->_httpRequest = new HTTPRequest($mockServer);

		$this->Expects($this->_httpRequest->IsHttps)->ToBeTrue();
	}

	public function ItShouldReportTheClientsIpAddress()
	{
		$mockServer = array('REMOTE_ADDR' => '127.0.0.1');
		$this->_httpRequest = new HTTPRequest($mockServer);

		$this->Expects($this->_httpRequest->ClientIP)->ToBeEqualTo('127.0.0.1');
	}
		
}
