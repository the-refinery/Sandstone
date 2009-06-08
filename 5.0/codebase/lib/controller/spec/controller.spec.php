<?php

class ControllerSpec extends DescribeBehavior
{
	public function ItShouldDetermineTheEntityFromTheNameOfTheController()
	{
		$controller = new FooBarController();

		return $this->Expects($controller->EntityType)->ToBeEqualTo('foobar');
	}

	public function ItShouldTellYouIfTheControllerIsMappedToAnEntity()
	{
		$controller = new FooBarController();

		return $this->Expects($controller->IsEntityBased)->ToBeTrue();
	}

	public function ItShouldTellYouThatAControllerIsNotMappedToAnEntity()
	{
		$controller = new HomeController();

		return $this->Expects($controller->IsEntityBased)->ToNotBeTrue();
	}

	public function ItShouldAssumeThePrimaryKeyFieldOfTheEntity()
	{
		$controller = new FooBarController();

		return $this->Expects($controller->PrimaryKeyField)->ToBeEqualTo('foobarid');
	}
}
