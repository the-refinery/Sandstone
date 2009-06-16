<?php

class Route extends Component
{
	protected $_path;
	protected $_parameters = array();
	protected $_extraParameters = array();
	protected $_fileType;

	public function __construct($Path)
	{
		$this->_path = $this->SanitizePath($Path);

		$this->_parameters = $this->ConvertPathToParameters($this->_path);
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getParameters()
	{
		return array_merge($this->_parameters, $this->_extraParameters);
	}

	public function AddParameter($Key, $Value)
	{
		$Key = strtolower($Key);
		$Value = strtolower($Value);

		$this->_extraParameters[$Key] = $Value;
	}

	public function getFileType()
	{
		return $this->_fileType;
	}

	public function CheckRoutingMatch($Path)
	{
		$this->_fileType = $this->DetermineFileType($Path);
		$Path = $this->SanitizePath($Path);
		$pattern = $this->GenerateMatchPattern($this->_parameters);

		return preg_match($pattern, $Path) >= 1;
	}

	public function GenerateMatchPattern($Parameters)
	{
		$path = implode("/", $Parameters);

		return "@^{$path}$@i";
	}

	public function SanitizePath($Path)
	{
		$Path = strtolower($Path);
		$parameters = explode('/', $Path);
		$parameters = array_filter($parameters);
		$returnValue = implode('/', $parameters);
		$returnValue = $this->RemoveFileExtension($returnValue);

		return $returnValue;
	}

	protected function ConvertPathToParameters($Path)
	{
		$Path = $this->SanitizePath($Path);

		$parameters = explode('/', $Path);
		foreach ($parameters as $tempParameter)
		{
			if (strpos($tempParameter, ":") === 0)
			{
				$key = substr($tempParameter, 1);
				$value = "[a-zA-Z0-9_-]+";
			}
			else
			{
				$key = $tempParameter;
				$value = $tempParameter;
			}

			$returnValue[$key] = $value;
		}

		return $returnValue;
	}

	protected function DetermineFileType($Path)
	{
		$pathInfo = pathinfo($Path);
		$extension = $pathInfo['extension'];

		if (is_null($extension))
		{
			$extension = 'htm';
		}

		return $extension;
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
