<?php

namespace thot\calendar;

use
	thot\calendar,
	thot\calendar\generator\event
;

class generator
{
	protected $opening = array();
	protected $closing = array();

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

	public function generate(\dateTime $start, \dateTime $stop)
	{
		$calendar = new calendar($start, $stop);

		foreach ($calendar as $date)
		{
			$openings = array();

			foreach ($this->opening as $event)
			{
				$interval = $event($date);

				if ($interval !== null)
				{
					$openings = $interval->addTo($openings);
				}
			}

			foreach ($this->closing as $event)
			{
				$interval = $event($date);

				if ($interval !== null)
				{
					$openings = $interval->substractFrom($openings);
				}
			}

			foreach ($openings as $interval)
			{
				$calendar->addInterval($date, $interval);
			}
		}

		return $calendar->rewind();
	}
}
