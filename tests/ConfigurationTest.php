<?php

declare(strict_types=1);

namespace Platine\Test\Template;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Template\Configuration;
use stdClass;

/**
 * Template class tests
 *
 * @group core
 * @group template
 */
class ConfigurationTest extends PlatineTestCase
{
    public function testConstructor()
    {
        $cfg = new Configuration([]);
        $this->assertInstanceOf(Configuration::class, $cfg);
    }

    public function testGetNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $cfg = new Configuration([]);
        $cfg->get('not_found_config');
    }

    public function testGetSuccess()
    {
        $cfg = new Configuration(['cache_expire' => 45]);
        $this->assertEquals(45, $cfg->get('cache_expire'));
    }
}
