<?php namespace Codesleeve\Executejs\Interfaces;

interface RuntimeInterface
{
	/**
	 * Call wraps the source code with an .apply to invoke
	 * the source code (as a function most likely)
	 * 
	 * @param  string $source
	 * @return string        
	 */
	public function call($source);

	/**
	 * Bring in more source code for the current context
	 * 
	 * @param  string|file $source
	 * @return $this        
	 */
	public function compile($source);

	/**
	 * Execute puts the source code in a temp file and
	 * does a php eval of the runtime against that temp file
	 * 
	 * @param  string $source
	 * @return string        
	 */
	public function execute($source);

	/**
	 * Executes the source code and return a
	 * temporary file where the results will 
	 * be output to when it is finished.
	 * 
	 * @param  $source
	 * @return $filename
	 */
	public function executeInBackground($source);

	/**
	 * Evaluate wraps the source code in an javascript eval()
	 * function and returns the results. This would be useful 
	 * if you had some data in php and you wanted to get that data
	 * into your javascript via an associative array (hash).
	 * 
	 * @param  string $source
	 * @return string        
	 */
	public function evaluate($source);

	/**
	 * Lets us know if this run time is available to use or not
	 * 
	 * @return boolean
	 */
	public function isAvailable();

}