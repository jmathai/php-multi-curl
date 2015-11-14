<?php use JMathai\PhpMultiCurl\MultiCurl;

class MultiCurlTest extends PHPUnit_Framework_TestCase
{
  public function testConstructorThrowsExceptionWhenInvokedWithNew()
  {
    MultiCurl::$singleton = 0;
    $this->setExpectedException('JMathai\PhpMultiCurl\MultiCurlException');
    new MultiCurl();
  }

  public function testConstructorSucceedsAsSingleton()
  {
    MultiCurl::getInstance();
  }

  public function testAddUrl()
  {
    $mc = MultiCurl::getInstance();
    $res = $mc->addUrl('http://google.com');
    $this->assertInstanceOf('Jmathai\PhpMultiCurl\MultiCurlManager', $res);
  }
}

