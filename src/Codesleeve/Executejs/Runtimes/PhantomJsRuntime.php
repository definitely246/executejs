<?php namespace Codesleeve\Executejs\Runtimes;

class PhantomJsRuntime extends ExternalRuntime
{
	/**
	 * Construct
	 */
	public function __construct()
	{
		$executable = realpath(__DIR__ . '/../../../../bin/phantomjs');

		parent::__construct($executable);
	}

	/**
	 * we need to make phantomjs terminate if there is no phantom.exit() called
	 */
	public function execute($source, $options = array())
	{
		$wrapper = file_get_contents(__DIR__ . '/../Support/phantomjs_runtime.js');

		$source = $this->ensureExitPathExists($source);

		$scriptPath = $this->createFileWrapper($source);

		$wrapper = str_replace('{{SCRIPT_PATH}}', $scriptPath, $wrapper);

		$this->async = array_key_exists('async', $options) ? $options['async'] : false;

		$outcome = parent::execute($wrapper);

		if (!$this->async)
		{
			unlink($scriptPath);
		}

		return $outcome;
	}

	/**
	 * Make sure ththe phantom script can exit somehow.
	 * 
	 * @param  [type] $source [description]
	 * @return [type]         [description]
	 */
	private function ensureExitPathExists($source)
	{
		if (strpos($source, 'phantom.exit()') === false)
		{
			$source .= PHP_EOL . 'phantom.exit();';
		}

		return $source;		
	}

}