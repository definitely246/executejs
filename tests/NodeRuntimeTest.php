<?php namespace Codesleeve\Executejs;

use PHPUnit_Framework_TestCase;

class NodeRuntimeTest extends PHPUnit_Framework_TestCase
{ 
    public function setUp()
    {
        $this->runtime = new Runtimes\NodeRuntime;
    }

    public function testSimpleCommand()
    {
        if (!$this->runtime->isAvailable()) return;

        $script = 'x = "hello world"; console.log(x)';
        $output = $this->runtime->execute($script);

        $this->assertEquals($output, 'hello world');
    }

    public function testHandlebars()
    {
       if (!$this->runtime->isAvailable()) return;

        $this->runtime->wrapInFile = false;
        $file = __DIR__ . '/handlebars/bin/handlebars ' . __DIR__ . '/handlebars/testfiles/test.jst.hbs';
        $output = $this->runtime->execute($file);

        $this->assertContains("var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};templates['test.jst.hbs']", $output);
    }
}