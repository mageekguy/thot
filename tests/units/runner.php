<?php

namespace time;

require __DIR__ . '/atoum/scripts/runner.php';

use
	atoum
;

$autoloader = new atoum\autoloader();
$autoloader
	->addDirectory(__NAMESPACE__, __DIR__ . '/../../classes')
	->register()
;
