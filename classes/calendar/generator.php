<?php

namespace thot\calendar;

use
	thot\time,
	thot\calendar,
	thot\calendar\generator\event
;

class generator
{
	protected $now = null;
	protected $delay = null;
	protected $opening = array();
	protected $closing = array();

	public function getNow()
	{
		return $this->now;
	}

	public function getDelay()
	{
		return $this->delay;
	}

	public function setDelay($delay, \dateTime $now)
	{
		$this->now = $now;
		$this->delay = $delay;

		return $this;
	}

	public function addOpening(event $event)
	{
		$this->opening[] = $event;

		return $this;
	}

	public function getOpening()
	{
		return $this->opening;
	}

	public function addClosing(event $event)
	{
		$this->closing[] = $event;

		return $this;
	}

	public function getClosing()
	{
		return $this->closing;
	}

	public function generate(\dateTime $start, \dateTime $stop, \dateTime $dateTime = null)
	{
		$calendar = new calendar($start, $stop);

		if ($dateTime === null)
		{
			$dateTime = $start;
		}

		$delay = $this->delay;

		if ($delay > 0)
		{
			$now = clone $this->now;

			while ($delay > 0 && $now <= $stop)
			{
				$intervals = $this->getNextIntervalsFromDateTime($now, $stop);

				foreach ($intervals as $interval)
				{
					$duration = $interval->getDuration($now);

					if ($delay - $duration > 0)
					{
						$delay -= $duration;
					}
					else
					{
						$now
							->modify('midnight')
							->modify('+' . ($interval->getStart()->toMinutes() + $delay) . ' minutes')
						;

						$delay = 0;

						break 2;
					}
				}

				$now->modify('tomorrow');
			}

			if ($delay <= 0 && $now > $dateTime)
			{
				$dateTime = $now;
			}
		}

		if ($delay <= 0 && $dateTime <= $stop)
		{
			$realStart = clone $dateTime;
			$realStart->modify('midnight');

			foreach ($calendar as $date)
			{
				if ($date >= $realStart)
				{
					foreach ($this->computeCalendarIntervals($date) as $interval)
					{
						if ($date->format('Y-m-d') == $dateTime->format('Y-m-d') && $interval->containsDateTime($dateTime) === true)
						{
							$interval->setStart(time::getFromDateTime($dateTime));
						}

						$calendar->addInterval($date, $interval);
					}
				}
			}
		}

		return $calendar->rewind();
	}

	public function getNextIntervalsFromDateTime(\dateTime $dateTime, \dateTime $stop)
	{
		$dateTime = clone $dateTime;

		$intervals = array();

		$dateIntervals = $this->computeCalendarIntervals($dateTime);

		while (sizeof($dateIntervals) <= 0 && $dateTime <= $stop)
		{
			$dateTime->modify('tomorrow');
			$dateIntervals = $this->computeCalendarIntervals($dateTime);
		}

		if (sizeof($dateIntervals) > 0)
		{
			$start = time::getFromDateTime($dateTime);

			foreach ($dateIntervals as $interval)
			{
				switch (true)
				{
					case $interval->isBeforeDateTime($dateTime):
						break;

					case $interval->containsDateTime($dateTime):
						$dateTimeStart = time::getFromDateTime($dateTime);

						if ($dateTimeStart->isGreaterThan($interval->getStart()) === true && $dateTimeStart->isLessThan($interval->getStop()) === true)
						{
							$interval->setStart($dateTimeStart);
							$intervals[] = $interval;
						}

						break;

					default:
						$intervals[] = $interval;
				}
			}
		}

		return $intervals;
	}

	protected function computeCalendarIntervals(\dateTime $dateTime)
	{
		$intervals = array();

		foreach ($this->opening as $event)
		{
			$interval = $event($dateTime);

			if ($interval !== null)
			{
				$intervals = $interval->addTo($intervals);
			}
		}

		foreach ($this->closing as $event)
		{
			$interval = $event($dateTime);

			if ($interval !== null)
			{
				$intervals = $interval->substractFrom($intervals);
			}
		}

		return $intervals;
	}
}
