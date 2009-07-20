<?php

class FindMatchingRouteSpec extends DescribeBehavior
{
	public function ItShouldFindTheFirstMatchingRoute()
	{
		$route1 = new Mock('IncorrectRoute');
		$route1->Parameters = array('foobar',':foobarid');

		$route2 = new Mock('CorrectRoute');
		$route2->Parameters = array('barfoo',':barfooid');

		$allRoutes = array($route1, $route2);

		$result = FindMatchingRoute::Find('barfoo/5', $allRoutes);

		return $this->Expects($result)->ToBeEqualTo($route2);
	}
}
