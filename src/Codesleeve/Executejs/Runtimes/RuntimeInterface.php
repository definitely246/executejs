<?php namespace Codesleeve\Executejs\Runtimes;

interface RuntimeInterface
{
	public function isAvailable();
	public function execute($string);
}