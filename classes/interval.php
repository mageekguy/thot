<?php

namespace thot;

use
	thot\exceptions
;

class interval
{
	protected $start = null;
	protected $stop = null;

	public function __construct(time $start = null, time $stop = null)
	{
		if ($start === null)
		{
			$start = new time();
		}

		if ($stop === null)
		{
			$stop = new time(23, 59);
		}

		if ($stop->toMinutes() <= $start->toMinutes())
		{
			throw new exceptions\invalidArgument('Start must be less than stop');
		}

		$this->start = $start;
		$this->stop = $stop;
	}

	public function setStart(time $start)
	{
		if ($this->stop->toMinutes() <= $start->toMinutes())
		{
			throw new exceptions\invalidArgument('Start must be less than stop');
		}

		$this->start = $start;

		return $this;
	}

	public function setStop(time $stop)
	{
		if ($this->start->toMinutes() >= $stop->toMinutes())
		{
			throw new exceptions\invalidArgument('Stop must be greater than start');
		}

		$this->stop = $stop;

		return $this;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getStop()
	{
		return $this->stop;
	}

	public function add(interval $interval)
	{
		$newInterval = clone $this;

		if ($this->stop->isGreaterThanOrEqualTo($interval->start) === true)
		{
			if ($this->start->isGreaterThan($interval->start) === true)
			{
				$newInterval->start = $interval->start;
			}

			if ($this->stop->isLessThan($interval->stop) === true)
			{
				$newInterval->stop = $interval->stop;
			}
		}

		return $newInterval;
	}

	public function substract(interval $interval)
	{
		switch (true)
		{
			/*
			case $this->start == $interval->start && $this->stop->isGreaterThan($interval->stop):
				return array(new static($interval->stop->addMinutes(1), clone $this->stop));

			case $this->start->isLessThan($interval->start) && $this->stop->isGreaterThan($interval->stop):
				return array(
					new static(clone $this->start, $interval->start->substractMinutes(1)),
					new static($interval->stop->addMinutes(1), clone $this->stop)
				);

			case $this->start->isLessThan($interval->start) && $this->stop->isLessThanOrEqualTo($interval->stop):
				return array(
					new static(clone $this->start, $interval->start->substractMinutes(1)),
				);

			case $this->start->isGreaterThan($interval->start) && $this->stop->isGreaterThan($interval->stop):
				return array(
					new static($interval->stop->addMinutes(1), clone $this->stop),
				);
			*/

			case $this->start->isGreaterThan($interval->start) && $this->start->isLessThan($interval->stop) && $this->stop->isGreaterThan($interval->stop):
				return array(
					new static(clone $interval->stop->addMinutes(1), clone $this->stop)
				);

			case $interval->start->isGreaterThan($this->start) && $interval->start->isLessThan($this->stop) && $interval->stop->isGreaterThan($this->stop):
				return array(
					new static(clone $this->start, $interval->start->substractMinutes(1))
				);

			case $this->start->isLessThan($interval->start) && $this->stop->isGreaterThan($interval->stop):
				return array(
					new static(clone $this->start, $interval->start->substractMinutes(1)),
					new static($interval->stop->addMinutes(1), clone $this->stop)
				);

			case $this->start == $interval->start && $this->stop->isGreaterThan($interval->stop):
				return array(
					new static($interval->stop->addMinutes(1), clone $this->stop)
				);

			case $this->start->isLessThan($interval->start) && $this->stop == $interval->stop:
				return array(
					new static(clone $this->start, $interval->start->substractMinutes(1))
				);

			default:
				return array();
		}
	}

	public function intersect(interval $interval)
	{
		switch (true)
		{
			case $this->start == $interval->start && $this->stop == $interval->stop:
			case $this->start->isGreaterThanOrEqualTo($interval->start) && $this->stop->isLessThanOrEqualTo($interval->stop):
				return clone $this;

			case $this->start->isLessThanOrEqualTo($interval->start) && $this->stop->isGreaterThanOrEqualTo($interval->stop):
				return clone $interval;

			case $interval->start->isGreaterThan($this->start) && $interval->start->isLessThan($this->stop):
				return new static(clone $interval->start, clone $this->stop);

			case $this->start->isGreaterThan($interval->start) && $this->start->isLessThan($interval->stop):
				return new static(clone $this->start, clone $interval->stop);

			default:
				return null;
		}
	}

	public function addTo(array $intervals)
	{
		$that = $this;

		foreach ($intervals as $key => $interval)
		{
			if ($interval->intersect($this) !== null)
			{
				unset($intervals[$key]);
				$that = $that->add($interval);
			}
		}

		$intervals[] = $that;

		usort($intervals, function($a, $b) { return ($a->getStop()->isLessThanOrEqualTo($b->getStart()) ? -1 : ($a->getStart()->isGreaterThanOrEqualTo($b->getStop()) ? 1 : 0)); });

		$intervals = array_values($intervals);

		return $intervals;
	}

	public function substractFrom(array $intervals)
	{
		$newIntervals = array();

		foreach ($intervals as $key => $interval)
		{
			if ($interval->intersect($this) === null)
			{
				$newIntervals[] = $interval;
			}
			else foreach ($interval->substract($this) as $substractedInterval)
			{
				$newIntervals[] = $substractedInterval;
			}
		}

		usort($newIntervals, function($a, $b) { return ($a->getStop()->isLessThanOrEqualTo($b->getStart()) ? -1 : ($a->getStart()->isGreaterThanOrEqualTo($b->getStop()) ? 1 : 0)); });

		return array_values($newIntervals);
	}

	public function containsDateTime(\dateTime $dateTime)
	{
		$time = time::getFromDateTime($dateTime);

		return $this->start->isLessThanOrEqualTo($time) && $this->stop->isGreaterThanOrEqualTo($time);
	}

	public function getMinutesToStop(\dateTime $dateTime)
	{
		$minutes = null;

		if ($this->containsDateTime($dateTime) === true)
		{
			$time = time::getFromDateTime($dateTime);

			$minutes = $this->stop->diff($time);
		}

		return $minutes;
	}
}
