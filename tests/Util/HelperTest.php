<?php

declare(strict_types=1);

namespace Platine\Test\Template\Util;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Util\Helper;

/**
 * Helper class tests
 *
 * @group core
 * @group template
 */
class HelperTest extends PlatineTestCase
{
    public function testNormalizePathAddLast(): void
    {
        $expected = '/foo' . DIRECTORY_SEPARATOR;

        $result = Helper::normalizePath('/foo');
        $this->assertEquals($expected, $result);
    }

    public function testNormalizePathTrimUnix(): void
    {
        $expected = '/foo' . DIRECTORY_SEPARATOR;

        $result = Helper::normalizePath('/foo/');
        $this->assertEquals($expected, $result);
    }

    public function testNormalizePathTrimWin(): void
    {
        $expected = '/foo' . DIRECTORY_SEPARATOR;

        $result = Helper::normalizePath('/foo\\');
        $this->assertEquals($expected, $result);
    }

    public function testDashesToCamelCase(): void
    {
        $list = [
           'sort_' => 'sort',
           'sort_key' => 'sortKey',
           'sortkey' => 'sortkey',
           'sortKey' => 'sortKey',
           'sort-key' => 'sort-key',
           'SORTKEY' => 'sORTKEY',
        ];

        foreach ($list as $key => $expected) {
            $result = Helper::dashesToCamelCase($key);
            $this->assertEquals($expected, $result);
        }
    }

    public function testArrayFlatten(): void
    {
        $arr = [
           'sort_' => 'sort',
           ['one', 'two'],
           'tags' => [1, 2, 3]
        ];
        $this->assertCount(3, $arr);
        $result = Helper::arrayFlatten($arr);

        $this->assertCount(6, $result);
    }
}
