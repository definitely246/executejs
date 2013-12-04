<?php namespace Codesleeve\Executejs;

use PHPUnit_Framework_TestCase;

class NodeRuntimeTest extends PHPUnit_Framework_TestCase
{ 
    public function setUp()
    {
        $this->runtime = new Runtimes\NodeRuntime;
    }



    public function testCall()
    {

    }

    public function testCompile()
    {

    }

    public function testCompileFile()
    {

    }

    public function testEvaluate()
    {

    }

    public function testIsAvailable()
    {

    }

    public function testExecute()
    {
        $source = file_get_contents(__DIR__ . '/files/source1.js');
        $output = $this->runtime->execute($source);

        $this->assertEquals($output, 'hello world');
    }

    public function testHandlebars()
    {
        $source = file_get_contents(__DIR__ . '/files/source2.js');
        $output = $this->runtime->execute($source);

        $this->assertContains("templates['test.jst.hbs']", $output);
    }

}