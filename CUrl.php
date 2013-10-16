<?php

namespace h4kuna;

use Nette\Utils\Strings;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 */
class CUrl extends ObjectWrapper {

    const OPT = 'CURLOPT_';
    const INFO = 'CURLINFO_';

    protected $prefix = 'curl_';

    /**
     * Inicializace curl
     *
     * @param string $url
     * @param array $options
     */
    public function __construct($url = FALSE, array $options = NULL) {

        if (!extension_loaded('curl')) {
            throw new CUrlException('Curl extension, does\'t loaded.');
        }

        $this->resource = $this->init();
        if ($options === NULL) {
            $options = array(CURLOPT_HEADER => FALSE, CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_SSL_VERIFYPEER => FALSE, CURLOPT_SSL_VERIFYHOST => FALSE);
        }

        if ($url !== FALSE) {
            $options += array(CURLOPT_URL => $url);
        }

        $this->setOptions($options);
    }

    /**
     * Magic setter
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value) {
        $val = strtoupper($name);
        if (defined($val)) {
            return $this->setopt(constant($val), $value);
        }

        $const = self::OPT . $val;
        if (defined($const)) {
            return $this->setopt(constant($const), $value);
        }

        $const = self::INFO . $val;
        if (defined($const)) {
            return $this->setopt(constant($const), $value);
        }

        return parent::__set($name, $value);
    }

    /**
     * Magic getter
     *
     * @param mixed $name
     * @return mixed
     */
    public function &__get($name) {
        $val = strtoupper($name);
        if (defined(self::INFO . $val)) {
            $a = $this->getInfo(constant(self::INFO . $val));
            return $a;
        }
        return parent::__get($name);
    }

    /**
     * Throw exception
     *
     * @return void
     */
    public function getErrors() {
        throw new CUrlException($this->error(), $this->errno());
    }

    /**
     * Curl version
     *
     * @param int $age
     * @return string
     */
    public static function getVersion($age = CURLVERSION_NOW) {
        return curl_version($age);
    }

    /**
     * Set curl options
     *
     * @param array $opts
     * @return bool
     */
    public function setOptions(array $opts) {
        return curl_setopt_array($this->resource, $opts);
    }

    /**
     * Download content page
     *
     * @param string $url
     * @return string
     * @throws CUrlException
     */
    static function download($url) {
        try {
            $curl = new static($url);
            if ($curl->errno() > 0) {
                $curl->getErrors();
            }
            return $curl->exec();
        } catch (CUrlException $e) {
            if (!ini_get('allow_url_fopen')) {
                throw new CUrlException('You need allow_url_fopen -on or curl extension');
            }
        }
        return file_get_contents($url);
    }

    /**
     * @example
     * $content = array(
     *            'foo' => 'bar',
     *            'file' => array(
     *                  'content' => 'file content is simple text', // or path
     *                  'name' => 'filename.txt',
     *                  'type' => 'text/plain'
     *            ));
     *
     * @param string $url
     * @param array $content
     * @return CUrl
     */
    static function postUploadFile($url, array $content) {
        $nl = "\r\n";
        $boundary = '--------CurlBoundary' . Strings::random(18);
        $curl = new static($url, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_VERBOSE => 0,
            CURLOPT_HTTPHEADER => array('Content-Type: multipart/form-data;charset=utf-8;boundary="' . $boundary . '"'))
        );
        $body = '';
        foreach ($content as $name => $value) {
            $body .= '--' . $boundary . $nl . 'Content-Disposition: form-data;name="' . $name . '"';
            if (is_array($value)) {
                // is file
                $body .= ';filename="' . $value['name'] . '"' . $nl;
                $body .= 'Content-Type: ' . $value['type'];
                $value = file_exists($value['content']) ? file_get_contents($value['content']) : $value['content'];
            }
            $body .= $nl . $nl . $value . $nl;
        }
        $body .= "--$boundary--";

        $curl->setopt(CURLOPT_POSTFIELDS, $body);
        return $curl;
    }

    /**
     * Close connection
     * @return void
     */
    protected function close() {
        $this->__call('close');
    }

}

/**
 *
 * @author Milan Matějček
 */
class CUrlException extends \RuntimeException {

}
