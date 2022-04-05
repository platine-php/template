<?php

declare(strict_types=1);

namespace Platine\Test\Template\Cache;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\FileCache;
use Platine\Template\Configuration;
use Platine\Template\Exception\NotFoundException;

/**
 * FileCache class tests
 *
 * @group core
 * @group template
 */
class FileCacheTest extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsCachePath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsCachePath = vfsStream::newDirectory('caches')->at($this->vfsRoot);
    }

    public function testConstructorDirectoryNotFound(): void
    {
        global $mock_realpath_to_false;

        $this->expectException(NotFoundException::class);
        $mock_realpath_to_false = true;
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '/path/not/found',
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        (new FileCache($cfg));
    }

    public function testConstructorDirectoryNotWritable(): void
    {
        global $mock_realpath_to_same;
        $this->expectException(NotFoundException::class);
        $path = $this->vfsCachePath->url();
        chmod($path, 0400);

        $mock_realpath_to_same = true;

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        (new FileCache($cfg));
    }

    public function testReadNotExist(): void
    {
        global $mock_realpath_to_same;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;

        $o = new FileCache($cfg);
        $this->assertFalse($o->read('fookey'));
    }

    public function testReadSuccessNoSerialization(): void
    {
        global $mock_realpath_to_same,
               $mock_file_exists,
               $mock_filemtime_to_int,
               $mock_time_to_zero,
               $mock_file_get_contents_to_data;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_file_exists = true;
        $mock_filemtime_to_int = true;
        $mock_time_to_zero = true;
        $mock_file_get_contents_to_data = true;

        $o = new FileCache($cfg);
        $this->assertEquals('foobar', $o->read('fookey', false));
    }

    public function testReadSuccessWithSerialization(): void
    {
        global $mock_realpath_to_same,
               $mock_file_exists,
               $mock_filemtime_to_int,
               $mock_time_to_zero,
               $mock_file_get_contents_to_data_serialize;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_file_exists = true;
        $mock_filemtime_to_int = true;
        $mock_time_to_zero = true;
        $mock_file_get_contents_to_data_serialize = true;

        $o = new FileCache($cfg);
        $this->assertEquals('foobar', $o->read('fookey', true));
    }

    public function testExistsTrue(): void
    {
        global $mock_realpath_to_same,
               $mock_file_exists,
               $mock_filemtime_to_int,
               $mock_time_to_zero;

        $path = $this->vfsCachePath->url();
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_file_exists = true;
        $mock_filemtime_to_int = true;
        $mock_time_to_zero = true;

        $o = new FileCache($cfg);

        $this->assertTrue($o->exists('fookey'));
    }

    public function testExistsFalse(): void
    {
        global $mock_realpath_to_same,
               $mock_filemtime_to_int,
               $mock_time_to_zero;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_filemtime_to_int = true;
        $mock_time_to_zero = true;


        $o = new FileCache($cfg);


        $this->assertFalse($o->exists('fookey'));
    }

    public function testWriteFailed(): void
    {
        global $mock_realpath_to_same,
               $mock_file_put_contents_to_false;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_file_put_contents_to_false = true;

        $o = new FileCache($cfg);
        $this->assertFalse($o->write('fookey', 'foovalue'));
    }

    public function testWriteSuccessWithSerialization(): void
    {
        global $mock_realpath_to_same;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;

        $o = new FileCache($cfg);
        $this->assertTrue($o->write('fookey', 'foovalue', true));
    }

    public function testFlush(): void
    {
        global $mock_realpath_to_same,
               $mock_glob,
               $mock_unlink_to_true;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_glob = true;
        $mock_unlink_to_true = true;

        $o = new FileCache($cfg);
        $this->assertTrue($o->flush());
    }

    public function testFlushOnlyExpired(): void
    {
        global $mock_realpath_to_same,
               $mock_glob,
               $mock_unlink_to_true,
               $mock_time,
               $mock_filemtime_to_int;

        $path = $this->vfsCachePath->url();

        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => $path,
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $mock_realpath_to_same = true;
        $mock_glob = true;
        $mock_unlink_to_true = true;
        $mock_time = true;
        $mock_filemtime_to_int = true;

        $o = new FileCache($cfg);
        $this->assertTrue($o->flush(true));
    }
}
