<?php

class BenchmarkSpec extends DescribeBehavior
{
	public function BeforeEach()
	{
		Benchmark::Clear();
	}

	public function ItShouldMarkATime()
	{
		Benchmark::Mark();

		return $this->Expects(count(Benchmark::AllMarks()))->ToBeEqualTo(1);
	}

	public function ItShouldClearMarks()
	{
		Benchmark::Mark();

		Benchmark::Clear();

		return $this->Expects(count(Benchmark::AllMarks()))->ToBeEqualTo(0);
	}

	public function ItShouldCalculateElapsedTime()
	{
		Benchmark::Mark('foo');
		Benchmark::Mark('bar');
	
		$elapsedTime = Benchmark::ElapsedTime();

		return $this->Expects($elapsedTime)->ToBeGreaterThan(0);
	}

	public function ItShouldCalculatedElapsedTimeBetweenNamedMarks()
	{
		Benchmark::Mark('foo');
		Benchmark::Mark('bar');
		Benchmark::Mark('gorp');
		Benchmark::Mark('blech');

		$shortTime = Benchmark::ElapsedTime('bar','gorp');
		$longTime = Benchmark::ElapsedTime('foo','blech');

		return $this->Expects($shortTime)->ToBeLessThan($longTime);
	}
}
