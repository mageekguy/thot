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
		return new calendar($start, $stop);
	}

	protected static function getKeyFromDateTime(\dateTime $dateTime)
	{
		$dateTime = clone $dateTime;

		return $dateTime->modify('midnight')->format('U');
	}
}
