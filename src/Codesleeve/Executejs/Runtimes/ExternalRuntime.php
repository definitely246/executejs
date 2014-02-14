<?php namespace Codesleeve\Executejs\Runtimes;

use Codesleeve\Executejs\Interfaces\RuntimeInterface;
use Codesleeve\Executejs\Exceptions\ExternalRuntimeException;

class ExternalRuntime implements RuntimeInterface
{
	/**
	 * Allows us to turn on a debug mode if we are trying to troubleshoot
	 * a runtime. This basically just overrides output in remove() function
	 * 
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * The binary file that runs the javascript 
	 * source code
	 * 
	 * @var file
	 */
	protected $executable;

	/**
	 * Prepends this source code to every script run by
	 * this executable runtime
	 * 
	 * @var string
	 */
	protected $source;

	/**
	 * This is the place where we store temporary scripts
	 * that are run by the executable
	 * 
	 * @var directory
	 */
	protected $storageDirectory;

	/**
	 * Some things execute differently on a windows environment
	 * 
	 * @var boolean
	 */
	protected $isWindowsEnvironment = false;

	/**
	 * Create a new external runtime class
	 * 
	 * @param string $context    
	 * @param string $source
	 * @param string $storageDirectory
	 */
	public function __construct($executable, $source = "", $storageDirectory = null)
	{
		$this->executable = $executable;
		$this->source = $source ? $source . PHP_EOL : "";
		$this->storageDirectory = $storageDirectory ? $storageDirectory : sys_get_temp_dir();
		$this->isWindowsEnvironment = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Calls apply on the source code
	 * 
	 * @param  string $source
	 * @return string        
	 */
	public function call($source, array $args = array(), $context = null)
	{
		$context = is_null($context) ? "this" : json_encode($context);
		$args = json_encode($args);

		print $source . '.apply('. $context . ', ' . $args . ')';
	}

	/**
	 * Add in additional source code to our existing runtime
	 * 
	 * @param  string|file $source
	 * @return $this        
	 */
	public function compile($source)
	{
		if (file_exists($source))
		{
			$source = file_get_contents($source);
		}

		$this->source .= $source;
		return $this;
	}

	/**
	 * Evaluate the source code
	 * 
	 * @param  string $source
	 * @return string        
	 */
	public function evaluate($source)
	{
		return $this->execute('return eval(' . $source . ');');
	}

	/**
	 * Run this javascript against the external command
	 * we have passed into the command line
	 * 
	 * @param  string $source
	 * @return string
	 */
	public function execute($source)
	{
		$error = null;

		$source = $this->source . $this->encode($source);

		$sourceFile = $this->createFileFromSource($source);

		$command  = "{$this->executable} $sourceFile";

		try { $outcome = $this->process($command); } catch (ExternalRuntimeException $e) { $error = $e; }

		unlink($sourceFile);

		if ($error)
		{
			throw $error;
		}

		return $outcome;
	}

	/**
	 * Run this javascript in background process and return
	 * the file which output will be sent to when the process
	 * finishes.
	 * 
	 * @param  string $source
	 * @return array($pidfile, $outputfile)
	 */
	public function executeInBackground($source)
	{
		$source = $this->source . $this->encode($source);

		$sourceFile = $this->createFileFromSource($source);

		list($pidFile, $resultsFile) = $this->createPidAndResultsFiles();

		$command = "{$this->executable} {$sourceFile} > {$resultsFile} 2>&1 {$this->separator()} " . $this->remove("{$sourceFile} {$pidFile}");

		$this->processInBackground($command);

		return array($pidFile, $resultsFile);
	}

	/**
	 * Checks to see if this external runtime is available
	 * 
	 * @return boolean
	 */
	public function isAvailable()
	{
		try {
			$output = $this->execute('x = 3;');
		} catch (ExternalRuntimeException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Encode the source code so it is okay? Not sure what I'm doing here
	 * 
	 * @param  string $source
	 * @return string        
	 */
	protected function encode($source)
	{
		return $source;
	}

	/**
	 * Creates a new temp file for us to use
	 * 
	 * @param  string $source
	 * @return string            
	 */
	protected function createFileFromSource($source)
	{
		$filename = $this->createFileFromPrefixAndExtension('executejs', 'js');
		file_put_contents($filename, $source);
		return $filename;
	}

	/**
	 * Creates a pid and results file for that pid
	 * 
	 * @return 
	 */
	protected function createPidAndResultsFiles()
	{
		$now = new \DateTime("now", new \DateTimeZone('UTC'));
		$now = $now->format("m-d-Y_H-i-s");

		$pid = $this->randomString();
		$pidFile = "{$this->storageDirectory}/executejs.{$pid}.pid";
		$resultsFile = "{$this->storageDirectory}/executejs.{$pid}.{$now}.result.txt";

		return array($pidFile, $resultsFile);
	}

	/**
	 * Create a temporary file from a given prefix and extension
	 * 
	 * @param  string $prefix
	 * @param  string $extension
	 * @return string
	 */
	protected function createFileFromPrefixAndExtension($prefix, $extension)
	{
		do {
			$filename = $this->createRandomFilename($prefix, $extension);
		} while (file_exists($filename));

		return $filename;
	}

	/**
	 * Creates a random file name with given prefix and extension
	 * 
	 * @param  string $prefix
	 * @param  string $extension
	 * @return string
	 */
	protected function createRandomFilename($prefix, $extension)
	{
		return "{$this->storageDirectory}/{$prefix}.{$this->randomString()}.{$extension}";
	}

	/**
	 * Return a random string of characters. Useful for creating
	 * random filenames.
	 * 
	 * @param  integer $length
	 * @param  string  $pool
	 * @return random string
	 */
	protected function randomString($length = 16, $pool = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
	{
		return substr(str_shuffle($pool), 0, $length);
	}

	/**
	 * Returns the command in order to remove a file. This can
	 * differ based on operating system so let's make it work for
	 * linux and then windows later.
	 * 
	 * @param  file $filename
	 * @return string
	 */
	protected function remove($filename)
	{
		if ($this->debug)
		{
			return "echo {$filename}";
		}

		if ($this->isWindowsEnvironment)
		{
			return "DEL /F /S /Q /A {$filename}";
		}

		return "rm -f {$filename}";
	}

	/**
	 * On windows a command line seperator is & but on linux it is a ;
	 * 
	 * @return string
	 */
	protected function separator()
	{
		if ($this->isWindowsEnvironment)
		{
			return "&";
		}

		return ";";
	}

	/**
	 * Process the command given by calling executable.
	 * This can be overridden as well if something different is needed here
	 * 
	 * @param  string $command
	 * @return string
	 */
	protected function process($command)
	{
		$buffers = array(0 => array('pipe', 'r'), 1 => array("pipe", "w"), 2 => array('pipe', 'w'));
		$process = proc_open($command, $buffers, $pipes);

		if (!is_resource($process))
		{
			throw new ExternalRuntimeException("Could not execute $command");
		}

		fclose($pipes[0]);
		$output = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$result = proc_close($process);

		if ($result != 0)
		{
			throw new ExternalRuntimeException("Got $result when executing $command");
		}

		return $output;
	}

	/**
	 * Execute process in the background. If we do
	 * this then there is no way to examine what the output is
	 * when the process finishes. This is good if you want to
	 * do something like start a server or something that will
	 * take a long time to finish execution.
	 * 
	 * @param  [type] $command [description]
	 * @return [type]          [description]
	 */
	protected function processInBackground($command)
	{
		$buffers = array();
		$process = proc_open($command, $buffers, $pipes);

		if (!is_resource($process))
		{
			throw new ExternalRuntimeException("Could not execute $command");
		}

		return $process;
	}
}