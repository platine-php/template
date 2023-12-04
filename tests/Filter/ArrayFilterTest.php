<?php

declare(strict_types=1);

namespace Platine\Test\Template\Filter;

use ArrayIterator;
use Platine\Dev\PlatineTestCase;
use Platine\Template\Filter\ArrayFilter;

/**
 * ArrayFilter class tests
 *
 * @group core
 * @group template
 */
class ArrayFilterTest extends PlatineTestCase
{
    public function testFirst(): void
    {
        $expected = 34;
        $list = [34, 3, 4, 5];

        $result = ArrayFilter::first($list);
        $this->assertEquals($expected, $result);

       //Iterator
        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::first($arrayIt);
        $this->assertEquals($expected, $resultIt);
        $this->assertEquals(23, ArrayFilter::first(23));
    }

    public function testLast(): void
    {
        $expected = 5;
        $list = [34, 3, 4, 5];

        $result = ArrayFilter::last($list);
        $this->assertEquals($expected, $result);

       //Traversable
        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::last($arrayIt);
        $this->assertEquals($expected, $resultIt);
        $this->assertEquals(23, ArrayFilter::last(23));
    }

    public function testSortDefault(): void
    {
        $expected = [3, 4, 5, 34];
        $list = [34, 3, 4, 5];

        $result = ArrayFilter::sort($list);
        $this->assertEquals($expected, array_values($result));
    }

    public function testSortTraversable(): void
    {
        $expected = [3, 4, 5, 34];
        $list = [34, 3, 4, 5];

        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::sort($arrayIt);
        $this->assertEquals($expected, array_values($resultIt));
    }

    public function testSortUsingProperty(): void
    {
        $expected = [['a' => 11, 'b' => 2], ['a' => 15, 'b' => 21]];
        $list = [['a' => 15, 'b' => 21], ['a' => 11, 'b' => 2]];

        $result = ArrayFilter::sort($list, 'b');
        $this->assertEquals($expected, array_values($result));
    }

    public function testSortKey(): void
    {
        $expected = [3, 5, 34, 4];
        $list = ['c' => 34, 'a' => 3, 'b' => 5, 'd' => 4];

        $result = ArrayFilter::sortKey($list);
        $this->assertEquals($expected, array_values($result));
        $this->assertEquals(12, ArrayFilter::sortKey(12));
    }

    public function testUniqueArray(): void
    {
        $expected = [4, 3, 5];
        $list = [4, 3, 4, 5];

        $result = ArrayFilter::unique($list);
        $this->assertEquals($expected, array_values($result));
    }

    public function testUniqueTraversable(): void
    {
        $expected = [4, 3, 5];
        $list = [4, 3, 4, 5];

        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::unique($arrayIt);
        $this->assertEquals($expected, array_values($resultIt));
    }

    public function testReverseArray(): void
    {
        $expected = [5, 4, 3];
        $list = [3, 4, 5];

        $result = ArrayFilter::reverse($list);
        $this->assertEquals($expected, array_values($result));
    }

    public function testReverseTraversable(): void
    {
        $expected = [5, 4, 3];
        $list = [3, 4, 5];

        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::reverse($arrayIt);
        $this->assertEquals($expected, array_values($resultIt));
    }

    public function testReserveNotArrayOrTraversable(): void
    {
        $this->assertEquals(13, ArrayFilter::reverse(13));
    }

    public function testMapTraversable(): void
    {
        $expected = [5, 30];
        $list = [[3, 4, 5], [10, 20, 30, 40]];

        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::map($arrayIt, 2);
        $this->assertEquals($expected, $resultIt);
    }

    public function testMapNotArrayOrTraversable(): void
    {
        $this->assertEquals(13, ArrayFilter::map(13, 2));
    }

    public function testMapSomeValueNull(): void
    {
        $expected = [5, null, 30];
        $list = [[3, 4, 5], [3, 1], [10, 20, 30, 40]];

        $arrayIt = new ArrayIterator($list);
        $resultIt = ArrayFilter::map($arrayIt, 2);
        $this->assertEquals($expected, $resultIt);
    }

    public function testMapCallable(): void
    {
        $expected = [50, 30];
        $list = [function () {
            return 50;
        }, [10, 20, 30, 40]];

        $result = ArrayFilter::map($list, 2);
        $this->assertEquals($expected, $result);
    }

    public function testJson(): void
    {
        $this->assertEquals(13, ArrayFilter::json(13));
        $this->assertEquals(true, ArrayFilter::json(true));
        $this->assertEquals(1.4, ArrayFilter::json(1.4));
        $this->assertEquals('[1,2]', ArrayFilter::json([1, 2]));
        $this->assertEquals('{"a":1,"b":2}', ArrayFilter::json(['a' => 1, 'b' => 2]));
        $this->assertCommandOutput('{
    "a": 1,
    "b": 2
}', ArrayFilter::json(['a' => 1, 'b' => 2], true));
    }
}
