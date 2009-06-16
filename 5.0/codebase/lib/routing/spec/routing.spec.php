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

	// RESTFUL GET ACTIONS
	
	public function ItShouldMatchAnyRestfulIndexAction()
	{
		$routing = new Routing();
		$result = $routing->FindMatchingRoute('Cars');

		return $this->Expects($result->Path)->ToBeEqualTo(':controller');
	}

	public function ItShouldMatchARestfulShowAction()
	{
		$routing = new Routing();
		$result = $routing->FindMatchingRoute('Car/4');

		return $this->Expects($result->Path)->ToBeEqualTo(':controller/:id');
	}

	public function ItShouldMatchARestfulNewAction()
	{
		$routing = new Routing();
		$result = $routing->FindMatchingRoute('Cars/New');

		return $this->Expects($result->Path)->ToBeEqualTo(':controller/new');
	}

	public function ItShouldMatchARestfulEditAction()
	{
		$routing = new Routing();
		$result = $routing->FindMatchingRoute('Car/4/Edit');

		return $this->Expects($result->Path)->ToBeEqualTo(':controller/:id/edit');
	}
}
