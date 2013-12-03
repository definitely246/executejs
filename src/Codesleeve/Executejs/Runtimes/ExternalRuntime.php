<?php namespace Codesleeve\Executejs\Runtimes;

use Codesleeve\Executejs\Exceptions\ExternalRuntimeException;

class ExternalRuntime implements RuntimeInterface
{
	/**
	 * Create a new external runtime class
	 * 
	 * @param string $name    
	 * @param string $command
	 */
	public function __construct($name, $command)
	{
		$this->name = $name;
		$this->command = $command;
		$this->wrapInFile = true;
	}

	/**
	 * Checks to see if this external runtime is available
	 * 
	 * @return boolean
	 */
	public function isAvailable()
	{
		try {
			$output = $this->execute("console.log('YES')");
		} catch (ExternalRuntimeException $e) {
			return false;
		}

		return $output == 'YES';
	}

	/**
	 * Run this javascript against the external command
	 * we have passed into the command line
	 * 
	 * @param  string $param
	 * @return string
	 */
	public function execute($param)
	{
		if ($this->wrapInFile) {
			$param = $this->getFileWrapper($param);
		}

		$result = 0; $outputs = array();
		exec($this->command . " $param", $outputs, $result);

		if ($this->wrapInFile) {
			unlink($param);
		}

		if ($result == 0) {
			return implode('', $outputs);
		}

		throw new ExternalRuntimeException("Got result $result when trying to execute $param");
	}

	/**
	 * Execute in file
	 * 
	 * @param  string $javascript
	 * @return string            
	 */
	protected function getFileWrapper($javascript)
	{
		$filename = $this->getRandomFilename();
		file_put_contents($filename, $javascript);
		return $filename;
	}

	/**
	 * Gets a random temporary filename
	 * 
	 * @return string
	 */
	protected function getRandomFilename()
	{
		$filename = tempnam(sys_get_temp_dir(), 'executejs') . '.js';
		return file_exists($filename) ? $this->getRandomFilename() : $filename;
	}
}