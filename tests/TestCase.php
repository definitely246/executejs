<?php namespace Codesleeve\Executejs;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Helper function to ensure that background processes finish
     * 
     * @param  file $pid
     * @param  file $results
     * @return void
     */
    protected function assertExecutionFinishes($pid)
    {
        $sanity = 5;

        while (file_exists($pid) && $sanity-- > 0)
        {
            sleep(1);
        }

        $this->assertGreaterThan(0, $sanity);
    }

    /**
     * See if the two things equal (after opening file)
     * @param  file $file
     * @param  string $match
     * @return void
     */
    protected function assertExecutionEquals($file, $match)
    {
        $this->assertExecutionHasResults($file);
        $results = file_get_contents($file); unlink($file);
        $this->assertEquals($results, $match);
    }

    /**
     * See if the results contains match
     * 
     * @param  file $file
     * @param  string $match
     * @return void
     */
    protected function assertExecutionContains($file, $match)
    {
        $this->assertExecutionHasResults($file);
        $results = file_get_contents($file); unlink($file);
        $this->assertContains($match, $results);
    }

    /**
     * Is there results file or not?
     * 
     * @param  file $file
     * @return void
     */
    protected function assertExecutionHasResults($file)
    {
        $sanity = 5;

        while (!file_exists($file) && $sanity-- > 0)
        {
            sleep(1);
        }

        $this->assertGreaterThan(0, $sanity);        
    }
}