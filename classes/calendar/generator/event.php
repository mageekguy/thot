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
	protected $trigger  = null;
	protected $dayIndex = null;

	public function __construct(closure $trigger = null, interval $interval = null, $dayIndex = 0)
	{
		$this->interval = $interval ?: new interval();
		$this->trigger = $trigger ?: function() { return true; };
        $this->dayIndex = (int) $dayIndex;
	}

    public function getDayIndex()
    {
        return $this->dayIndex;
    }

    public function setDayIndex($value)
    {
        $this->dayIndex = $value;
        return $this;
    }

	public function __invoke(\dateTime $dateTime)
	{
		return ($this->trigger->__invoke($dateTime) === false ? null : clone $this->interval);
	}


}
