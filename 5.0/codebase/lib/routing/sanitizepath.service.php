<?php

class SanitizePath extends BasePrimitive
{
	protected $_path;

	public function __construct($Path)
	{
		$Path = strtolower($Path);
		$parameters = explode('/', $Path);
		$parameters = array_filter($parameters);
		$Path = implode('/', $parameters);
		$Path = $this->RemoveFileExtension($Path);

		$this->_path = $Path;
	}

	public function getPath()
	{
		return $this->_path;
	}
	
	protected function RemoveFileExtension($Path)
	{
		$extension = strrchr($Path, '.'); 

		if($extension) 
		{ 
			$Path = substr($Path, 0, -strlen($extension)); 
		} 

		return $Path; 
	}
}
