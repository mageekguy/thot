<?php

namespace thot\tests\units\exceptions;

require __DIR__ . '/../../runner.php';

use
	atoum
;

class invalidArgument extends atoum
{
	public function testClass()
	{
		$this->testedClass
			->implements('thot\exception')
			->extends('invalidArgumentException')
		;
	}
}
