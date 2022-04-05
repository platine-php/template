<?php

declare(strict_types=1);

namespace Platine\Test\Template\Loader;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
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

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => '/path/not/found',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
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

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => $path,
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        (new FileLoader($cfg));
    }


    public function testReadInvalidTemplateName(): void
    {
        global $mock_realpath_to_same;

        $path = $this->vfsTemplatePath->url();
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => $path,
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
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
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => $path,
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
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
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => $path,
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;

        $expected = '{% foo %}';
        $this->createVfsFile('page.tpl', $this->vfsTemplatePath, $expected);
        $o = new FileLoader($cfg);
        $this->assertEquals($o->read('page'), $expected);
    }
}
