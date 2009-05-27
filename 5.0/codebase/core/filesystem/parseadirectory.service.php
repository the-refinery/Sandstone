<?php

class ParseADirectory
{
	public function FindFilesInADirectory($Pattern)
	{
		return glob($Pattern);
	}
}
