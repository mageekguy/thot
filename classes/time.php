<?php

namespace thot;

use
	thot\time,
	thot\exceptions
;

class time
{
	protected $minutes = 0;

	public function __construct($hour = 0, $minute = 0)
	{
		$this
			->setHour($hour)
			->setMinute($minute)
		;
	}

	public function getHour()
	{
		return ($this->minutes - ($this->minutes % 60)) / 60;
	}

	public function setHour($hour)
	{
		$hour = (int) $hour;

		if ($hour < 0 || $hour > 23)
		{
			throw new exceptions\invalidArgument('Hour must be between 0 and 23');
		}

		$this->minutes = ($this->minutes - $this->getHour()) + ($hour * 60);

		return $this;
	}

	public function setMinute($minute)
	{
		$minute = (int) $minute;

		if ($minute < 0 || $minute > 59)
		{
			throw new exceptions\invalidArgument('Minute must be between 0 and 59');
		}

		$this->minutes = ($this->minutes - $this->getMinute()) + $minute;

		return $this;

	}

	public function getMinute()
	{
		return $this->minutes % 60;
	}

	public function toMinutes()
	{
		return $this->minutes;
	}

	public function isGreaterThan(time $time)
	{
		return $this->toMinutes() > $time->toMinutes();
	}

	public function isGreaterThanOrEqualTo(time $time)
	{
		return $this->toMinutes() >= $time->toMinutes();
	}

	public function isLessThan(time $time)
	{
		return ($this->isGreaterThanOrEqualTo($time) === false);
	}

	public function isLessThanOrEqualTo(time $time)
	{
		return ($this->isGreaterThan($time) === false);
	}

	public function addMinutes($minutes)
	{
		$newTime = clone $this;

		$newTime->minutes = ($this->minutes + abs($minutes)) % 1440;

		return $newTime;
	}

	public function substractMinutes($minutes)
	{
		$newTime = clone $this;

		$newTime->minutes = ($this->minutes - abs($minutes)) % 1440;

		if ($newTime->minutes < 0)
		{
			$newTime->minutes = 1440 + $newTime->minutes;
		}

		return $newTime;
	}

	public function diff(time $time)
	{
		return $this->minutes - $time->minutes;
	}

	public static function getFromDateTime(\dateTime $dateTime)
	{
		return new static($dateTime->format('G'), $dateTime->format('i'));
	}
}
