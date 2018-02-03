<?php 
namespace JMathai\PhpMultiCurl;

use JMathai\PhpMultiCurl\Manager;
use JMathai\PhpMultiCurl\Sequence;
use JMathai\PhpMultiCurl\MultiException;
use JMathai\PhpMultiCurl\MultiInvalidParameterException;

/**
 * MultiCurl multicurl http client
 *
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class MultiCurl
{
    const TIMEOUT = 3;
    private static $_inst = null;
    /* @TODO make this private and add a method to set it to 0 */
    public static $singleton = 0;

    private $_mc;
    private $_running;
    private $_execStatus;
    private $_sleepIncrement = 1.1;
    private $_requests = array();
    private $_responses = array();
    private $_properties = array();
    private static $_timers = array();

    public function __construct()
    {
        if (self::$singleton === 0) {
            throw new MultiException('This class cannot be instantiated by the new keyword.  You must instantiate it using: $obj = MultiCurl::getInstance();');
        }

        $this->_mc = curl_multi_init();
        $this->_properties = array(
        'code'  => CURLINFO_HTTP_CODE,
        'time'  => CURLINFO_TOTAL_TIME,
        'length'=> CURLINFO_CONTENT_LENGTH_DOWNLOAD,
        'type'  => CURLINFO_CONTENT_TYPE,
        'url'   => CURLINFO_EFFECTIVE_URL
        );
    }
  
    public function reset()
    {
        $this->_requests = array();
        $this->_responses = array();
        self::$_timers = array();
    }

    public function addUrl($url, $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        foreach ($options as $option=>$value) {
            curl_setopt($ch, $option, $value);
        }
        return $this->addCurl($ch);
    }

    public function addCurl($ch)
    {
        if (gettype($ch) !== 'resource') {
            throw new MultiInvalidParameterException('Parameter must be a valid curl handle');
        }

        $key = $this->_getKey($ch);
        $this->_requests[$key] = $ch;
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, '_headerCallback'));

        $code = curl_multi_add_handle($this->_mc, $ch);
        $this->_startTimer($key);
    
        // (1)
        if ($code === CURLM_OK || $code === CURLM_CALL_MULTI_PERFORM) {
            do {
                $this->_execStatus = curl_multi_exec($this->_mc, $this->_running);
            } while ($this->_execStatus === CURLM_CALL_MULTI_PERFORM);

            return new Manager($key);
        } else {
            return $code;
        }
    }

    public function getResult($key = null)
    {
        if ($key != null) {
            if (isset($this->_responses[$key]['code'])) {
                return $this->_responses[$key];
            }

            $innerSleepInt = $outerSleepInt = 1;
            while ($this->_running && ($this->_execStatus == CURLM_OK || $this->_execStatus == CURLM_CALL_MULTI_PERFORM)) {
                usleep(intval($outerSleepInt));
                $outerSleepInt = intval(max(1, ($outerSleepInt*$this->_sleepIncrement)));
                $ms=curl_multi_select($this->_mc, 0);

                // bug in PHP 5.3.18+ where curl_multi_select can return -1
                // https://bugs.php.net/bug.php?id=63411
                if ($ms === -1) {
                    usleep(100000);
                }

                // see pull request https://github.com/jmathai/php-multi-curl/pull/17
                // details here http://curl.haxx.se/libcurl/c/libcurl-errors.html
                if ($ms >= CURLM_CALL_MULTI_PERFORM) {
                    do {
                        $this->_execStatus = curl_multi_exec($this->_mc, $this->_running);
                        usleep(intval($innerSleepInt));
                        $innerSleepInt = intval(max(1, ($innerSleepInt*$this->_sleepIncrement)));
                    } while ($this->_execStatus==CURLM_CALL_MULTI_PERFORM);
                    $innerSleepInt = 1;
                }
                $this->_storeResponses();
                if (isset($this->_responses[$key]['data'])) {
                    return $this->_responses[$key];
                }
            }
            return null;
        }
        return false;
    }

    public static function getSequence()
    {
        return new Sequence(self::$_timers);
    }

    public static function getTimers()
    {
        return self::$_timers;
    }

    public function inject($key, $value)
    {
        $this->$key = $value;
    }

    private function _getKey($ch)
    {
        return (string)$ch;
    }

    private function _headerCallback($ch, $header)
    {
        $_header = trim($header);
        $colonPos= strpos($_header, ':');
        if ($colonPos > 0) {
            $key = substr($_header, 0, $colonPos);
            $val = preg_replace('/^\W+/', '', substr($_header, $colonPos));
            $this->_responses[$this->_getKey($ch)]['headers'][$key] = $val;
        }
        return strlen($header);
    }

    private function _storeResponses()
    {
        while ($done = curl_multi_info_read($this->_mc)) {
            $this->_storeResponse($done);
        }
    }

    private function _storeResponse($done, $isAsynchronous = true)
    {
        $key = $this->_getKey($done['handle']);
        $this->_stopTimer($key, $done);
        if ($isAsynchronous) {
            $this->_responses[$key]['data'] = curl_multi_getcontent($done['handle']);
        } else {
            $this->_responses[$key]['data'] = curl_exec($done['handle']);
        }

        $this->_responses[$key]['response'] = $this->_responses[$key]['data'];

        foreach ($this->_properties as $name => $const) {
            $this->_responses[$key][$name] = curl_getinfo($done['handle'], $const);
        }

        if ($isAsynchronous) {
            curl_multi_remove_handle($this->_mc, $done['handle']);
        }
        curl_close($done['handle']);
    }

    private function _startTimer($key)
    {
        self::$_timers[$key]['start'] = microtime(true);
    }

    private function _stopTimer($key, $done)
    {
        self::$_timers[$key]['end'] = microtime(true);
        self::$_timers[$key]['api'] = curl_getinfo($done['handle'], CURLINFO_EFFECTIVE_URL);
        self::$_timers[$key]['time'] = curl_getinfo($done['handle'], CURLINFO_TOTAL_TIME);
        self::$_timers[$key]['code'] = curl_getinfo($done['handle'], CURLINFO_HTTP_CODE);
    }

    public static function getInstance()
    {
        if (self::$_inst == null) {
            self::$singleton = 1;
            self::$_inst = new MultiCurl();
        }
        
        return self::$_inst;
    }
}

/*
 * Credits:
 *  - (1) Alistair pointed out that curl_multi_add_handle can return CURLM_CALL_MULTI_PERFORM on success.
 */
