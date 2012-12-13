<?php

namespace thot\calendar\generator;

use
	closure,
	thot,
	thot\interval
;

class event extends thot\interval
{
	protected $interval = null;
	protected $trigger = null;

	public function __construct(closure $trigger = null, interval $interval = null)
	{
		$this->interval = $interval ?: new interval();
		$this->trigger = $trigger ?: function() { return true; };
	}

	public function __invoke(\dateTime $dateTime)
	{
		return ($this->trigger->__invoke($dateTime) === false ? null : clone $this->interval);
	}
}
