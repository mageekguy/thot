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
	protected $round = 0;

	public function setDelay($delay, \dateTime $now)
	{
		$this->now = $now;
		$this->delay = $delay;

		return $this;
	}

	public function getNow()
	{
		return $this->now;
	}

	public function getDelay()
	{
		return $this->delay;
	}

	public function setRound($round)
	{
		$this->round = $round;

		return $this;
	}

	public function getRound()
	{
		return $this->round;
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
				foreach ($this->getNextIntervalsFromDateTime($now, $stop) as $interval)
				{
					$duration = $interval->getDuration($now);

					if ($delay - $duration > 0)
					{
						$delay -= $duration;
					}
					else
					{
						$interval->getStart()->setInDateTime($now)->modify('+' . $delay . ' minutes');
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

		$time = time::getFromDateTime($dateTime)->round($this->round);
		$time->setInDateTime($dateTime);

		if ($delay <= 0 && $calendar->moveTo($dateTime) === true)
		{
			foreach ($this->getDateTimeIntervals($date = $calendar->current()) as $interval)
			{
				if ($interval->isBeforeDateTime($dateTime) === false)
				{
					if ($interval->containsDateTime($dateTime) === true)
					{
						$interval->setStart($time);
					}

					$calendar->addInterval($date, $interval);
				}
			}

			while ($calendar->next()->valid() === true)
			{
				$calendar->addIntervals($calendar->current(), $this->getDateTimeIntervals($calendar->current()));
			}
		}

		return $calendar->rewind();
	}

	public function getNextIntervalsFromDateTime(\dateTime $dateTime, \dateTime $stop)
	{
		$intervals = array();

		$dateIntervals = $this->getDateTimeIntervals($dateTime);

		while (sizeof($dateIntervals) <= 0 && $dateTime <= $stop)
		{
			$dateTime->modify('tomorrow');
			$dateIntervals = $this->getDateTimeIntervals($dateTime);
		}

		if (sizeof($dateIntervals) > 0)
		{
			foreach ($dateIntervals as $interval)
			{
				switch (true)
				{
					case $interval->isBeforeDateTime($dateTime):
						break;

					case $interval->containsDateTime($dateTime):
						$dateTimeStart = time::getFromDateTime($dateTime);

						if ($dateTimeStart->isGreaterThan($interval->getStart()) === true)
						{
							$interval->setStart($dateTimeStart);
						}

						$intervals[] = $interval;
						break;

					default:
						if (sizeof($intervals) <= 0)
						{
							$dateTime->modify('midnight')->modify('+' . $interval->getStart()->toMinutes() . ' minutes');
						}

						$intervals[] = $interval;
				}
			}
		}

		return $intervals;
	}

	protected function getDateTimeIntervals(\dateTime $dateTime)
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
