<?php

class SanitizePath extends BaseService
{
	static function Sanitize($Path)
	{
		$Path = strtolower($Path);
		$parameters = explode('/', $Path);
		$parameters = array_filter($parameters);
		$Path = implode('/', $parameters);
		$Path = self::RemoveFileExtension($Path);

		return $Path;
	}

	public function RemoveFileExtension($Path)
	{
		$extension = strrchr($Path, '.'); 

		if($extension) 
		{ 
			$Path = substr($Path, 0, -strlen($extension)); 
		} 

		return $Path; 
	}
}
