<?php

namespace thot\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	thot\time,
	thot\interval as testedClass
;

class interval extends atoum\test
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

	public function testMergeIn()
	{
		$this
			->if($interval = new testedClass())
			->then
				->array($interval->mergeIn(array()))->isEqualTo(array($interval))
				->array($interval->mergeIn(array($interval)))->isEqualTo(array($interval))
				->array($interval->mergeIn(array(new testedClass(new time(8), new time(12)))))->isEqualTo(array($interval))
			->if($interval = new testedClass(new time(8), new time(12)))
			->then
				->array($interval->mergeIn(array()))->isEqualTo(array($interval))
				->array($interval->mergeIn(array($otherInterval = new testedClass())))->isEqualTo(array($otherInterval))
				->array($interval->mergeIn(array($otherInterval = new testedClass(new time(14), new time(18)))))->isEqualTo(array($interval, $otherInterval))
				->array($interval->mergeIn(array(new testedClass(new time(10), new time(14)))))->isEqualTo(array(new testedClass(new time(8), new time(14))))
				->array($interval->mergeIn(array($previousInterval = new testedClass(new time(1), new time(5)), $nextInterval = new testedClass(new time(14)))))->isEqualTo(array($previousInterval, $interval, $nextInterval))
				->array($interval->mergeIn(array($nextInterval, $previousInterval)))->isEqualTo(array($previousInterval, $interval, $nextInterval))
		;
	}

	public function testContainsDateTime()
	{
		$this
			->if($interval = new testedClass())
			->then
				->boolean($interval->containsDateTime(new \dateTime()))->isTrue()
			->if($interval = new testedClass(new time(), new time(12)))
			->then
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 00:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:00')))->isTrue()
				->boolean($interval->containsDateTime(new \dateTime('1976-10-06 12:01')))->isFalse()
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
}
