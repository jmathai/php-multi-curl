<?php

use JMathai\PhpMultiCurl\EpiCurl;

class ExampleTest extends PHPUnit_Framework_TestCase {
 
    public function testExample()
    {
        $mc = EpiCurl::getInstance();
        $google = $mc->addURL('http://www.google.com'); // call google
        
        $this->assertInternalType('integer', $google->code);
    }
}