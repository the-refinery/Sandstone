<?php

class TranslateRestVerbSpec extends DescribeBehavior
{
	public function ItShouldDecodeAGetRequest()
	{
		$verb = TranslateRestVerb::Translate('GET');

		return $this->Expects($verb)->ToBeEqualTo('GET');
	}

	public function ItShouldDecodeAPostRequest()
	{
		$verb = TranslateRestVerb::Translate('POST');

		return $this->Expects($verb)->ToBeEqualTo('POST');
	}

	public function ItShouldDecodeAPutRequest()
	{
		$verb = TranslateRestVerb::Translate('POST', 'PUT');

		return $this->Expects($verb)->ToBeEqualTo('PUT');
	}

	public function ItShouldDecodeADeleteRequest()
	{
		$verb = TranslateRestVerb::Translate('POST', 'DELETE');

		return $this->Expects($verb)->ToBeEqualTo('DELETE');
	}
}
