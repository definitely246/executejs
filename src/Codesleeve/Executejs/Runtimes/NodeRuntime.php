<?php namespace Codesleeve\Executejs\Runtimes;

class NodeRuntime extends ExternalRuntime
{
	public function __construct()
	{
		parent::__construct('Node Runtime', 'node');
	}
}