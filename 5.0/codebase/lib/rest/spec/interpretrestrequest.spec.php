<?php

class InterpretRestRequestSpec extends DescribeBehavior
{
	public function ItShouldDecodeAGetRequest()
	{
		$mockServer = array('REQUEST_METHOD' => 'GET');
		$server = new HTTPRequest($mockServer);
		$decodeRest = new InterpretRestRequest($server, array());

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('GET');
	}

	public function ItShouldDecodeAPostRequest()
	{
		$mockServer = array('REQUEST_METHOD' => 'POST');
		$server = new HTTPRequest($mockServer);
		$decodeRest = new InterpretRestRequest($server, array());

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('POST');
	}

	public function ItShouldDecodeAPutRequest()
	{
		$server = new HTTPRequest(array());
		$request = array('_method' => 'PUT');
		$decodeRest = new InterpretRestRequest($server, $request);

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('PUT');
	}

	public function ItShouldDecodeADeleteRequest()
	{
		$server = new HttpRequest(array());
		$request = array('_method' => 'DELETE');

		$decodeRest = new InterpretRestRequest($server, $request);

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('DELETE');
	}
}
