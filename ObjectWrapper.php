<?php

namespace h4kuna;

use Nette\Object;

/**
 * @author Milan Matějček
 */
abstract class ObjectWrapper extends Object {

    /** @var resource */
    protected $resource;

    /** @var string prefix of function */
    protected $prefix;

    public function __call($name, $args = array()) {
        $fname = $this->prefix . $name;
        if (function_exists($fname)) {
            array_unshift($args, $this->resource);
            return call_user_func_array($fname, $args);
        }
        throw new \RuntimeException('Call undefined method ' . __CLASS__ . '::' . $name);
    }

    public function getResource() {
        return $this->resource;
    }

    public function clearResource() {
        if (!is_resource($this->resource)) {
            $this->resource = NULL;
            return;
        }

        $this->close(); //magic call or implement
        $this->resource = NULL;
    }

    /**
     * close resource
     */
    abstract protected function close();

    public function __destruct() {
        $this->clearResource();
    }

}
