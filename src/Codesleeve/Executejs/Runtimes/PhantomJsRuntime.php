<?php namespace Codesleeve\Executejs\Runtimes;

use Codesleeve\Executejs\Exceptions\ExternalRuntimeException;

class PhantomJsRuntime extends ExternalRuntime
{
	/**
	 * Construct the runtime
	 */
	public function __construct()
	{
		$executable = realpath(__DIR__ . '/../../../../bin/phantomjs');

		parent::__construct($executable);
	}

	/**
	 * We have to wrap phantom js in a special script so we know it won't error
	 * or else we might have deadlocks in our application if we get invalid source
	 * code
	 *
	 * @param  string $source
	 * @param  array $options
	 * @return string
	 */
	public function execute($source)
	{
		$error = null;

		list($wrapperSource, $scriptFile) = $this->createWrapperAndScriptFile($source);

		try { $outcome = parent::execute($wrapperSource); } catch(ExternalRuntimeException $e) { $error = $e; }

		unlink($scriptFile);

		if ($error)
		{
			throw $error;
		}

		return $outcome;
	}

	/**
	 * We have to wrap phantom js in a special script so we know it won't error
	 * or else we might have deadlocks in our application if we get invalid source
	 * code
	 *
	 * @param  string $source
	 * @param  array $options
	 * @return string
	 */
	public function executeInBackground($source)
	{
		list($wrapperSource, $scriptFile) = $this->createWrapperAndScriptFile($source);

		list($pidFile, $resultsFile) = $this->createPidAndResultsFiles();

		$source = $this->source . $this->encode($wrapperSource);

		$sourceFile = $this->createFileFromSource($source);

		$command  = "{$this->executable} {$sourceFile} > {$resultsFile} 2>&1 {$this->separator()} " . $this->remove("{$pidFile} {$scriptFile} {$sourceFile}");

		$this->processInBackground($command);

		return array($pidFile, $resultsFile);
	}

	/**
	 * Creates a wrapper file so that if the source code is invalid
	 * then phantom can still exit. If we didn't do this then anytime
	 * the $source had errors in it phantom would just hang causing
	 * everything else to hang (including this php library).
	 * 
	 * @param  string $source
	 * @return string
	 */
	private function createWrapperAndScriptFile($source)
	{
		$wrapper = file_get_contents(__DIR__ . '/../Support/phantomjs_runtime.js');

		$source = $this->ensureExitPathExists($source);

		$scriptFile = $this->createFileFromSource($source);

		return array(str_replace('{{SCRIPT_PATH}}', $scriptFile, $wrapper), $scriptFile);
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