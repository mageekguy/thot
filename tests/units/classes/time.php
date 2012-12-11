<?php

namespace time\tests\units;

require __DIR__ . '/../runner.php';

use
	atoum,
	time\time as testedClass
;

class time extends atoum\test
{
	public function test__construct()
	{
		$this
			->if($time = new testedClass())
			->then
				->integer($time->getHour())->isZero()
				->integer($time->getMinute())->isZero()
			->if($time = new testedClass($hour = rand(1, 23), $minute = rand(1, 59)))
			->then
				->integer($time->getHour())->isEqualTo($hour)
				->integer($time->getMinute())->isEqualTo($minute)
			->exception(function() { new testedClass(24); })
				->isInstanceOf('time\exceptions\invalidArgument')
				->hasMessage('Hour must be between 0 and 23')
			->exception(function() { new testedClass(rand(1, 23), 60); })
				->isInstanceOf('time\exceptions\invalidArgument')
				->hasMessage('Minute must be between 0 and 59')
		;
	}

	public function testSetHour()
	{
		$this
			->if($time = new testedClass())
			->then
				->object($time->setHour($hour = rand(1, 23)))->isIdenticalTo($time)
				->integer($time->getHour())->isEqualTo($hour)
			->exception(function() use ($time) { $time->setHour(24); })
				->isInstanceOf('time\exceptions\invalidArgument')
				->hasMessage('Hour must be between 0 and 23')
		;
	}

	public function testGetMinute()
	{
		$this
			->if($time = new testedClass())
			->then
				->object($time->setMinute($minute = rand(1, 59)))->isIdenticalTo($time)
				->integer($time->getMinute())->isEqualTo($minute)
			->exception(function() use ($time) { $time->setMinute(60); })
				->isInstanceOf('time\exceptions\invalidArgument')
				->hasMessage('Minute must be between 0 and 59')
		;
	}

	public function testToMinutes()
	{
		$this
			->if($time = new testedClass())
			->then
				->integer($time->toMinutes())->isZero()
			->if($time = new testedClass(0, 1))
			->then
				->integer($time->toMinutes())->isEqualTo(1)
			->if($time = new testedClass(1, 1))
			->then
				->integer($time->toMinutes())->isEqualTo(61)
		;
	}

	public function testIsGreaterThan()
	{
		$this
			->if($time = new testedClass())
			->then
				->boolean($time->isGreaterThan($time))->isFalse()
			->if($otherTime = new testedClass(0, 1))
			->then
				->boolean($time->isGreaterThan($otherTime))->isFalse()
				->boolean($otherTime->isGreaterThan($time))->isTrue()
			->if($anotherTime = new testedClass(1, 0))
			->then
				->boolean($time->isGreaterThan($anotherTime))->isFalse()
				->boolean($anotherTime->isGreaterThan($time))->isTrue()
		;
	}

	public function testIsGreaterThanOrEqualTo()
	{
		$this
			->if($time = new testedClass())
			->then
				->boolean($time->isGreaterThanOrEqualTo($time))->isTrue()
			->if($otherTime = new testedClass(0, 1))
			->then
				->boolean($time->isGreaterThanOrEqualTo($otherTime))->isFalse()
				->boolean($otherTime->isGreaterThanOrEqualTo($time))->isTrue()
			->if($anotherTime = new testedClass(1, 0))
			->then
				->boolean($time->isGreaterThanOrEqualTo($anotherTime))->isFalse()
				->boolean($anotherTime->isGreaterThanOrEqualTo($time))->isTrue()
		;
	}

	public function testIsLessThan()
	{
		$this
			->if($time = new testedClass())
			->then
				->boolean($time->isLessThan($time))->isFalse()
			->if($otherTime = new testedClass(0, 1))
			->then
				->boolean($time->isLessThan($otherTime))->isTrue()
				->boolean($otherTime->isLessThan($time))->isFalse()
			->if($anotherTime = new testedClass(1, 0))
			->then
				->boolean($time->isLessThan($anotherTime))->isTrue()
				->boolean($anotherTime->isLessThan($time))->isFalse()
		;
	}

	public function testIsLessThanOrEqualTo()
	{
		$this
			->if($time = new testedClass())
			->then
				->boolean($time->isLessThanOrEqualTo($time))->isTrue()
			->if($otherTime = new testedClass(0, 1))
			->then
				->boolean($time->isLessThanOrEqualTo($otherTime))->isTrue()
				->boolean($otherTime->isLessThanOrEqualTo($time))->isFalse()
			->if($anotherTime = new testedClass(1, 0))
			->then
				->boolean($time->isLessThanOrEqualTo($anotherTime))->isTrue()
				->boolean($anotherTime->isLessThanOrEqualTo($time))->isFalse()
		;
	}

	public function testAddMinutes()
	{
		$this
			->if($time = new testedClass())
			->then
				->object($time->addMinutes(0))->isCloneOf($time)
				->object($time->addMinutes(1))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(0, 1))
				->object($time->addMinutes(60))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(1, 0))
				->object($time->addMinutes(61))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(1, 1))
				->object($time->addMinutes(1439))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(23, 59))
				->object($time->addMinutes(1440))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass())
				->object($time->addMinutes(-1))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(0, 1))
		;
	}

	public function testDiff()
	{
		$this
			->if($time = new testedClass())
			->then
				->integer($time->diff($time))->isZero()
			->if($otherTime = new testedClass(0, 1))
			->then
				->integer($time->diff($otherTime))->isEqualTo(-1)
				->integer($otherTime->diff($time))->isEqualTo(1)
			->if($otherTime = new testedClass(1, 1))
			->then
				->integer($time->diff($otherTime))->isEqualTo(-61)
				->integer($otherTime->diff($time))->isEqualTo(61)
		;
	}

	public function testSubstractMinutes()
	{
		$this
			->if($time = new testedClass())
			->then
				->object($time->substractMinutes(0))->isCloneOf($time)
				->object($time->substractMinutes(1))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(23, 59))
				->object($time->substractMinutes(60))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(23, 0))
				->object($time->substractMinutes(61))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(22, 59))
				->object($time->substractMinutes(1439))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(0, 1))
				->object($time->substractMinutes(1440))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass())
				->object($time->substractMinutes(-1))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(23, 59))
			->if($time = new testedClass(23, 59))
			->then
				->object($time->substractMinutes(0))->isCloneOf($time)
				->object($time->substractMinutes(1))
					->isNotIdenticalTo($time)
					->isEqualTo(new testedClass(23, 58))
		;
	}

	public function testGetFromDateTime()
	{
		$this
			->object(testedClass::getFromDateTime($date = new \DateTime()))->isEqualTo(new testedClass($date->format('G'), $date->format('i')))
		;
	}
}
