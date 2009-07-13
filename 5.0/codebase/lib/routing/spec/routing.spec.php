<?php

class RoutingSpec extends DescribeBehavior
{
	public function ItShouldAddARoute()
	{
		$routing = new Routing();
		$routing->AddRoute('foobar/:foobarid');

		return $this->Expects(count($routing->Routes))->ToBeGreaterThanOrEqualTo(1);
	}

	public function ItShouldFindTheFirstMatchingRoute()
	{
		$routing = new Routing();
		$routing->AddRoute('foobar/:foobarid');
		$routing->AddRoute('barfoo/:barfooid');
		$routing->AddRoute('gorp');

		$result = $routing->FindMatchingRoute('barfoo/5');

		return $this->Expects($result->Path)->ToBeEqualTo('barfoo/:barfooid');
	}
}
