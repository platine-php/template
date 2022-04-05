<?php

declare(strict_types=1);

namespace Platine\Test\Template\Cache;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\MemoryCache;
use Platine\Template\Configuration;

/**
 * MemoryCache class tests
 *
 * @group core
 * @group template
 */
class MemoryCacheTest extends PlatineTestCase
{
    public function testRead(): void
    {
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $o = new MemoryCache($cfg);
        $this->assertFalse($o->read('fookey'));
        $expected = 'foovalue';

        $o->write('fookey', $expected);

        $this->assertEquals($o->read('fookey'), $expected);
    }


    public function testExists(): void
    {
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $o = new MemoryCache($cfg);
        $this->assertFalse($o->exists('fookey'));
        $expected = 'foovalue';

        $o->write('fookey', $expected);
        $this->assertTrue($o->exists('fookey'));
    }

    public function testWrite(): void
    {
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $o = new MemoryCache($cfg);
        $this->assertTrue($o->write('fookey', 'foovalue'));
    }

    public function testFlush(): void
    {
        $cfg = new Configuration([
            'cache_expire' => 3600,
            'cache_dir' => '.',
            'cache_prefix' => '__platine_template',
            'template_dir' => '.',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ]);

        $o = new MemoryCache($cfg);
        $this->assertTrue($o->flush());
        $this->assertEmpty($this->getPropertyValue(
            MemoryCache::class,
            $o,
            'data'
        ));
    }
}
