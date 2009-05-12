<?php

class Source 
{
	// The first argument of mixin functions should be $Self,
	// which is a fake reference to $this
	public function Foo($Self, $String = 'foo') 
	{
		return $String;
	}
}

