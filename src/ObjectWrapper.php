<?php

namespace h4kuna;

use RuntimeException;

/**
 * @author Milan Matějček
 */
abstract class ObjectWrapper
{

    /** @var Resource */
    protected $resource;

    /** @var string prefix of function */
    protected $prefix;

    public function __call($name, $args = array())
    {
        $fname = $this->prefix . $name;
        if (function_exists($fname)) {
            array_unshift($args, $this->resource);
            return call_user_func_array($fname, $args);
        }
        throw new RuntimeException('Call undefined method ' . get_called_class() . '::' . $name);
    }

    /**
     * Active resource of class
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Safety clear resource
     *
     * @return void
     */
    public function clearResource()
    {
        if (!is_resource($this->resource)) {
            $this->resource = NULL;
            return;
        }

        $this->close();
        $this->resource = NULL;
    }

    /**
     * Implement close resource
     */
    abstract public function close();

    public function __destruct()
    {
        $this->clearResource();
    }

}
