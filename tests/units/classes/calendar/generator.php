<?php

namespace thot\tests\units\calendar;

require __DIR__ . '/../../runner.php';

use
	atoum,
	thot\time,
	thot\interval,
	thot\calendar,
	thot\calendar\generator\event,
	thot\calendar\generator as testedClass
;

class generator extends atoum
{
	public function test__construct()
	{
		$this
			->if($generator = new testedClass())
			->then
				->array($generator->getOpening())->isEmpty()
				->array($generator->getClosing())->isEmpty()
				->variable($generator->getNow())->isNull()
				->variable($generator->getDelay())->isNull()
		;
	}

	public function testSetDelay()
	{
		$this
			->if($generator = new testedClass())
			->then
				->object($generator->setDelay($delay = rand(1, PHP_INT_MAX), $now = new \dateTime()))->isIdenticalTo($generator)
		;
	}

	public function testAddOpening()
	{
		$this
			->if($generator = new testedClass())
			->then
				->object($generator->addOpening($firstEvent = new event()))->isIdenticalTo($generator)
				->array($generator->getOpening())->isEqualTo(array($firstEvent))
				->object($generator->addOpening($secondEvent = new event()))->isIdenticalTo($generator)
				->array($generator->getOpening())->isEqualTo(array($firstEvent, $secondEvent))
		;
	}

	public function testAddClosing()
	{
		$this
			->if($generator = new testedClass())
			->then
				->object($generator->addClosing($firstEvent = new event()))->isIdenticalTo($generator)
				->array($generator->getClosing())->isEqualTo(array($firstEvent))
				->object($generator->addClosing($secondEvent = new event()))->isIdenticalTo($generator)
				->array($generator->getClosing())->isEqualTo(array($firstEvent, $secondEvent))
		;
	}

