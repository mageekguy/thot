<?php

namespace mageekguy\atoum;

use
	mageekguy\atoum,
	mageekguy\atoum\iterators,
	mageekguy\atoum\exceptions
;

class runner implements observable
{
	const atoumVersionConstant = 'mageekguy\atoum\version';
	const atoumDirectoryConstant = 'mageekguy\atoum\directory';

	const runStart = 'runnerStart';
	const runStop = 'runnerStop';

	protected $score = null;
	protected $adapter = null;
	protected $locale = null;
	protected $includer = null;
	protected $globIteratorFactory = null;
	protected $reflectionClassFactory = null;
	protected $observers = null;
	protected $reports = null;
	protected $testNumber = 0;
	protected $testMethodNumber = 0;
	protected $codeCoverage = true;
	protected $phpPath = null;
	protected $defaultReportTitle = null;
	protected $maxChildrenNumber = null;
	protected $bootstrapFile = null;
	protected $testDirectoryIterator = null;
	protected $debugMode = false;

	private $start = null;
	private $stop = null;

	public function __construct()
	{
		$this
			->setAdapter()
			->setLocale()
			->setIncluder()
			->setScore()
			->setTestDirectoryIterator()
			->setGlobIteratorFactory()
			->setReflectionClassFactory()
		;

		$this->observers = new \splObjectStorage();
		$this->reports = new \splObjectStorage();
	}

