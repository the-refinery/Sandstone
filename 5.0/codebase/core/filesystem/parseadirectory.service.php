<?php

class ParseADirectory extends BaseService
{
	static function FindFilesInADirectory($Pattern)
	{
		return glob($Pattern);
	}
}
