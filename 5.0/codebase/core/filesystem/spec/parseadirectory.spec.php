<?php

class ParseADirectorySpec extends DescribeBehavior
{
	public function ItShouldFindFilesByDirectory()
	{
		$parser = new ParseADirectory();

		$files = $parser->FindFilesInADirectory('codebase/core/filesystem/spec/utilities/*');

		return $this->Expects($files)->ToContain('codebase/core/filesystem/spec/utilities/test1.txt');
	}
}
