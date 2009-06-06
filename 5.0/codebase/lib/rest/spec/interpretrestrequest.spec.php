<?php

class InterpretRestRequestSpec extends DescribeBehavior
{
	public function ItShouldDecodeAGetRequest()
	{
		$server = new Mock('HttpRequest');
		$server->SetPropertyValue('Method','GET');

		$decodeRest = new InterpretRestRequest($server, array());

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('GET');
	}

	public function ItShouldDecodeAPostRequest()
	{
		$server = new Mock('HttpRequest');
		$server->SetPropertyValue('Method','POST');

		$decodeRest = new InterpretRestRequest($server, array());

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('POST');
	}

	public function ItShouldDecodeAPutRequest()
	{
		$server = new Mock('HttpRequest');
		$request = array('_method' => 'PUT');

		$decodeRest = new InterpretRestRequest($server, $request);

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('PUT');
	}

	public function ItShouldDecodeADeleteRequest()
	{
		$server = new Mock('HttpRequest');
		$request = array('_method' => 'DELETE');

		$decodeRest = new InterpretRestRequest($server, $request);

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('DELETE');
	}
}
