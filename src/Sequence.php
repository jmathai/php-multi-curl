<?php 
namespace JMathai\PhpMultiCurl;
/**
 * Sequence displays sequence of http calls
 *
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */
class Sequence
{
    private $_width = 100;
    private $_timers;
    private $_min;
    private $_max;
    private $_range;
    private $_step;

    public function __construct($timers) 
    {
        $this->_timers = $timers;
    
        $min = PHP_INT_MAX;
        $max = 0;
        foreach ($this->_timers as $timer) {
            if (!isset($timer['start'])) {
                $timer['start'] = PHP_INT_MAX;
            }

            if (!isset($timer['end'])) {
                $timer['end'] = 0;
            }

            $min = min($timer['start'], $min);
            $max = max($timer['end'], $max);
        }
        $this->_min = $min;
        $this->_max = $max;
        $this->_range = $max-$min;
        $this->_step = floatval($this->_range/$this->_width);
    }

    public function renderAscii()
    {
        $tpl = '';
        foreach ($this->_timers as $timer) {
            $tpl .= $this->_tplAscii($timer);
        }
    
        return $tpl;
    }

    private function _tplAscii($timer)
    {
        $lpad = $rpad = 0;
        $lspace = $chars = $rspace = '';
        if ($timer['start'] > $this->_min) {
            $lpad = intval(($timer['start'] - $this->_min) / $this->_step);
        }
        if ($timer['end'] < $this->_max) {
            $rpad = intval(($this->_max - $timer['end']) / $this->_step);
        }
        $mpad = $this->_width - $lpad - $rpad;
        if ($lpad > 0) {
            $lspace = str_repeat(' ', $lpad);
        }
        if ($mpad > 0) {
            $chars = str_repeat('=', $mpad);
        }
        if ($rpad > 0) {
            $rspace = str_repeat(' ', $rpad);
        }
    
        $tpl = <<<TPL
({$timer['api']} ::  code={$timer['code']}, start={$timer['start']}, end={$timer['end']}, total={$timer['time']})
[{$lspace}{$chars}{$rspace}]

TPL;
        return $tpl;
    }
}