	public function testGenerate()
	{
		$this
			->if($generator = new testedClass())
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isEqualTo(new calendar($start, $stop))
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
			->if($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, new interval())))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
			->if($generator->addClosing(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); })))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
			->if($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-03'); })))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-06'); })))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07'), new \dateTime('2012-12-02')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07'), new \dateTime('2012-12-05')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07'), new \dateTime('2012-12-06 14:00:00')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 13:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 14:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 +1 day')))->isFalse()
			->if($generator = new testedClass())
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-01'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-01'); }, new interval(new time(14), new time(18)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, new interval(new time(14), new time(18)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-03'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-03'); }, new interval(new time(14), new time(18)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-04'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-04'); }, new interval(new time(14), new time(18)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-05'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-05'); }, new interval(new time(14), new time(18)))))
			->and($generator->setDelay(120, new \dateTime('2012-10-01 00:00:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 09:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 10:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator->setDelay(120, new \dateTime('2012-12-01 07:59:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 09:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 10:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator->setDelay(120, new \dateTime('2012-12-01 08:00:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 09:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 10:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator->setDelay(120, new \dateTime('2012-12-01 09:00:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 10:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 11:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator->setDelay(120, new \dateTime('2012-12-01 10:00:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 11:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 12:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 14:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator->setDelay(120, new \dateTime('2012-12-01 10:01:00')))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07')))->isInstanceOf('thot\calendar')
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 12:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 14:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 14:01:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05 08:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07 08:00:00')))->isFalse()
			->if($generator = new testedClass())
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, new interval(new time(8), new time(12)))))
			->and($generator->setRound(30))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2012-12-01'), $stop = new \dateTime('2012-12-07'), new \dateTime('2012-12-02 10:42:05')))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01 -1 day')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-01')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 00:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 10:42:05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 11:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 12:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-02 12:01:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-03')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-04')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-05')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-06')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2012-12-07')))->isFalse()
			->if($generator = new testedClass())
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2013-02-22'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2013-02-22'); }, new interval(new time(14), new time(16)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2013-02-23'); }, new interval(new time(8), new time(12)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2013-02-23'); }, new interval(new time(14), new time(18)))))
			->and($generator->addClosing(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2013-02-23'); }, new interval(new time(10), new time(16)))))
			->and($generator->setDelay(120, $now = new \dateTime('2013-02-22 15:40:00')))
			->and($generator->setRound(60))
			->then
				->object($calendar = $generator->generate($start = new \dateTime('2013-02-01'), $stop = new \dateTime('2013-02-28'), $now))->isInstanceOf('thot\calendar')
				->object($calendar->getStart())->isEqualTo($start)
				->object($calendar->getStop())->isEqualTo($stop)
				->boolean($calendar->isAvailable($now))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 08:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 09:39:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 09:40:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 09:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 10:00:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 15:59:00')))->isFalse()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 16:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 18:00:00')))->isTrue()
				->boolean($calendar->isAvailable(new \dateTime('2013-02-23 18:01:00')))->isFalse()
		;
	}

	public function testGetNextIntervalsFromDateTime()
	{
		$this
			->if($generator = new testedClass())
			->then
				->array($generator->getNextIntervalsFromDateTime($dateTime = new \dateTime(), new \dateTime()))->isEmpty()
			->if($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, $interval = new interval(new time(8), new time(18)))))
			->and($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-03'); }, $otherInterval = new interval(new time(10), new time(16)))))
			->then
				->array($generator->getNextIntervalsFromDateTime($dateTime = new \dateTime('2012-12-01'), new \dateTime('2012-12-07')))->isEqualTo(array($interval))
				->object($dateTime)->isEqualTo(new \dateTime('2012-12-02 08:00:00'))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 00:00:00'), new \dateTime('2012-12-07')))->isEqualTo(array($interval))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 7:59:00'), new \dateTime('2012-12-07')))->isEqualTo(array($interval))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 12:00:00'), new \dateTime('2012-12-07')))->isEqualTo(array(new interval(new time(12), new time(18))))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 17:59:00'), new \dateTime('2012-12-07')))->isEqualTo(array(new interval(new time(17, 59), new time(18))))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 18:00:00'), new \dateTime('2012-12-07')))->isEqualTo(array(new interval(new time(18), new time(18))))
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 18:01:00'), new \dateTime('2012-12-07')))->isEmpty()
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-02 23:59:00'), new \dateTime('2012-12-07')))->isEmpty()
				->array($generator->getNextIntervalsFromDateTime(new \dateTime('2012-12-03 00:00:00'), new \dateTime('2012-12-07')))->isEqualTo(array($otherInterval))
		;
	}

    public function testHasClosing()
    {
        $this
            ->if($generator = new testedClass())
            ->then
                ->object($generator->addClosing(new event(function(\dateTime $dateTime) { return ($dateTime->format('Y-m-d') == '2012-12-02'); }, new interval())))->isIdenticalTo($generator)
                ->boolean($generator->hasClosing(new \dateTime('2012-12-02')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-12-02')))->isFalse()
                ->boolean($generator->hasClosing(new \dateTime('2012-10-04')))->isFalse()
        ;
    }

    public function testAddPublicHolidays()
    {
       $this
            ->if($generator = new testedClass())
            ->then
                ->exception(function() use ($generator) { $generator->generate(new \DateTime('2014-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')); })
                ->isInstanceOf('invalidArgumentException')
                ->hasMessage('Start must be less than stop')
            ->if($generator->generate(new \DateTime('2013-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')))
            ->and($generator->addPublicHolidays(new \DateTime('2013-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')))
                ->exception(function() use ($generator) { $generator->addPublicHolidays(new \DateTime('2014-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')); })
                ->isInstanceOf('invalidArgumentException')
                ->hasMessage('Start must be less than or equal to stop')
            ->if($generator = new testedClass())
            ->and($generator->generate(new \DateTime('2013-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')))
            ->and($generator->addPublicHolidays(new \DateTime('2013-01-01 00:00:00'), new \dateTime('2013-12-31 23:59:59')))
            ->then
                ->boolean($generator->hasClosing(new \dateTime('2013-01-01')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-05-01')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-05-08')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-07-14')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-08-15')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-11-01')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-11-11')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-12-25')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-03-31')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-04-01')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-05-09')))->isTrue()
                ->boolean($generator->hasClosing(new \dateTime('2013-05-20')))->isTrue()
       ;
    }
}
