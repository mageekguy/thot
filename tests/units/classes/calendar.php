<?php

namespace thot\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	thot\time,
	thot\interval,
	thot\calendar as testedClass
;

class calendar extends atoum
{
	public function testClass()
	{
		$this->testedClass->implements('iterator');
	}

	public function test__construct()
	{
		$this
			->if($calendar = new testedClass($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))
			->then
				->object($calendar->getStart())->isIdenticalTo($start)
				->object($calendar->getStop())->isIdenticalTo($stop)
			->exception(function() { new testedClass(new \dateTime('2012-12-07'), new \dateTime('2012-12-01')); })
				->isInstanceOf('thot\exceptions\invalidArgument')
				->hasMessage('Start must be less than stop')
		;
	}

	public function testRewind()
	{
		$this
			->if($calendar = new testedClass($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))
			->then
				->object($calendar->rewind())->isIdenticalTo($calendar)
				->integer($calendar->key())->isZero()
				->object($calendar->current())->isEqualTo($start)
			->if($calendar->next())
			->then
				->object($calendar->rewind())->isIdenticalTo($calendar)
				->integer($calendar->key())->isZero()
				->object($calendar->current())->isEqualTo($start)
		;
	}

	public function testNext()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(1)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +1 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(2)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +2 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(3)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +3 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(4)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +4 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(5)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +5 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isTrue()
					->integer($calendar->key())->isEqualTo(6)
					->object($calendar->current())->isEqualTo(new \dateTime('2012-12-01 +6 day'))
				->object($calendar->next())->isIdenticalTo($calendar)
					->boolean($calendar->valid())->isFalse()
					->variable($calendar->key())->isNull()
					->variable($calendar->current())->isNull()
		;
	}

	public function testAddInterval()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->object($calendar->addInterval($date1 = new \dateTime('2012-12-01'), $interval1 = new interval()))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->object($calendar->addInterval($date1, $otherInterval1 = new interval()))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->object($calendar->addInterval($date2 = new \dateTime('2012-12-02'), $interval2 = new interval(new time(8), new time(12))))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->array($calendar->getIntervals($date2))->isEqualTo(array($interval2))
				->object($calendar->addInterval($date2 = new \dateTime('2012-12-02'), $otherInterval2 = new interval(new time(14), new time(18))))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->array($calendar->getIntervals($date2))->isEqualTo(array($interval2, $otherInterval2))
				->object($calendar->addInterval($date3 = new \dateTime('2012-12-03'), $interval3 = new interval(new time(14), new time(18))))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->array($calendar->getIntervals($date2))->isEqualTo(array($interval2, $otherInterval2))
				->array($calendar->getIntervals($date3))->isEqualTo(array($interval3))
				->object($calendar->addInterval($date3 = new \dateTime('2012-12-03'), $otherInterval3 = new interval(new time(8), new time(12))))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->array($calendar->getIntervals($date2))->isEqualTo(array($interval2, $otherInterval2))
				->array($calendar->getIntervals($date3))->isEqualTo(array($otherInterval3, $interval3))
				->object($calendar->addInterval($date3 = new \dateTime('2012-12-03'), new interval(new time(10), new time(16))))->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1))
				->array($calendar->getIntervals($date2))->isEqualTo(array($interval2, $otherInterval2))
				->array($calendar->getIntervals($date3))->isEqualTo(array(new interval(new time(8), new time(18))))
		;
	}

	public function testAddIntervals()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->object($calendar->addIntervals($date1 = new \dateTime('2012-12-01'), array(
							$interval1 = new interval(new time(8), new time(12)),
							$otherInterval1 = new interval(new time(14), new time(18))
						)
					)
				)->isIdenticalTo($calendar)
				->array($calendar->getIntervals($date1))->isEqualTo(array($interval1, $otherInterval1))
		;
	}

	public function testGetIntervals()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->array($calendar->getIntervals(new \dateTime('2012-12-01')))->isEmpty()
			->if($calendar->addInterval(new \dateTime('2012-12-03'), $interval = new interval()))
			->then
				->array($calendar->getIntervals(new \dateTime('2012-11-30')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-01')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-02')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-03')))->isEqualTo(array($interval))
				->array($calendar->getIntervals(new \dateTime('2012-12-03 12:00:00')))->isEqualTo(array($interval))
				->array($calendar->getIntervals(new \dateTime('2012-12-04')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-05')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-06')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-07')))->isEmpty()
				->array($calendar->getIntervals(new \dateTime('2012-12-08')))->isEmpty()
		;
	}

	public function testGetIntervalsSince()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->array($calendar->getIntervalsSince(new \dateTime('2012-11-30')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-01')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-02')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-03')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-04')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-05')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-06')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-07')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-08')))->isEmpty()
			->if($calendar->addInterval(new \dateTime('2012-12-03'), $interval = new interval()))
			->then
				->array($calendar->getIntervalsSince(new \dateTime('2012-11-30')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-01')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-02')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-03')))->isEqualTo(array($interval))
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-03 12:00:00')))->isEqualTo(array(new interval(new time(12))))
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-04')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-05')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-06')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-07')))->isEmpty()
				->array($calendar->getIntervalsSince(new \dateTime('2012-12-08')))->isEmpty()
		;
	}

	public function testGetIntervalAtDateTime()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->variable($calendar->getIntervalAtDateTime(new \dateTime()))->isNull()
			->if($calendar->addInterval(new \dateTime('2012-12-03'), $interval = new interval()))
			->then
				->variable($calendar->getIntervalAtDateTime(new \dateTime('2012-12-01')))->isNull()
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-03 00:00:00')))->isEqualTo($interval)
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-03 23:59:00')))->isEqualTo($interval)
			->if($calendar->addInterval(new \dateTime('2012-12-05'), $otherInterval = new interval(new time(8), new time(18))))
			->then
				->variable($calendar->getIntervalAtDateTime(new \dateTime('2012-12-01')))->isNull()
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-03 00:00:00')))->isEqualTo($interval)
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-03 23:59:00')))->isEqualTo($interval)
				->variable($calendar->getIntervalAtDateTime(new \dateTime('2012-12-05 00:00:00')))->isNull()
				->variable($calendar->getIntervalAtDateTime(new \dateTime('2012-12-05 07:59:00')))->isNull()
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-05 08:00:00')))->isEqualTo($otherInterval)
				->object($calendar->getIntervalAtDateTime(new \dateTime('2012-12-05 18:00:00')))->isEqualTo($otherInterval)
				->variable($calendar->getIntervalAtDateTime(new \dateTime('2012-12-05 18:01:00')))->isNull()
		;
	}

	public function testIsAvailable()
	{
		$this
			->if($calendar = new testedClass($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))
			->then
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
			->if($calendar->addInterval(new \dateTime('2012-12-02'), new interval()))
			->then
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
		;
	}

	public function testMoveToDate()
	{
		$this
			->if($calendar = new testedClass(new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))
			->then
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-01')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-02')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-03')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-05')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-06')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo($target = new \dateTime('2012-12-07')))->isTrue()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
				->boolean($calendar->moveTo(new \dateTime('2012-12-08')))->isFalse()
				->object($calendar->current())->isEqualTo($target)
				->boolean($calendar->valid())->isTrue()
		;
	}

	public function testGetFirstOpenDateTime()
	{
		$this
			->if($calendar = new testedClass($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))
			->then
				->variable($calendar->getFirstOpenDateTime())->isNull()
			->if($calendar->addInterval(new \dateTime('2012-12-05'), new interval()))
			->then
				->object($calendar->getFirstOpenDateTime())->isEqualTo(new \dateTime('2012-12-05'))
			->if($calendar->addInterval(new \dateTime('2012-12-02'), new interval(new time(14), new time(18))))
			->then
				->object($calendar->getFirstOpenDateTime())->isEqualTo(new \dateTime('2012-12-02 14:00:00'))
				->object($calendar->current())->isEqualTo(new \dateTime('2012-12-02'))
			->if($calendar->rewind())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->then
				->object($calendar->getFirstOpenDateTime())->isEqualTo(new \dateTime('2012-12-02 14:00:00'))
				->object($calendar->current())->isEqualTo(new \dateTime('2012-12-02'))
			->if($calendar->rewind())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->and($calendar->next())
			->then
				->boolean($calendar->valid())->isFalse()
				->object($calendar->getFirstOpenDateTime())->isEqualTo(new \dateTime('2012-12-02 14:00:00'))
				->object($calendar->current())->isEqualTo(new \dateTime('2012-12-02'))
				->boolean($calendar->valid())->isTrue()
		;
	}

    public function testSegmentizeDate()
    {
        $this
            ->if($calendar = new testedClass($start = new \DateTime('10/06/1976'), $stop = new \DateTime('10/07/1976')))
            ->then
                ->array($calendar->segmentizeDate($start))->isEmpty()
                ->array($calendar->segmentizeDate($stop->modify('+15 day')))->isEmpty()
                ->array($calendar->segmentizeDate($stop))->isEmpty()
            ->if($calendar->addInterval($start, new Interval(new Time(9, 0), new Time(12, 0))))
            ->if($calendar->addInterval($start, new Interval(new Time(14, 0), new Time(16, 0))))
            ->then
                ->array($calendar->segmentizeDate($start))->isEqualTo(array(
                    array(
                        new Time(9,0),
                        new Time(9,30),
                        new Time(10,0),
                        new Time(10,30),
                        new Time(11,0),
                        new Time(11,30),
                        new Time(12)
                    ),
                    array(
                        new Time(14,0),
                        new Time(14,30),
                        new Time(15,0),
                        new Time(15,30),
                        new Time(16,0)
                    )
                )
        )
            ->array($calendar->segmentizeDate($stop->modify('+15 day')))->isEmpty()
            ->array($calendar->segmentizeDate($stop))->isEmpty()
            ->array($calendar->segmentizeDate($start->setTime(14, 11, 53)))->isEqualTo(array(
                array(
                    new Time(9,0),
                    new Time(9,30),
                    new Time(10,0),
                    new Time(10,30),
                    new Time(11,0),
                    new Time(11,30),
                    new Time(12)
                ),
                array(
                    new Time(14,0),
                    new Time(14,30),
                    new Time(15,0),
                    new Time(15,30),
                    new Time(16,0)
                )
            )
        )
        ;
    }

    public function testGetClosedDays()
    {
        $this
            ->if($calendar = new testedClass($start = new \DateTime('10/06/1976'), new \DateTime('10/08/1976')))
                ->and($calendar->addInterval(new \DateTime('10/06/1976'), $interval = new Interval(new Time(10, 45), new Time(12))))
                ->and($calendar->addInterval(new \DateTime('10/07/1976'), $interval = new Interval(new Time(8, 45), new Time(18))))
                ->and($calendar->addInterval(new \DateTime('10/08/1976'), $interval = new Interval(new Time(15), new Time(18))))
            ->then
                ->array($calendar->getClosedDays())->isEqualTo(array())
            ->if($calendar = new testedClass($start = new \DateTime('10/06/1976'), new \DateTime('10/08/1976')))
                ->and($calendar->addInterval(new \DateTime('10/06/1976'), new Interval(new Time(10, 45), new Time(12))))
            ->then
                ->array($calendar->getClosedDays())->isEqualTo(array(
                    new \DateTime('10/07/1976'),
                    new \DateTime('10/08/1976')
            )
        )
        ;
    }
}
