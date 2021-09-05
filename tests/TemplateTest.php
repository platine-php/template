<?php

declare(strict_types=1);

namespace Platine\Test\Template;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\NullCache;
use Platine\Template\Configuration;
use Platine\Template\Exception\ParseException;
use Platine\Template\Loader\StringLoader;
use Platine\Template\Template;
use Platine\Test\Fixture\CustomBlock;
use Platine\Test\Fixture\CustomTag;
use stdClass;

/**
 * Template class tests
 *
 * @group core
 * @group template
 */
class TemplateTest extends PlatineTestCase
{

    public function testConstructor(): void
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
        $loader = $this->getMockInstance(StringLoader::class);
        $cache = $this->getMockInstance(NullCache::class);

        $t = new Template($cfg, $loader, $cache);
        $this->assertInstanceOf(Template::class, $t);
        $this->assertInstanceOf(Configuration::class, $t->getConfig());
        $this->assertInstanceOf(StringLoader::class, $t->getLoader());
        $this->assertInstanceOf(NullCache::class, $t->getCache());
    }

    public function testTags(): void
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
        $loader = $this->getMockInstance(StringLoader::class);
        $cache = $this->getMockInstance(NullCache::class);

        $t = new Template($cfg, $loader, $cache);

        $this->assertEmpty($t->getTags());
        $t->addTag('mytag', stdClass::class);
         $this->assertCount(1, $t->getTags());
    }

    public function testFilters(): void
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
        $loader = $this->getMockInstance(StringLoader::class);
        $cache = $this->getMockInstance(NullCache::class);

        $t = new Template($cfg, $loader, $cache);

        $this->assertEmpty($t->getFilters());
        $t->addFilter(stdClass::class);
        $this->assertCount(1, $t->getFilters());
    }

    public function testSetTickCallback(): void
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
        $loader = $this->getMockInstance(StringLoader::class);
        $cache = $this->getMockInstance(NullCache::class);

        $t = new Template($cfg, $loader, $cache);

        $this->assertNull($this->getPropertyValue(Template::class, $t, 'tickCallback'));
        $t->setTickCallback(function () {
        });
        $this->assertNotNull($this->getPropertyValue(Template::class, $t, 'tickCallback'));
    }

    public function testRender(): void
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
        $loader = $this->getMockInstance(StringLoader::class, ['read' => '{% tnh %}']);
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);

        $t = new Template($cfg, $loader, $cache);
        $t->setTickCallback(function () {
        });

        $t->addTag('tnh', CustomTag::class);
        $t->addFilter(stdClass::class);

        $result = $t->render('mypage', [], []);

        $this->assertEquals($result, CustomTag::class);
    }
    
    public function testRenderString(): void
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
        $loader = $this->getMockInstance(StringLoader::class, ['read' => '{% tnh %}']);
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);

        $t = new Template($cfg, $loader, $cache);
        $t->setTickCallback(function () {
        });

        $t->addTag('tnh', CustomTag::class);
        $t->addFilter(stdClass::class);

        $result = $t->renderString('{% tnh %}', [], []);

        $this->assertEquals($result, CustomTag::class);
    }

    public function testRenderMissingDelimiter(): void
    {
        $this->expectException(ParseException::class);
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
        $loader = $this->getMockInstance(StringLoader::class, ['read' => '{% tnh %}']);
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);

        $t = new Template($cfg, $loader, $cache);
        $t->setTickCallback(function () {
        });

        $t->addTag('tnh', CustomBlock::class);
        $t->addFilter(stdClass::class);

        $result = $t->render('mypage', [], []);

        $this->assertEquals($result, CustomTag::class);
    }
}
