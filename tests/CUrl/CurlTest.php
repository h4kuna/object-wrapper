<?php

namespace Tests;

require_once __DIR__ . '/../bootstrap.php';

use h4kuna\CUrl\CUrl;
use h4kuna\CUrl\CUrlBuilder;
use h4kuna\CUrl\CUrlMulti;
use PHPUnit_Framework_TestCase;

/**
 * @author Milan Matějček
 */
class CUrlTest extends PHPUnit_Framework_TestCase
{

    const TEST_URL_95 = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt?date=30.12.1995';
    const TEST_URL_96 = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt?date=30.12.1996';

    public function testCurlDownload()
    {
        $data = CUrlBuilder::download(self::TEST_URL_95);
        $this->assertSame(file_get_contents(__DIR__ . '/../testCurlDownload.txt'), $data);
    }

    public function testCurlMulti()
    {
        $a = CUrlBuilder::createDownload(self::TEST_URL_95);
        $b = CUrlBuilder::createDownload(self::TEST_URL_96);

        $multi = new CUrlMulti();
        $multi->addHandles(array('1995-12-30' => $a), $b);
        $multi->exec();
        $this->assertEquals(file_get_contents(__DIR__ . '/../testCurlMulti.txt'), serialize($multi->getSelect()));
    }

    public function testCreateFile()
    {
        $curl = new CUrl(self::TEST_URL_95);
        $file = $curl->fileCreate(__FILE__);

        if (PHP_VERSION_ID >= 50500) {
            $this->assertSame(TRUE, $file instanceof \CURLFile);
        } else {
            $this->assertSame(TRUE, '@' . __FILE__ == $file);
        }
    }

    public function _testCurlShare()
    {
        // je funkční ale jestli pracuje správně???
        $a = new CUrl('http://example.com/');
        $a->enableShare();
        dump($a->exec());

        $b = new CUrl('http://php.net/');
        $b->enableShare();
        dump($b->exec());
    }

}
