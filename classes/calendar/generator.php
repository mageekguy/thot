<?php

namespace thot\calendar;

use
	thot\time,
	thot\calendar,
	thot\calendar\generator\event,
    thot\interval,
    thot\exceptions
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

    public function hasClosing(\dateTime $dateTime)
    {
        if(!($closings = $this->getClosing()))
            return false;
        else {

            foreach($closings as $event) {
                //var_dump($event);
                if($event($dateTime))
                    return true;
            }
        }
        return false;
    }

    public function addPublicHolidays($startDate, $stopDate)
    {
        if ($stopDate < $startDate)
        {
            throw new exceptions\invalidArgument('Start must be less than or equal to stop');
        }

        $closing = $this->getClosing();

        $publicHolidaysDays = array();
        $yearsRange = range($startDate->format('Y'), $stopDate->format('Y'));

        foreach($yearsRange as $year)
        {
            //New year
            $publicHolidaysDays[] = new \DateTime('01/01/' . $year);
            //Fête du travail
            $publicHolidaysDays[] = new \DateTime('05/01/' . $year);
            //8 Mai 1945
            $publicHolidaysDays[] = new \DateTime('05/08/' . $year);
            //Fête nationale
            $publicHolidaysDays[] = new \DateTime('07/14/' . $year);
            //Assomption
            $publicHolidaysDays[] = new \DateTime('08/15/' . $year);
            //La toussaint
            $publicHolidaysDays[] = new \DateTime('11/01/' . $year);
            //Armistice
            $publicHolidaysDays[] = new \DateTime('11/11/' . $year);
            //Noël
            $publicHolidaysDays[] = new \DateTime('12/25/' . $year);

            //Pâques
            $easterDay = new \DateTime(date('m/d/Y', $this->_getEasterDate(0, $year)));
            $publicHolidaysDays[] = $easterDay;
            //Lundi de Pâques
            $lundiPaques = clone $easterDay;
            $publicHolidaysDays[] = $lundiPaques->modify('+1 day');
            //Ascension
            $ascension = clone $easterDay;
            $publicHolidaysDays[] = $ascension->modify('+39 day');
            //Lundi de Pentecote
            $pentecote = clone $easterDay;
            $publicHolidaysDays[] = $pentecote->modify('+50 day');
        }

        $cpt = 0;

        foreach($publicHolidaysDays as $date)
        {   $cpt++;
            if($startDate <= $date && $date <= $stopDate){
                $this->addClosing(new event(function(\dateTime $dateTime) use($date) { return ($dateTime->format('Y-m-d') == $date->format('Y-m-d')); }, new interval(new time(0, 0))));
            }
        }
    }

    protected function _getEasterDate($jourj = 0, $annee = NULL)
    {
        $annee = ($annee == NULL) ? date("Y") : $annee;

        $G   = $annee % 19;
        $C   = floor($annee / 100);
        $C_4 = floor($C / 4);
        $E   = floor((8 * $C + 13) / 25);
        $H   = (19 * $G + $C - $C_4 - $E + 15) % 30;

        if($H == 29) {
            $H = 28;
        } elseif($H == 28 && $G > 10) {
            $H = 27;
        }

        $K  = floor($H / 28);
        $P  = floor(29 / ($H + 1));
        $Q  = floor((21 - $G) / 11);
        $I  = ($K * $P * $Q - 1) * $K + $H;
        $B  = floor($annee / 4) + $annee;
        $J1 = $B + $I + 2 + $C_4 - $C;
        $J2 = $J1 % 7; //jour de pâques (0=dimanche, 1=lundi....)
        $R  = 28 + $I - $J2; // résultat final :)
        $mois = $R > 30 ? 4 : 3; // mois (1 = janvier, ... 3 = mars...)
        $Jour = $mois == 3 ? $R : $R - 31;

        return mktime(0, 0, 0, $mois, $Jour + $jourj, $annee);
    }
}
