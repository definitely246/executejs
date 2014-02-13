<?php namespace Codesleeve\Executejs;

use PHPUnit_Framework_TestCase;

class PhantomJsRuntimeTest extends PHPUnit_Framework_TestCase
{ 
    public function setUp()
    {
        $this->runtime = new Runtimes\PhantomJsRuntime;
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

        $this->assertEquals($output, "hello world\n");
    }

    public function testHandlebars()
    {
        $source = file_get_contents(__DIR__ . '/files/source2.js');
        $output = $this->runtime->execute($source);

        $this->assertContains("templates['test.jst.hbs']", $output);
    }

    /**
     * @expectedException Codesleeve\Executejs\Exceptions\ExternalRuntimeException
     */
    public function testInvalidSourceCode()
    {
        $source = file_get_contents(__DIR__ . '/files/source3.js');
        $output = $this->runtime->execute($source);
    }

    // write a test to ensure that phantom.js has phantom.exit() written to 
    // javascript files that don't contain that

    // also write a test using rasterize or something as an example to see
    // that it works...
}