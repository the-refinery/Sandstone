<?php

class InterpretRestRequestSpec extends DescribeBehavior
{
	public function ItShouldDecodeAGetRequest()
	{
		$decodeRest = new InterpretRestRequest('GET');

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('GET');
	}

	public function ItShouldDecodeAPostRequest()
	{
		$decodeRest = new InterpretRestRequest('POST');

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('POST');
	}

	public function ItShouldDecodeAPutRequest()
	{
		$decodeRest = new InterpretRestRequest('POST', 'PUT');

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('PUT');
	}

	public function ItShouldDecodeADeleteRequest()
	{
		$decodeRest = new InterpretRestRequest('POST', 'DELETE');

		return $this->Expects($decodeRest->Verb)->ToBeEqualTo('DELETE');
	}
}
