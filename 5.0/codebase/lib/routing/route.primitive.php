<?php

class Route extends Component
{
	protected $_path;
	protected $_parameters = array();
	protected $_extraParameters = array();
	protected $_fileType;

	public function __construct($Path)
	{
		$sanitize = new SanitizePath($Path);
		$this->_path = $sanitize->Path;

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

		$sanitize = new SanitizePath($Path);
		$Path = $sanitize->Path;

		$pattern = $this->GenerateMatchPattern($this->_parameters);

		return preg_match($pattern, $Path) >= 1;
	}

	public function GenerateMatchPattern($Parameters)
	{
		$path = implode("/", $Parameters);

		return "@^{$path}$@i";
	}

	protected function ConvertPathToParameters($Path)
	{
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
}
