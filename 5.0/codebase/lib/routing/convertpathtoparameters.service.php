<?php

class ConvertPathToParameters extends BaseService
{
	static function Convert($Path)
	{
		$returnValue = strtolower($Path);
		$returnValue = explode('/', $returnValue);	

		return $returnValue;
	}
}
