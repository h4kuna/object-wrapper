<?php

namespace h4kuna;

/**
 * @author Milan Matějček
 */
class File extends ObjectWrapper {

    protected $prefix = 'f';

    public function __construct($fileName = NULL, $mode = 'r', $useIncludePath = FALSE, $context = NULL) {
        if ($fileName) {
            $this->open($fileName, $mode, $useIncludePath, $context);
        }
    }

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

    protected function close() {
        $this->__call('close');
    }

}
