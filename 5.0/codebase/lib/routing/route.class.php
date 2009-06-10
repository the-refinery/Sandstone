<?php

class Route extends Component
{
	protected $_parameters = array();
	protected $_fileType;

	public function __construct($Path)
	{
		$this->_parameters = $this->ConvertPathToParameters($Path);
	}

	public function getParameters()
	{
		return $this->_parameters;
	}

	public function getFileType()
	{
		return $this->_fileType;
	}

	public function CheckRoutingMatch($Path)
	{
		$incomingParameters = $this->ConvertPathToParameters($Path);

		return $incomingParameters == $this->_parameters;
	}

	protected function ConvertPathToParameters($Path)
	{
		$Path = strtolower($Path);
		$this->_fileType = $this->DetermineFileType($Path);
		$Path = $this->RemoveFileExtension($Path);
		$returnValue = explode('/', $Path);
		$returnValue = array_filter($returnValue); // Remove empty elements

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
