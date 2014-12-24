<?php

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use h4kuna\File;
use PHPUnit_Framework_TestCase;

/**
 * @author Milan Matějček
 */
class FileTest extends PHPUnit_Framework_TestCase
{

    public function testRead()
    {
        $fileName = __DIR__ . '/testRead.txt';
        $file = new File($fileName);
        $this->assertSame(file_get_contents($fileName), $file->read());
    }

    public function testIterator()
    {
        $fileName = __DIR__ . '/testRead.txt';
        $file = new File($fileName);
        $content = '';
        foreach ($file as $line) {
            $content .= $line;
        }
        $this->assertSame(file_get_contents($fileName), $content);
    }

    public function getFileInfo()
    {
        $fileName = __DIR__ . '/testRead.txt';
        $file = new File($fileName);
        $info = $file->getFileInfo();
        $this->assertSame(filemtime($fileName), $info->getMTime());
    }

}
