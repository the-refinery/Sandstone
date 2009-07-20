<?php

class ParseADirectorySpec extends DescribeBehavior
{
	public function ItShouldFindFilesByDirectory()
	{
		$files = ParseADirectory::FindFilesInADirectory('codebase/core/filesystem/spec/utilities/*');

		return $this->Expects($files)->ToContain('codebase/core/filesystem/spec/utilities/test1.txt');
	}
}
