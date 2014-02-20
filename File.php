<?php

namespace h4kuna;

use Iterator;
use RuntimeException;

/**
 * This class is alias for SplFileObject, but I can read 1GB file with this class
 *
 * @author Milan Matějček
 *
 * @method bool eof()
 * @method bool flush()
 * @method string getc()
 * @method array getcsv(int $length = 0, string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
 * @method string gets(int $length)
 * @method string getss(int $length, string $allowable_tags)
 * @method int passthru()
 * @method int putcsv(array $fields, string $delimiter = ',', string $enclosure = '"')
 * @method int seek(int $offset, int $whence = SEEK_SET)
 * @method array stat()
 * @method int tell()
 * @method bool truncate(int $size)
 * @method int write(string $string, int $length = 0)
 */
class File extends ObjectWrapper implements Iterator {

    protected $prefix = 'f';

    /** @var string */
    private $fileName;

    /** @var int */
    private $lineNumber;

    /**
     *
     * @param string $fileName
     * @param string $mode
     * @param mixed $useIncludePath
     * @param mixed $context
     */
    public function __construct($fileName, $mode = 'r', $useIncludePath = FALSE, $context = NULL) {
        $this->fileName = $fileName;

        if ($context) {
            $this->resource = @fopen($fileName, $mode, $useIncludePath, $context);
        } else {
            $this->resource = @fopen($fileName, $mode, $useIncludePath);
        }

        if (!$this->resource) {
            throw new RuntimeException('This file "' . $fileName . '" did not open.');
        }
    }

    /**
     *
     * @param int $operation
     * @param int $wouldLock
     * @return bool
     */
    public function lock($operation, &$wouldLock = NULL) {
        return flock($this->resource, $operation, $wouldLock);
    }

    /**
     *
     * @param int $length
     * @return string
     */
    public function read($length = 0) {
        if ($length === 0) {
            $length = filesize($this->fileName);
        }
        return fread($this->resource, $length);
    }

    /**
     * Close resourse
     * @return bool
     */
    public function close() {
        return fclose($this->resource);
    }

    /** @return string */
    public function current() {
        return fgets($this->resource);
    }

    /** @return int */
    public function key() {
        return $this->lineNumber;
    }

    public function next() {
        ++$this->lineNumber;
    }

    public function rewind() {
        fseek($this->resource, 0);
        $this->lineNumber = 1;
    }

    /** @return bool */
    public function valid() {
        return !feof($this->resource);
    }

}
