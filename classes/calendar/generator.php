<?php

namespace thot\calendar;

use
	thot\interval,
	thot\calendar,
	thot\exceptions
;

class generator
{
	protected $opening = array();
	protected $closing = array();

	public function addOpening($day, interval $interval)
	{
		switch ($day)
		{
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
				if (isset($this->opening[$day]) === false)
				{
					$this->opening[$day] = array();
				}

				$this->opening[$day] = $interval->mergeIn($this->opening[$day]);
				return $this;

			default:
				throw new exceptions\invalidArgument('Day \'' . $day . '\' is invalid');
		}
	}

	public function getOpening()
	{
		return $this->opening;
	}

	public function addClosing(\dateTime $dateTime, interval $interval)
	{
		$key = static::getKeyFromDateTime($dateTime);

		if (isset($this->closing[$key]) === false)
		{
			$this->closing[$key] = array();
		}

		$this->closing[$key] = $interval->mergeIn($this->closing[$key]);

		return $this;
	}

	public function getClosing()
	{
		return $this->closing;
	}

	public function generate(\dateTime $start, \dateTime $stop)
	{
		return new calendar($start, $stop);
	}

	protected static function getKeyFromDateTime(\dateTime $dateTime)
	{
		$dateTime = clone $dateTime;

		return $dateTime->modify('midnight')->format('U');
	}
}
