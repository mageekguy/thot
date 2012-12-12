<?php

namespace thot\tests\units\calendar;

require __DIR__ . '/../../runner.php';

use
	atoum,
	thot\time,
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
			->if($generator->addOpening(new event(function(\dateTime $dateTime) { return ($dateTime == new \dateTime('2012-12-02')); })))
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
			->if($generator->addClosing(new event(function(\dateTime $dateTime) { return ($dateTime == new \dateTime('2012-12-02')); })))
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
		;
	}
}
