<?php

namespace time\tests\units\exceptions;

require __DIR__ . '/../../runner.php';

use
	atoum
;

class invalidArgument extends atoum\test
{
	public function testClass()
	{
		$this->testedClass
			->implements('time\exception')
			->extends('invalidArgumentException')
		;
	}
}
