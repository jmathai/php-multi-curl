<?php 
namespace JMathai\PhpMultiCurl;
/**
 * Manager manages multicurl handles
 *
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class Manager
{
    private $_key;
    private $_epiCurl;

    public function __construct($key)
    {
        $this->_key = $key;
        $this->_epiCurl = MultiCurl::getInstance();
    }

    public function __get($name)
    {
        $responses = $this->_epiCurl->getResult($this->_key);
        return isset($responses[$name]) ? $responses[$name] : null;
    }

    public function __isset($name)
    {
        $val = self::__get($name);
        return empty($val);
    }
}