	public function setAdapter(adapter $adapter = null)
	{
		$this->adapter = $adapter ?: new adapter();

		return $this;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function setLocale(locale $locale = null)
	{
		$this->locale = $locale ?: new locale();

		return $this;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setIncluder(includer $includer = null)
	{
		$this->includer = $includer ?: new includer();

		return $this;
	}

	public function getIncluder()
	{
		return $this->includer;
	}

	public function setScore(runner\score $score = null)
	{
		$this->score = $score ?: new runner\score();

		return $this;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function setTestDirectoryIterator(iterators\recursives\directory\factory $iterator = null)
	{
		$this->testDirectoryIterator = $iterator ?: new iterators\recursives\directory\factory();

		return $this;
	}

	public function getTestDirectoryIterator()
	{
		return $this->testDirectoryIterator;
	}

	public function setGlobIteratorFactory(\closure $factory = null)
	{
		$this->globIteratorFactory = $factory ?: function($pattern) { return new \globIterator($pattern); };

		return $this;
	}

	public function getGlobIteratorFactory()
	{
		return $this->globIteratorFactory;
	}

	public function setReflectionClassFactory(\closure $factory = null)
	{
		$this->reflectionClassFactory = $factory ?: function($class) { return new \reflectionClass($class); };

		return $this;
	}

	public function getReflectionClassFactory()
	{
		return $this->reflectionClassFactory;
	}

	public function enableDebugMode()
	{
		$this->debugMode = true;

		return $this;
	}

	public function disableDebugMode()
	{
		$this->debugMode = false;

		return $this;
	}

	public function debugModeIsEnabled()
	{
		return $this->debugMode;
	}

	public function setMaxChildrenNumber($number)
	{
		if ($number < 1)
		{
			throw new exceptions\logic\invalidArgument('Maximum number of children must be greater or equal to 1');
		}

		$this->maxChildrenNumber = $number;

		return $this;
	}

	public function setDefaultReportTitle($title)
	{
		$this->defaultReportTitle = (string) $title;

		return $this;
	}

	public function setBootstrapFile($path)
	{
		try
		{
			$this->includer->includePath($path, function($path) { include_once($path); });
		}
		catch (atoum\includer\exception $exception)
		{
			throw new exceptions\runtime\file(sprintf($this->getLocale()->_('Unable to use bootstrap file \'%s\''), $path));
		}

		$this->bootstrapFile = $path;

		return $this;
	}

	public function getDefaultReportTitle()
	{
		return $this->defaultReportTitle;
	}

	public function getPhpPath()
	{
		if ($this->phpPath === null)
		{
			$phpPath = null;

			if ($this->adapter->defined('PHP_BINARY') === true)
			{
				$phpPath = $this->adapter->constant('PHP_BINARY');
			}

			if ($phpPath === null)
			{
				$phpPath = $this->adapter->getenv('PHP_PEAR_PHP_BIN');

				if ($phpPath === false)
				{
					$phpPath = $this->adapter->getenv('PHPBIN');

					if ($phpPath === false)
					{
						$phpDirectory = $this->adapter->constant('PHP_BINDIR');

						if ($phpDirectory === null)
						{
							throw new exceptions\runtime('Unable to find PHP executable');
						}

						$phpPath = $phpDirectory . '/php';
					}
				}
			}

			$this->setPhpPath($phpPath);
		}

		return $this->phpPath;
	}

	public function getTestNumber()
	{
		return $this->testNumber;
	}

	public function getTestMethodNumber()
	{
		return $this->testMethodNumber;
	}

	public function getObservers()
	{
		$observers = array();

		foreach ($this->observers as $observer)
		{
			$observers[] = $observer;
		}

		return $observers;
	}

	public function getBootstrapFile()
	{
		return $this->bootstrapFile;
	}

	public function getTestMethods(array $namespaces = array(), array $tags = array(), array $testMethods = array(), $testBaseClass = null)
	{
		$classes = array();

		foreach ($this->getDeclaredTestClasses($testBaseClass) as $testClass)
		{
			$test = new $testClass();

			if (static::isIgnored($test, $namespaces, $tags) === false)
			{
				$methods =  self::getMethods($test, $testMethods, $tags);

				if ($methods)
				{
					$classes[$testClass] = $methods;
				}
			}
		}

		return $classes;
	}

	public function getCoverage()
	{
		return $this->score->getCoverage();
	}

	public function setPhpPath($path)
	{
		$this->phpPath = (string) $path;

		return $this;
	}

	public function enableCodeCoverage()
	{
		$this->codeCoverage = true;

		return $this;
	}

	public function disableCodeCoverage()
	{
		$this->codeCoverage = false;

		return $this;
	}

	public function codeCoverageIsEnabled()
	{
		return $this->codeCoverage;
	}

	public function addObserver(atoum\observer $observer)
	{
		$this->observers->attach($observer);

		return $this;
	}

	public function removeObserver(atoum\observer $observer)
	{
		$this->observers->detach($observer);

		return $this;
	}

	public function callObservers($event)
	{
		foreach ($this->observers as $observer)
		{
			$observer->handleEvent($event, $this);
		}

		return $this;
	}

	public function setPathAndVersionInScore()
	{
		$this->score
			->setAtoumVersion($this->adapter->defined(static::atoumVersionConstant) === false ? null : $this->adapter->constant(static::atoumVersionConstant))
			->setAtoumPath($this->adapter->defined(static::atoumDirectoryConstant) === false ? null : $this->adapter->constant(static::atoumDirectoryConstant))
		;

		$phpPath = $this->adapter->realpath($this->getPhpPath());

		if ($phpPath === false)
		{
			throw new exceptions\runtime('Unable to find \'' . $this->getPhpPath() . '\'');
		}
		else
		{
			$descriptors = array(
				1 => array('pipe', 'w'),
				2 => array('pipe', 'w'),
			);

			$php = @$this->adapter->invoke('proc_open', array(escapeshellarg($phpPath) . ' --version', $descriptors, & $pipes));

			if ($php === false)
			{
				throw new exceptions\runtime('Unable to open \'' . $phpPath . '\'');
			}

			$phpVersion = trim($this->adapter->stream_get_contents($pipes[1]));

			$this->adapter->fclose($pipes[1]);
			$this->adapter->fclose($pipes[2]);

			$phpStatus = $this->adapter->proc_get_status($php);

			while ($phpStatus['running'] == true)
			{
				$phpStatus = $this->adapter->proc_get_status($php);
			}

			$this->adapter->proc_close($php);

			if ($phpStatus['exitcode'] > 0)
			{
				throw new exceptions\runtime('Unable to get PHP version from \'' . $phpPath . '\'');
			}

			$this->score
				->setPhpPath($phpPath)
				->setPhpVersion($phpVersion)
			;
		}

		return $this;
	}

	public function run(array $namespaces = array(), array $tags = array(), array $runTestClasses = array(), array $runTestMethods = array(), $testBaseClass = null)
	{
		$this->start = $this->adapter->microtime(true);
		$this->testNumber = 0;
		$this->testMethodNumber = 0;

		$this->score->reset();

		$this->setPathAndVersionInScore();

		if ($this->defaultReportTitle !== null)
		{
			foreach ($this->reports as $report)
			{
				if ($report->getTitle() === null)
				{
					$report->setTitle($this->defaultReportTitle);
				}
			}
		}

		$declaredTestClasses = $this->getDeclaredTestClasses($testBaseClass);

		if (sizeof($runTestClasses) <= 0)
		{
			$runTestClasses = $declaredTestClasses;
		}
		else
		{
			$runTestClasses = array_intersect($runTestClasses, $declaredTestClasses);
		}

		natsort($runTestClasses);

		$tests = array();

		foreach ($runTestClasses as $runTestClass)
		{
			$test = new $runTestClass();

			if (static::isIgnored($test, $namespaces, $tags) === false && ($methods = self::getMethods($test, $runTestMethods, $tags)))
			{
				$tests[] = array($test, $methods);

				$this->testNumber++;
				$this->testMethodNumber += sizeof($methods);
			}
		}

		$this->callObservers(self::runStart);

		if ($tests)
		{
			$phpPath = $this->getPhpPath();

			foreach ($tests as $testMethods)
			{
				list($test, $methods) = $testMethods;

				$test
					->setPhpPath($phpPath)
					->setAdapter($this->adapter)
					->setLocale($this->locale)
					->setBootstrapFile($this->bootstrapFile)
				;

				if ($this->debugMode === true)
				{
					$test->enableDebugMode();
				}

				if ($this->maxChildrenNumber !== null)
				{
					$test->setMaxChildrenNumber($this->maxChildrenNumber);
				}

				if ($this->codeCoverageIsEnabled() === false)
				{
					$test->disableCodeCoverage();
				}
				else
				{
					$test->getScore()->setCoverage($this->getCoverage());
				}

				foreach ($this->observers as $observer)
				{
					$test->addObserver($observer);
				}

				$this->score->merge($test->run($methods)->getScore());
			}
		}

		$this->stop = $this->adapter->microtime(true);

		$this->callObservers(self::runStop);

		return $this->score;
	}

	public function addTest($path)
	{
		$runner = $this;

		try
		{
			$this->includer->includePath($path, function($path) use ($runner) { include_once($path); });
		}
		catch (atoum\includer\exception $exception)
		{
			throw new exceptions\runtime\file(sprintf($this->getLocale()->_('Unable to add test file \'%s\''), $path));
		}

		return $this;
	}

	public function addTestsFromDirectory($directory)
	{
		try
		{
			$paths = array();

			foreach (new \recursiveIteratorIterator($this->testDirectoryIterator->getIterator($directory)) as $path)
			{
				$paths[] = $path;
			}
		}
		catch (\UnexpectedValueException $exception)
		{
			throw new exceptions\runtime('Unable to read test directory \'' . $directory . '\'');
		}

		natcasesort($paths);

		foreach ($paths as $path)
		{
			$this->addTest($path);
		}

		return $this;
	}

	public function addTestsFromPattern($pattern)
	{
		try
		{
			$paths = array();

			foreach (call_user_func($this->globIteratorFactory, rtrim($pattern, DIRECTORY_SEPARATOR)) as $path)
			{
				$paths[] = $path;
			}
		}
		catch (\UnexpectedValueException $exception)
		{
			throw new exceptions\runtime('Unable to read test from pattern \'' . $pattern . '\'');
		}

		natcasesort($paths);

		foreach ($paths as $path)
		{
			if ($path->isDir() === false)
			{
				$this->addTest($path);
			}
			else
			{
				$this->addTestsFromDirectory($path);
			}
		}

		return $this;
	}

	public function getRunningDuration()
	{
		return ($this->start === null || $this->stop === null ? null : $this->stop - $this->start);
	}

	public function getDeclaredTestClasses($testBaseClass = null)
	{
		$reflectionClassFactory = $this->reflectionClassFactory;
		$testBaseClass = $testBaseClass ?: __NAMESPACE__ . '\test';

		return array_filter($this->adapter->get_declared_classes(), function($class) use ($reflectionClassFactory, $testBaseClass) {
				$class = $reflectionClassFactory($class);

				return ($class->isSubClassOf($testBaseClass) === true && $class->isAbstract() === false);
			}
		);
	}

	public function addReport(atoum\report $report)
	{
		$this->reports->attach($report);

		return $this->addObserver($report);
	}

	public function removeReport(atoum\report $report)
	{
		$this->reports->detach($report);

		return $this->removeObserver($report);
	}

	public function removeReports()
	{
		foreach ($this->reports as $report)
		{
			$this->removeObserver($report);
		}

		$this->reports = new \splObjectStorage();

		return $this;
	}

	public function hasReports()
	{
		return (sizeof($this->reports) > 0);
	}

	public function getReports()
	{
		$reports = array();

		foreach ($this->reports as $report)
		{
			$reports[] = $report;
		}

		return $reports;
	}

	public static function isIgnored(test $test, array $namespaces, array $tags)
	{
		$isIgnored = $test->isIgnored();

		if ($isIgnored === false && $namespaces)
		{
			$classNamespace = strtolower($test->getClassNamespace());

			$isIgnored = sizeof(array_filter($namespaces, function($value) use ($classNamespace) { return strpos($classNamespace, strtolower($value)) === 0; })) <= 0;
		}

		if ($isIgnored === false && $tags)
		{
			$isIgnored = sizeof($testTags = $test->getAllTags()) <= 0 || sizeof(array_intersect($tags, $testTags)) == 0;
		}

		return $isIgnored;
	}

	private static function getMethods(test $test, array $runTestMethods, array $tags)
	{
		$methods = array();

		if (isset($runTestMethods['*']) === true)
		{
			$methods = $runTestMethods['*'];
		}

		$testClass = $test->getClass();

		if (isset($runTestMethods[$testClass]) === true)
		{
			$methods = $runTestMethods[$testClass];
		}

		if (in_array('*', $methods) === true)
		{
			$methods = array();
		}

		if (sizeof($methods) <= 0)
		{
			$methods = $test->getTestMethods($tags);
		}
		else
		{
			$methods = $test->getTaggedTestMethods($methods, $tags);
		}

		return $methods;
	}
}
