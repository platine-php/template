<?php

declare(strict_types=1);

namespace Platine\Test\Template\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\PlatineTestCase;
use Platine\Template\Configuration;
use Platine\Template\Exception\NotFoundException;
use Platine\Template\Exception\ParseException;
use Platine\Template\Loader\FileLoader;

/**
 * FileLoader class tests
 *
 * @group core
 * @group template
 */
class FileLoaderTest extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsTemplatePath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsTemplatePath = vfsStream::newDirectory('templates')->at($this->vfsRoot);
    }

    public function testConstructorDirectoryNotFound(): void
    {
        global $mock_realpath_to_false;

        $this->expectException(NotFoundException::class);
        $mock_realpath_to_false = true;
        $cfg = $this->getMockInstance(Configuration::class, [
            'getTemplateDir' => '/path/not/found/'
        ]);

        (new FileLoader($cfg));
    }

    public function testConstructorDirectoryNotWritable(): void
    {
        global $mock_realpath_to_same;
        $this->expectException(NotFoundException::class);
        $path = $this->vfsTemplatePath->url();
        chmod($path, 0400);

        $mock_realpath_to_same = true;

        $cfg = $this->getMockInstance(Configuration::class, [
            'getTemplateDir' => $path
        ]);

        (new FileLoader($cfg));
    }


    public function testReadInvalidTemplateName(): void
    {
        global $mock_realpath_to_same;

        $path = $this->vfsTemplatePath->url();
        $cfg = $this->getMockInstance(Configuration::class, [
            'getTemplateDir' => $path,
            'isIncludeWithExtension' => false
        ]);

        $mock_realpath_to_same = true;

        $this->expectException(ParseException::class);

        $o = new FileLoader($cfg);
        $o->read('page.tpl');
    }

    public function testReadFileNotFound(): void
    {
        global $mock_realpath_to_same;
        $path = $this->vfsTemplatePath->url();
        $cfg = $this->getMockInstance(Configuration::class, [
            'getTemplateDir' => $path,
            'isIncludeWithExtension' => false
        ]);

        $mock_realpath_to_same = true;

        $this->expectException(NotFoundException::class);

        $o = new FileLoader($cfg);
        $o->read('page');
    }

    public function testRead(): void
    {
        global $mock_realpath_to_same;

        $path = $this->vfsTemplatePath->url();
        $cfg = $this->getMockInstance(Configuration::class, [
            'getTemplateDir' => $path,
            'isIncludeWithExtension' => true
        ]);

        $mock_realpath_to_same = true;

        $expected = '{% foo %}';
        $file = $this->createVfsFile('page.tpl', $this->vfsTemplatePath, $expected);
        $o = new FileLoader($cfg);
        $this->assertEquals($o->read('page.tpl'), $expected);
    }
}
