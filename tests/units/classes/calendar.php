<?php

namespace thot\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	thot\time,
	thot\interval,
	thot\calendar as testedClass
;

class calendar extends atoum\test
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
				->integer($calendar->key())->isEqualTo(1)
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
}
