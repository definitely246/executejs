<?php namespace Codesleeve\Executejs;

class Executejs
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
	 * Execute the javascript
	 * 
	 * @param  string $javascript
	 * @return string            
	 */
	public function execute($javascript)
	{
		return $this->runtime->execute($javascript);
	}

	/**
	 * Returns the first available runtime in this system
	 * 
	 * @return RuntimeInterface
	 */
	public function firstAvailable()
	{
		foreach ($this->runtimes as $runtime)
		{
			if ($runtime->isAvailable()) {
				return $runtime;
			}
		}

		throw new Exceptions\NoAvailableRuntime('No available runtime found');
	}
}