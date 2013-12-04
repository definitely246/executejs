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

        $this->runtime->compile_file(__DIR__ . '/handlebars.js');

        $html = '<div>{{foo}}</div>';
        $source = "print(\"var JST = (typeof JST === 'undefined') ? {} : JST; (function() { var template = Handlebars.template, templates = JST; templates['test.jst.hbs'] = template(\" + Handlebars.precompile('$html') + \"); })();\")";
        $output = $this->runtime->execute($source);

        $this->assertContains("var JST = (typeof JST === 'undefined') ? {} : JST; (function() { var template = Handlebars.template, templates = JST; templates['test.jst.hbs']", $output);
    }

}