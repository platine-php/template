<?php

declare(strict_types=1);

namespace Platine\Test\Template;

use InvalidArgumentException;
use Platine\PlatineTestCase;
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

    public function testDefaultValues()
    {
        $cfg = new Configuration([]);
        $this->assertEquals('.', $cfg->getCacheDir());
        $this->assertEquals('.', $cfg->getTemplateDir());
        $this->assertEquals(3600, $cfg->getCacheExpire());
        $this->assertEquals('__platine_template', $cfg->getCachePrefix());
        $this->assertEquals('tpl', $cfg->getFileExtension());

        $this->assertFalse($cfg->isIncludeWithExtension());
        $this->assertFalse($cfg->isAutoEscape());
        $this->assertEmpty($cfg->getTags());
        $this->assertEmpty($cfg->getFilters());
    }

    public function testLoadSuccess()
    {
        $cfg = new Configuration([
            'cache_expire' => 5600,
            'cache_dir' => 'tmp',
            'cache_prefix' => '__tnh__',
            'template_dir' => 'tmp/templates',
            'file_extension' => 'html',
            'include_with_extension' => true,
            'auto_escape' => true,
            'filters' => [stdClass::class],
            'tags' => [
                'tnh' => stdClass::class
            ],
        ]);

        $this->assertEquals('tmp', $cfg->getCacheDir());
        $this->assertEquals('tmp/templates', $cfg->getTemplateDir());
        $this->assertEquals(5600, $cfg->getCacheExpire());
        $this->assertEquals('__tnh__', $cfg->getCachePrefix());
        $this->assertEquals('html', $cfg->getFileExtension());

        $this->assertTrue($cfg->isIncludeWithExtension());
        $this->assertTrue($cfg->isAutoEscape());
        $this->assertCount(1, $cfg->getTags());
        $this->assertCount(1, $cfg->getFilters());
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
