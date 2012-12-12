<?php

namespace thot\tests\units\calendar\generator;

require __DIR__ . '/../../../runner.php';

use
	atoum,
	thot\interval,
	thot\calendar\generator\event as testedClass
;

class event extends atoum
{
	public function testClass()
	{
		$this->testedClass->extends('thot\interval');
	}

	public function test__construct()
	{
		$this
			->if($event = new testedClass())
			->then
				->object($event(new \dateTime()))->isEqualTo(new interval())
			->if($date = new \dateTime())
			->and($event = new testedClass(function(\dateTime $dateTime) use ($date) { return $dateTime == $date; }))
			->then
				->variable($event(new \dateTime('-1 day')))->isNull()
				->object($event($date))->isEqualTo(new interval())
			->and($event = new testedClass(function(\dateTime $dateTime) use ($date) { return $dateTime == $date; }, $interval = new interval()))
			->then
				->variable($event(new \dateTime('-1 day')))->isNull()
				->object($event($date))->isIdenticalTo($interval)
		;
	}
}
