<?php

namespace thot;

class autoloader
{
	public function requireClass($class)
	{
		if (strpos($class, __NAMESPACE__) === 0)
		{
			$file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', ltrim(substr($class, strlen(__NAMESPACE__) + 1), '\\')) . '.php';


			if (is_file($file) === true)
			{
				require $file;
			}
		}
	}

	public static function register()
	{
    	spl_autoload_register(array(new static(), 'requireClass'));
	}
}
