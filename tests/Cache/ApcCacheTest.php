<?php

declare(strict_types=1);

namespace Platine\Test\Template\Cache;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\ApcCache;
use Platine\Template\Configuration;
use Platine\Template\Exception\TemplateException;

/**
 * ApcCache class tests
 *
 * @group core
 * @group template
 */
class ApcCacheTest extends PlatineTestCase
{
    public function testConstructorExtensionIsNotLoaded(): void
    {
        global $mock_extension_loaded_to_false;

        $mock_extension_loaded_to_false = true;
        $this->expectException(TemplateException::class);
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

        (new ApcCache($cfg));
    }

    public function testConstructorExtensionIstLoadedButNotEnabled(): void
    {
        global $mock_extension_loaded_to_true, $mock_ini_get_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_false = true;

        $this->expectException(TemplateException::class);
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

        (new ApcCache($cfg));
    }

    public function testRead(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_fetch_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;

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
        $ac = new ApcCache($cfg);

        $mock_apcu_fetch_to_false = true;
        //value not exists
        $this->assertFalse($ac->read('not_found_key'));

        $mock_apcu_fetch_to_false = false;
        //Return correct data
        $key = uniqid();
        $content = $ac->read($key);
        $this->assertEquals(md5('__platine_template' . $key), $content);
    }


    public function testExists(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_exists_to_true,
        $mock_apcu_exists_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;

        $key = uniqid();
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

        $ac = new ApcCache($cfg);

        $mock_apcu_exists_to_false = true;

        $this->assertFalse($ac->exists($key));

        $mock_apcu_exists_to_false = false;
        $mock_apcu_exists_to_true = true;

        $this->assertTrue($ac->exists($key));
    }

    public function testWriteFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_false = true;

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

        $ac = new ApcCache($cfg);
        $result = $ac->write('key', 'data');
        $this->assertFalse($result);
    }


    public function testWriteSuccess(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_true;

        $key = uniqid();
        $data = array('foo' => 'bar');

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_true = true;

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
        $ac = new ApcCache($cfg);
        $result = $ac->write($key, $data);
        $this->assertTrue($result);
    }

    public function testFlushFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_clear_cache_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_clear_cache_to_false = true;

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

        $ac = new ApcCache($cfg);

        $this->assertFalse($ac->flush());
    }

    public function testFlushSuccess(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_clear_cache_to_true;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_clear_cache_to_true = true;

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

        $ac = new ApcCache($cfg);

        $this->assertTrue($ac->flush());
    }
}
