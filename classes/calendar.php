<?php

namespace thot;

use
	thot\exceptions
;

class calendar implements \iterator
{
	protected $start = null;
	protected $stop = null;
	protected $intervals = array();
	protected $key = 0;
	protected $current = null;

	public function __construct(\dateTime $start, \dateTime $stop)
	{
		if ($start > $stop)
		{
			throw new exceptions\invalidArgument('Start must be less than stop');
		}

		$this->start = $start;
		$this->stop= $stop;

		$this->rewind();
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getStop()
	{
		return $this->stop;
	}

	public function rewind()
	{
		$this->current = clone $this->start;

		return $this;
	}

	public function valid()
	{
		return ($this->current <= $this->stop);
	}

	public function next()
	{
		if ($this->valid() === true)
		{
			$this->key++;
			$this->current->modify('tomorrow');
		}

		return $this;
	}

	public function key()
	{
		return ($this->valid() === false ? null : $this->key);
	}

	public function current()
	{
		return ($this->valid() === false ? null : clone $this->current->modify('midnight'));
	}

	public function getIntervals(\dateTime $dateTime)
	{
		$intervals = array();

		$key = static::getKeyFromDateTime($dateTime);

		if (isset($this->intervals[$key]) === true)
		{
			foreach ($this->intervals[$key] as $interval)
			{
				$intervals[] = clone $interval;
			}
		}

		return $intervals;
	}

	public function getIntervalsSince(\dateTime $dateTime)
	{
		$intervals = $this->getIntervals($dateTime);

		if (sizeof($intervals) > 0)
		{
			$time = time::getFromDateTime($dateTime);

			foreach ($intervals as $key => $interval)
			{
				if ($interval->containsDateTime($dateTime) === false)
				{
					unset($intervals[$key]);
				}
				else
				{
					$interval->setStart(time::getFromDateTime($dateTime));

					break;
				}
			}
		}

		return $intervals;
	}

	public function addInterval(\dateTime $dateTime, interval $interval)
	{
		$key = static::getKeyFromDateTime($dateTime);

		if (isset($this->intervals[$key]) === false)
		{
			$this->intervals[$key] = array();
		}

		$this->intervals[$key] = $interval->mergeIn($this->intervals[$key]);

		return $this;
	}

	protected static function getKeyFromDateTime(\dateTime $dateTime)
	{
		$dateTime = clone $dateTime;

		return $dateTime->modify('midnight')->format('U');
	}
}
