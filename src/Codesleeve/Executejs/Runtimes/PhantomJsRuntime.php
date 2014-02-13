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

		$this->compile_file(__DIR__ . '/../Support/phantomjs_runtime.js');
	}

	/**
	 * we need to make phantomjs terminate if there is no phantom.exit() called
	 */
	public function execute($source)
	{
		if ($this->hasNoExit($source))
		{
			$source .= PHP_EOL . 'phantom.exit();';
		}

		return parent::execute($source);
	}

	/**
	 * Control how a command is executed
	 * 
	 * @param  [type] $cmd [description]
	 * @return [type]      [description]
	 */
	// protected function process($command)
	// {
	// 	$buffers = array();
	// 	$resource = proc_open($cmd, $buffers, $pipes);
		
	// 	if (is_resource($resource))
	// 	{
	// 		return true;
	// 	}
	// }

	/**
	 * Simple check to see if the script has anything about 
	 * phantom.exit in it
	 */
	private function hasNoExit($source)
	{
		return strpos($source, 'phantom.exit()') === false;
	}
}