<?php

namespace h4kuna;

/**
 * Open Internet or Unix domain socket connection
 * @author Milan Matějček
 */
class FSocket extends ObjectWrapper {

    protected $prefix = 'f';
    private $errno;
    private $errstr;

    public function __construct($hostname = NULL, $port = -1, $timeOut = NULL) {
        if ($hostname) {
            $this->open($hostname, $port, $timeOut);
        }
    }

    public function open($hostname, $port = -1, $timeOut = NULL) {
        $this->resource = fsockopen($hostname, $port, $this->errno, $this->errstr, $timeOut);
        if (!$this->resource) {
            $this->exception();
        }
    }

    public function getErrstr() {
        return $this->errstr;
    }

    public function getErrno() {
        return $this->errno;
    }

    public function getError() {
        return "#{$this->errno}, " . $this->errstr;
    }

    protected function exception() {
        throw new \RuntimeException($this->errstr, $this->errno);
    }

    protected function close() {
        $this->__call('close');
    }

}
