<?php

class FakeTemplate
{
	public $content_to_return;

	public function Content()
	{
		return $this->content_to_return;
	}
}
