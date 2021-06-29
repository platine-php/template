<?php

declare(strict_types=1);

namespace Platine\Test\Template\Loader;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\NotFoundException;
use Platine\Template\Loader\StringLoader;

/**
 * StringLoader class tests
 *
 * @group core
 * @group template
 */
class StringLoaderTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $o = new StringLoader([]);
        $this->assertInstanceOf(StringLoader::class, $o);
        $this->assertEmpty($this->getPropertyValue(
            StringLoader::class,
            $o,
            'data'
        ));
    }

    public function testRead(): void
    {
        $expected = '{% foo %}';
        $o = new StringLoader(['page' => $expected]);
        $this->assertEquals($o->read('page'), $expected);
    }

    public function testReadNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $o = new StringLoader([]);
        $o->read('page');
    }
}
