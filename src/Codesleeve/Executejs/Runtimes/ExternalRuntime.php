<?php namespace Codesleeve\Executejs\Runtimes;

use Codesleeve\Executejs\Exceptions\ExternalRuntimeException;

class ExternalRuntime implements RuntimeInterface
{
	/**
	 * Create a new external runtime class
	 * 
	 * @param string $context    
	 * @param string $source
	 */
	public function __construct($executable, $source = "")
	{
		$this->executable = $executable;
		$this->source = $source ? $source . PHP_EOL : "";
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
	 * @param  string $source
	 * @return $this        
	 */
	public function compile($source)
	{
		$this->source .= $source;
		return $this;
	}

	/**
	 * Compiles the source from a file name
	 * 
	 * @param  string $filename
	 * @return string          
	 */
	public function compile_file($filename)
	{
		return $this->compile(file_get_contents($filename));
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
		$source = $this->source . $this->encode($source);

		$filename = $this->createFileWrapper($source);

		$command  = escapeshellcmd(sprintf("%s %s", $this->executable, $filename));

		$output = $this->process($command);

		unlink($filename);

		return $output;			
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
		exec($command, $output, $result);

		if ($result != 0)
		{
			throw new ExternalRuntimeException("Got a return status of $result when executing $command");
		}

		return implode('', $output);
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
	protected function createFileWrapper($source)
	{
		do {
			$filename = sys_get_temp_dir() . '/execute.' . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . '.js';
		} while (file_exists($filename));

		file_put_contents($filename, $source);
		return $filename;
	}

}