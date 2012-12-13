<?php

namespace thot\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	thot\time,
	thot\interval as testedClass
;

class interval extends atoum
{
	public function test__construct()
	{
		$this
			->if($interval = new testedClass())
			->then
				->object($interval->getStart())->isEqualTo(new time())
				->object($interval->getStop())->isEqualTo(new time(23, 59))
			->if($interval = new testedClass($start = new time(), $stop = new time(23, 59)))
			->then
				->object($interval->getStart())->isIdenticalTo($start)
				->object($interval->getStop())->isIdenticalTo($stop)
			->exception(function() { new testedClass(new time(23, 59), new time()); })
				->isInstanceOf('thot\exceptions\invalidArgument')
				->hasMessage('Start must be less than stop')
		;
	}

	public function testSetStart()
	{
		$this
			->if($interval = new testedClass())
			->then
				->object($interval->setStart($start = new time(8)))->isIdenticalTo($interval)
				->object($interval->getStart())->isIdenticalTo($start)
			->if($interval = new testedClass(null, new time(12)))
			->then
				->exception(function() use ($interval) { $interval->setStart(new time(12, 1)); })
					->isInstanceOf('thot\exceptions\invalidArgument')
					->hasMessage('Start must be less than stop')
		;
	}

	public function testSetStop()
	{
		$this
			->if($interval = new testedClass())
			->then
				->object($interval->setStop($stop = new time(8)))->isIdenticalTo($interval)
				->object($interval->getStop())->isIdenticalTo($stop)
			->if($interval = new testedClass(new time(12)))
			->then
				->exception(function() use ($interval) { $interval->setStop(new time(11, 59)); })
					->isInstanceOf('thot\exceptions\invalidArgument')
					->hasMessage('Stop must be greater than start')
		;
	}

	public function testAdd()
	{
		$this
			->if($interval = new testedClass())
			->then
				->object($interval->add($interval))->isCloneOf($interval)
			->if($otherInterval = new testedClass(new time(8), new time(18)))
			->then
				->object($interval->add($otherInterval))->isCloneOf($interval)
				->object($otherInterval->add($interval))->isCloneOf($interval)
			->if($interval = new testedClass($start = new time(8), $stop = new time(14)))
			->and($otherInterval = new testedClass($otherStart = new time(12), $otherStop = new time(18)))
			->then
				->object($interval->add($otherInterval))->isEqualTo(new testedClass($start, $otherStop))
			->if($interval = new testedClass($start = new time(12), $stop = new time(18)))
			->and($otherInterval = new testedClass($otherStart = new time(8), $otherStop = new time(14)))
			->then
				->object($interval->add($otherInterval))->isEqualTo(new testedClass($otherStart, $stop))
			->if($interval = new testedClass($start = new time(8), $stop = new time(12)))
			->and($otherInterval = new testedClass($otherStart = new time(14), $otherStop = new time(18)))
			->then
				->object($interval->add($otherInterval))->isCloneOf($interval)
			->if($interval = new testedClass($start = new time(8), $stop = new time(13)))
			->and($otherInterval = new testedClass($otherStart = new time(13), $otherStop = new time(18)))
			->then
				->object($interval->add($otherInterval))->isEqualTo(new testedClass($start, $otherStop))
		;
	}

	public function testSubstract()
	{
		$this
			->if($interval = new testedClass())
			->then
				->array($interval->substract($interval))->isEmpty()
			->if($otherInterval = new testedClass(new time(), new time(12)))
			->then
				->array($interval->substract($otherInterval))->isEqualTo(array(new testedClass(new time(12, 1), new time(23, 59))))
			->if($otherInterval = new testedClass(new time(12), new time(23, 59)))
			->then
				->array($interval->substract($otherInterval))->isEqualTo(array(new testedClass(new time(), new time(11, 59))))
			->if($otherInterval = new testedClass(new time(8), new time(12)))
			->then
				->array($interval->substract($otherInterval))->isEqualTo(array(new testedClass(new time(0), new time(7, 59)), new testedClass(new time(12, 1), new time(23, 59))))
			->if($interval = new testedClass(new time(8), new time(12)))
			->then
				->array($interval->substract(new testedClass(new time(10), new time(14))))->isEqualTo(array(new testedClass(new time(8), new time(9, 59))))
			->if($interval = new testedClass(new time(10), new time(14)))
			->then
				->array($interval->substract(new testedClass(new time(8), new time(12))))->isEqualTo(array(new testedClass(new time(12, 1), new time(14))))
		;
	}

	public function testIntersect()
	{
		$this
			->if($interval = new testedClass())
			->then
				->object($interval->intersect($interval))->isCloneOf($interval)
				->object($interval->intersect($otherInterval = new testedClass(new time(0), new time(12))))->isCloneOf($otherInterval)
				->object($interval->intersect($otherInterval = new testedClass(new time(10), new time(16))))->isCloneOf($otherInterval)
				->object($interval->intersect($otherInterval = new testedClass(new time(12), new time(18))))->isCloneOf($otherInterval)
			->if($interval = new testedClass(new time(0), new time(12)))
			->then
				->object($interval->intersect(new testedClass()))->isCloneOf($interval)
			->if($interval = new testedClass(new time(10), new time(16)))
			->then
				->object($interval->intersect(new testedClass()))->isCloneOf($interval)
			->if($interval = new testedClass(new time(12), new time(18)))
			->then
				->object($interval->intersect(new testedClass()))->isCloneOf($interval)
			->if($interval = new testedClass(new time(10), new time(14)))
			->then
				->object($interval->intersect(new testedClass(new time(12), new time(16))))->isEqualTo(new testedClass(new time(12), new time(14)))
			->if($interval = new testedClass(new time(12), new time(16)))
			->then
				->object($interval->intersect(new testedClass(new time(10), new time(14))))->isEqualTo(new testedClass(new time(12), new time(14)))
			->if($interval = new testedClass(new time(10), new time(12)))
			->then
				->variable($interval->intersect(new testedClass(new time(12), new time(14))))->isNull()
			->if($interval = new testedClass(new time(10), new time(12)))
			->then
				->variable($interval->intersect(new testedClass(new time(14), new time(16))))->isNull()
		;
	}

