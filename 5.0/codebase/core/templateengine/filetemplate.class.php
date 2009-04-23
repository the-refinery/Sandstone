<?php

class FileTemplate
{
	protected $_fileName;

	public function __construct($FileName)
	{
		$this->_fileName = $FileName;
	}

	public function Content()
	{
		return file_get_contents($this->_fileName);
	}
}
