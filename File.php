<?php

namespace h4kuna;

/**
 * @author Milan Matějček
 */
class File extends ObjectWrapper {

    protected $prefix = 'f';

    /**
     *
     * @param string $fileName
     * @param string $mode
     * @param mixed $useIncludePath
     * @param mixed $context
     */
    public function __construct($fileName = NULL, $mode = 'r', $useIncludePath = FALSE, $context = NULL) {
        if ($fileName) {
            $this->open($fileName, $mode, $useIncludePath, $context);
        }
    }

    /**
     *
     * @param string $fileName
     * @param string $mode
     * @param mixed $useIncludePath
     * @param mixed $context
     * @throws \RuntimeException
     */
    public function open($fileName, $mode = 'r', $useIncludePath = FALSE, $context = NULL) {
        if ($context) {
            $this->resource = @fopen($fileName, $mode, $useIncludePath, $context);
        } else {
            $this->resource = @fopen($fileName, $mode, $useIncludePath);
        }

        if (!$this->resource) {
            throw new \RuntimeException('This file "' . $fileName . '" did not open.');
        }
    }

    /**
     * Close resourse
     */
    protected function close() {
        $this->__call('close');
    }

}
