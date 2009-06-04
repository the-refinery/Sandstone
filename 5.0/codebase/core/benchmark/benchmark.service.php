<?php

class Benchmark extends Component
{
	protected $_marks = array();

	static public function Instance()
	{
		static $benchmark;

		if (isset($benchmark) == false)
		{
			$benchmark = new Benchmark();
		}

		return $benchmark;
	}

	public function ProcessMark($MarkName = null)
	{
		$this->_marks[$MarkName] = microtime(true);
	}

	static public function Mark($MarkName = null)
	{
		$benchmark = Benchmark::Instance();

		return $benchmark->ProcessMark($MarkName);
	}

	public function ProcessAllMarks()
	{
		return $this->_marks;
	}

	static public function AllMarks()
	{
		$benchmark = Benchmark::Instance();

		return $benchmark->ProcessAllMarks();
	}

	public function ProcessClear()
	{
		$this->_marks = array();
	}

	static public function Clear()
	{
		$benchmark = Benchmark::Instance();

		return $benchmark->ProcessClear();
	}

	public function ProcessElapsedTime($Start = null, $End = null)
	{
		if ($Start)
		{
			$startPoint = $this->_marks[$Start];
		}
		else
		{
			$startPoint = reset($this->_marks);
		}

		if ($End)
		{
			$endPoint = $this->_marks[$End];
		}
		else
		{
			$endPoint = end($this->_marks);
		}

		return $endPoint - $startPoint;
	}

	static public function ElapsedTime($Start = null, $End = null)
	{
		$benchmark = Benchmark::Instance();

		return $benchmark->ProcessElapsedTime($Start, $End);
	}
}

