<?php namespace Codesleeve\Executejs;

class Executejs implements Runtimes\RuntimeInterface
{
	/**
	 * Create a new executejs object with a runtime
	 * 
	 * @param RuntimeInterface $runtime
	 */
	public function __construct(Runtimes\RuntimeInterface $runtime = null)
	{
		$this->runtimes = array(
			new Runtimes\NodeRuntime
		);

		$this->runtime = $runtime ?: $this->firstAvailable();
	}

	/**
	 * Calls apply on the source
	 * 
	 * @param  [type] $source [description]
	 * @return [type]         [description]
	 */
	public function call($source)
	{
		return $this->runtime->call($source);
	}

	/**
	 * [compile description]
	 * @param  [type] $source [description]
	 * @return [type]         [description]
	 */
	public function compile($source)
	{
		return $this->runtime->compile($source);
	}

	/**
	 * Evaluate the javascript
	 * 
	 * @param  string $source
	 * @return string            
	 */
	public function evaluate($source)
	{
		return $this->runtime->evaluate($source);
	}

	/**
	 * Execute the javascript
	 * 
	 * @param  string $source
	 * @return string            
	 */
	public function execute($source)
	{
		return $this->runtime->execute($source);
	}

	/**
	 * Is there a runtime set or not?
	 * 
	 * @return boolean
	 */
	public function isAvailable()
	{
		return is_null($this->runtime);
	}

	/**
	 * Returns the first available runtime in this system
	 * 
	 * @return RuntimeInterface
	 */
	protected function firstAvailable()
	{
		foreach ($this->runtimes as $runtime)
		{
			if ($runtime->isAvailable()) {
				return $runtime;
			}
		}

		return null;
	}
}