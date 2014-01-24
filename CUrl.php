<?php

namespace h4kuna;

use Nette\Utils\MimeTypeDetector;

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
            $content = $curl->exec();
            if (!$curl->errno()) {
                $curl->getErrors();
            }
            return $content;
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
     *                  'content' => 'file content is simple text', // or path, mantatory
     *                  'name' => 'filename.txt', // optional
     *                  'type' => 'text/plain' // optional
     *            ));
     *
     * @param string $url
     * @param array $content
     * @return CUrl - call ->exec()
     */
    static function postUploadFile($url, array $content) {
        $eol = "\r\n";
        $boundary = md5(microtime(TRUE));
        $curl = new static($url, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_POST => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_HTTPHEADER => array('Content-Type: multipart/form-data; charset=utf-8; boundary="' . $boundary . '"'))
        );
        $body = '';
        foreach ($content as $name => $value) {
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data;name="' . $name . '"';
            if (is_array($value)) {

                if (file_exists($value['content'])) {
                    $type = MimeTypeDetector::fromFile($value['content']);
                    $content = file_get_contents($value['content']);
                } else {
                    $type = MimeTypeDetector::fromString($content = $value['content']);
                }

                // is file
                $body .= '; filename="' . (isset($value['name']) ? $value['name'] : date('YmdHis')) . '"' . $eol;
                $body .= 'Content-Type: ' . (isset($value['type']) ? $value['type'] : $type) . $eol;

                if (preg_match('~base64~i', $content)) {
                    $body .= 'Content-Transfer-Encoding: base64' . $eol;
                    $content = preg_replace('~^base64~i', '', $content);
                }

                $body .= $eol;
                // $body .= chunk_split(base64_encode($content)); // RFC 2045
                $body .= trim($content) . $eol;
            } else {
                $body .= $eol . $eol . $value . $eol;
            }
        }
        $body .= "--$boundary--" . $eol . $eol;

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