	public function testAddTo()
	{
		$this
			->if($interval = new testedClass())
			->then
				->array($interval->addTo(array()))->isEqualTo(array($interval))
				->array($interval->addTo(array($interval)))->isEqualTo(array($interval))
				->array($interval->addTo(array(new testedClass(new time(8), new time(12)))))->isEqualTo(array($interval))
			->if($interval = new testedClass(new time(8), new time(12)))
			->then
				->array($interval->addTo(array()))->isEqualTo(array($interval))
				->array($interval->addTo(array($otherInterval = new testedClass())))->isEqualTo(array($otherInterval))
				->array($interval->addTo(array($otherInterval = new testedClass(new time(14), new time(18)))))->isEqualTo(array($interval, $otherInterval))
				->array($interval->addTo(array(new testedClass(new time(10), new time(14)))))->isEqualTo(array(new testedClass(new time(8), new time(14))))
				->array($interval->addTo(array($previousInterval = new testedClass(new time(1), new time(5)), $nextInterval = new testedClass(new time(14)))))->isEqualTo(array($previousInterval, $interval, $nextInterval))
				->array($interval->addTo(array($nextInterval, $previousInterval)))->isEqualTo(array($previousInterval, $interval, $nextInterval))
		;
	}

	public function testSubstractFrom()
	{
		$this
			->if($interval = new testedClass())
			->then
				->array($interval->substractFrom(array()))->isEmpty()
				->array($interval->substractFrom(array($interval)))->isEmpty()
				->array($interval->substractFrom(array(new testedClass(new time(8), new time(12)))))->isEmpty()
			->if($interval = new testedClass(new time(8), new time(12)))
			->then
				->array($interval->substractFrom(array()))->isEmpty()
				->array($interval->substractFrom(array($otherInterval = new testedClass())))->isEqualTo(array(new testedClass(new time(), new time(7, 59)), new testedClass(new time(12, 1), new time(23, 59))))
				->array($interval->substractFrom(array($otherInterval = new testedClass(new time(14), new time(18)))))->isEqualTo(array($otherInterval))
				->array($interval->substractFrom(array(new testedClass(new time(10), new time(14)))))->isEqualTo(array(new testedClass(new time(12, 1), new time(14))))
				->array($interval->substractFrom(array($previousInterval = new testedClass(new time(1), new time(5)), $nextInterval = new testedClass(new time(14)))))->isEqualTo(array($previousInterval, $nextInterval))
				->array($interval->substractFrom(array($nextInterval, $previousInterval)))->isEqualTo(array($previousInterval, $nextInterval))
		;
	}

	public function testContainsDateTime()
	{
		$this
			->if($interval = new testedClass())
			->then
				->boolean($interval->containsDateTime(new \dateTime()))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 00:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:01')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 23:59')))->isTrue()
			->if($interval = new testedClass(new time(8), new time(12)))
			->then
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 00:00')))->isFalse()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 07:59')))->isFalse()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 08:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:01')))->isFalse()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 23:59')))->isFalse()
		;
	}

	public function testGetMinutesToStop()
	{
		$this
			->if($interval = new testedClass())
			->then
				->integer($interval->getMinutesToStop(new \dateTime('1976-10-06 00:00')))->isEqualTo(1439)
				->integer($interval->getMinutesToStop(new \dateTime('1976-10-06 23:59')))->isZero()
				->integer($interval->getMinutesToStop(new \dateTime('1976-10-06 12:00')))->isEqualTo(719)
			->if($interval = new testedClass(new time(0), new time(12)))
			->then
				->integer($interval->getMinutesToStop(new \dateTime('1976-10-06 00:00')))->isEqualTo(720)
				->integer($interval->getMinutesToStop(new \dateTime('1976-10-06 12:00')))->isZero()
				->variable($interval->getMinutesToStop(new \dateTime('1976-10-06 23:59')))->isNull()
		;
	}

	public function testIsBeforeDateTime()
	{
		$this
			->if($interval = new testedClass())
			->then
				->boolean($interval->isBeforeDateTime(new \dateTime()))->isFalse()
			->if($interval = new testedClass(new time(10), new time(14)))
			->then
				->boolean($interval->isBeforeDateTime(new \dateTime('2012-12-01 09:59:00')))->isFalse()
				->boolean($interval->isBeforeDateTime(new \dateTime('2012-12-01 10:00:00')))->isFalse()
				->boolean($interval->isBeforeDateTime(new \dateTime('2012-12-01 14:00:00')))->isFalse()
				->boolean($interval->isBeforeDateTime(new \dateTime('2012-12-01 14:01:00')))->isTrue()
		;
	}
}
