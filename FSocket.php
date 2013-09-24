<?php

namespace h4kuna;

/**
 * Open Internet or Unix domain socket connection
 *
 * @author Milan Matějček
 */
class FSocket extends ObjectWrapper {

    protected $prefix = 'f';
    private $errno;
    private $errstr;

    /**
     *
     * @param string|NULL $hostname
     * @param int $port
     * @param NULL|int $timeOut
     */
    public function __construct($hostname = NULL, $port = -1, $timeOut = NULL) {
        if ($hostname) {
            $this->open($hostname, $port, $timeOut);
        }
    }

    /**
     * Open connection
     *
     * @param string $hostname
     * @param int $port
     * @param int $timeOut
     * @return void
     */
    public function open($hostname, $port = -1, $timeOut = NULL) {
        $this->resource = fsockopen($hostname, $port, $this->errno, $this->errstr, $timeOut);
        if (!$this->resource) {
            $this->exception();
        }
    }

    /**
     * Error message
     *
     * @return string
     */
    public function getErrstr() {
        return $this->errstr;
    }

    /**
     * Error number
     *
     * @return int
     */
    public function getErrno() {
        return $this->errno;
    }

    /**
     * Error as string #number, message
     *
     * @return string
     */
    public function getError() {
        return "#{$this->errno}, " . $this->errstr;
    }

    /**
     * Error as exception
     *
     * @throws \RuntimeException
     */
    protected function exception() {
        throw new \RuntimeException($this->errstr, $this->errno);
    }

    /**
     * Close connection
     */
    protected function close() {
        $this->__call('close');
    }

}
