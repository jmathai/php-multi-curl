<?php use JMathai\PhpMultiCurl\MultiCurl;

class AsynchronousTest extends PHPUnit_Framework_TestCase
{
  public function testAsynchronousCalls()
  {
    $ch1 = curl_init('http://slowapi.herokuapp.com/delay/2.0');
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    $ch2 = curl_init('http://slowapi.herokuapp.com/delay/2.0');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

    MultiCurl::$singleton = 0;
    $mc = MultiCurl::getInstance();
    $res1 = $mc->addCurl($ch1);
    $res2 = $mc->addCurl($ch2);

    $sequenceGraph = $mc->getSequence()->renderAscii();
    $this->assertEquals(2, substr_count($sequenceGraph, '=]'));
    $this->assertEquals(2, substr_count($sequenceGraph, '[='));
  }
}
