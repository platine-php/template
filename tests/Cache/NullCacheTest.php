<?php

declare(strict_types=1);

namespace Platine\Test\Template\Cache;

use Platine\PlatineTestCase;
use Platine\Template\Cache\NullCache;
use Platine\Template\Configuration;

/**
 * NullCache class tests
 *
 * @group core
 * @group template
 */
class NullCacheTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $o = new NullCache($cfg);
        $this->assertInstanceOf(NullCache::class, $o);
        $this->assertInstanceOf(Configuration::class, $this->getPropertyValue(
            NullCache::class,
            $o,
            'config'
        ));
    }

    public function testRead(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $o = new NullCache($cfg);
        $this->assertFalse($o->read('fookey'));
    }

    public function testExists(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $o = new NullCache($cfg);
        $this->assertFalse($o->exists('fookey'));
    }

    public function testWrite(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $o = new NullCache($cfg);
        $this->assertTrue($o->write('fookey', 'foovalue'));
    }

    public function testFlush(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $o = new NullCache($cfg);
        $this->assertTrue($o->flush());
    }
}
