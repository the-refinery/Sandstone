<?php

class ParseADirectorySpec extends DescribeBehavior
{
	protected $_parser; 

	public function BeforeEach()
	{
		$this->_parser = new ParseADirectory();
	}

	public function ItShouldFindFilesByDirectory()
	{
		$files = $this->_parser->FindFilesInADirectory('codebase/core/filesystem/spec/utilities/*');

		return $this->Expects($files)->ToContain('codebase/core/filesystem/spec/utilities/test1.txt');
	}
}
