<?php

declare(strict_types=1);

namespace Platine\Test\Template\Filter;

use ArrayIterator;
use Platine\PlatineTestCase;
use Platine\Template\Filter\StringFilter;
use Platine\Test\Fixture\StringFilterLengthTestClass;

/**
 * StringFilter class tests
 *
 * @group core
 * @group template
 */
class StringFilterTest extends PlatineTestCase
{

    public function testLengthTraversable(): void
    {
        $arrayIt = new ArrayIterator([1, 4, 5]);
        $resultIt = StringFilter::length($arrayIt);
        $this->assertEquals(3, $resultIt);
    }

    public function testLengthParamCannotUse(): void
    {
        $this->assertEquals(3, StringFilter::length(3));
    }

    public function testLengthArray(): void
    {
        $result = StringFilter::length([1, 4, 5]);
        $this->assertEquals(3, $result);
    }

    public function testLengthObjectHasMethodSize(): void
    {
        $result = StringFilter::length(new StringFilterLengthTestClass());
        $this->assertEquals(234, $result);
    }

    public function testLengthMultibyte(): void
    {
        global $mock_function_exists_to_true, $mock_mb_strlen;

        $mock_function_exists_to_true = true;
        $mock_mb_strlen = true;
        $result = StringFilter::length('foo');
        $this->assertEquals('foo', $result);
    }

    public function testLengthMultibyteNotExist(): void
    {
        global $mock_function_exists_to_false;

        $mock_function_exists_to_false = true;
        $result = StringFilter::length('foo');
        $this->assertEquals(3, $result);
    }

    public function testAppend(): void
    {
        $result = StringFilter::append('foo', 'bar');
        $this->assertEquals('foobar', $result);
        $this->assertEquals(3, StringFilter::append(3, 'foo'));
    }

    public function testPrepend(): void
    {
        $resultIt = StringFilter::prepend('foo', 'bar');
        $this->assertEquals('barfoo', $resultIt);
        $this->assertEquals(3, StringFilter::prepend(3, 'foo'));
    }

    public function testRemove(): void
    {
        $result = StringFilter::remove('foo', 'o');
        $this->assertEquals('f', $result);
        $this->assertEquals(3, StringFilter::remove(3, 'foo'));
    }

    public function testReplace(): void
    {
        $result = StringFilter::replace('foo', 'o', 'a');
        $this->assertEquals('faa', $result);
        $this->assertEquals(3, StringFilter::replace(3, 'foo', 'bar'));
    }

    public function testTruncate(): void
    {
        $result = StringFilter::truncate('foo', 2, '...');
        $this->assertEquals('fo...', $result);
        $this->assertEquals('foo', StringFilter::truncate('foo', 3, 'bar'));
        $this->assertEquals(3, StringFilter::truncate(3, 'foo', 'bar'));
    }

    public function testTruncateWord(): void
    {
        $result = StringFilter::truncateWord('foo bar baz', 2, '...');
        $this->assertEquals('foo bar...', $result);
        $this->assertEquals('foo', StringFilter::truncateWord('foo', 3, 'bar'));
        $this->assertEquals(3, StringFilter::truncateWord(3, 'foo', 'bar'));
    }

    public function testUpperMultibyte(): void
    {
        global $mock_function_exists_to_true, $mock_mb_strtoupper;

        $mock_function_exists_to_true = true;
        $mock_mb_strtoupper = true;

        $result = StringFilter::upper('foo');
        $this->assertEquals('foo', $result);
    }

    public function testUpperMultibyteNotFound(): void
    {
        global $mock_function_exists_to_false;

        $mock_function_exists_to_false = true;

        $result = StringFilter::upper('foo');
        $this->assertEquals('FOO', $result);
        $this->assertEquals(123, StringFilter::upper(123));
    }

    public function testLowerMultibyte(): void
    {
        global $mock_function_exists_to_true, $mock_mb_strtolower;

        $mock_function_exists_to_true = true;
        $mock_mb_strtolower = true;

        $result = StringFilter::lower('foo');
        $this->assertEquals('foo', $result);
    }

    public function testLowerMultibyteNotFound(): void
    {
        global $mock_function_exists_to_false;

        $mock_function_exists_to_false = true;

        $result = StringFilter::lower('FOO');
        $this->assertEquals('foo', $result);
        $this->assertEquals(123, StringFilter::lower(123));
    }

    public function testUrlEncode(): void
    {
        $result = StringFilter::urlEncode('foo@bar.com');
        $this->assertEquals('foo%40bar.com', $result);
        $this->assertEquals(123, StringFilter::urlEncode(123));
    }

    public function testUrlDecode(): void
    {
        $result = StringFilter::urlDecode('foo%40bar.com');
        $this->assertEquals('foo@bar.com', $result);
        $this->assertEquals(123, StringFilter::urlDecode(123));
    }

    public function testStringfy(): void
    {
        $result = StringFilter::stringfy(12);
        $this->assertEquals('12', $result);
        $this->assertEquals([1], StringFilter::stringfy([1]));
    }

    public function testSplit(): void
    {
        $result = StringFilter::split('1,2', ',');
        $this->assertEquals([1, 2], $result);
        $this->assertEquals(1, StringFilter::split(1, '1'));
    }

    public function testRaw(): void
    {
        $this->assertEquals(1, StringFilter::raw(1));
    }

    public function testEscape(): void
    {
        $result = StringFilter::escape('1<b>');
        $this->assertEquals('1&lt;b&gt;', $result);
        $this->assertEquals(1, StringFilter::escape(1));
    }

    public function testEscapeOnce(): void
    {
        $result = StringFilter::escapeOnce('1<b>');
        $this->assertEquals('1&lt;b&gt;', $result);
        $this->assertEquals(1, StringFilter::escapeOnce(1));
    }

    public function testDefaultValue(): void
    {
        $this->assertEquals(1, StringFilter::defaultValue(1, 1));
        $this->assertEquals('default', StringFilter::defaultValue('', 'default'));
        $this->assertEquals(true, StringFilter::defaultValue(false, true));
        $this->assertEquals(123, StringFilter::defaultValue(null, 123));
    }

    public function testJoin(): void
    {
        $result = StringFilter::join([1, 2, 3], '|');
        $this->assertEquals('1|2|3', $result);
        $this->assertEquals('foo', StringFilter::join('foo', 1));
        $this->assertEquals('4,5', StringFilter::join(new ArrayIterator([4, 5]), ','));
        $this->assertEquals(25, StringFilter::join(25, ','));
    }

    public function testCapitalize(): void
    {
        $this->assertEquals(1, StringFilter::capitalize(1));
        $this->assertEquals('Foo Bar', StringFilter::capitalize('foo bar'));
        $this->assertEquals('Foo', StringFilter::capitalize('foo'));
        $this->assertEquals('1Foo', StringFilter::capitalize('1foo'));
        $this->assertEquals('', StringFilter::capitalize(''));
    }

    public function testLstrip(): void
    {
        $this->assertEquals(1, StringFilter::lstrip(1));
        $this->assertEquals('foo', StringFilter::lstrip(' foo'));
        $this->assertEquals('foo ', StringFilter::lstrip('foo '));
    }

    public function testRstrip(): void
    {
        $this->assertEquals(1, StringFilter::rstrip(1));
        $this->assertEquals('foo', StringFilter::rstrip('foo '));
        $this->assertEquals(' foo', StringFilter::rstrip(' foo'));
    }

    public function testStrip(): void
    {
        $this->assertEquals(1, StringFilter::strip(1));
        $this->assertEquals('foo', StringFilter::strip(' foo '));
        $this->assertEquals('foo', StringFilter::strip(' foo'));
        $this->assertEquals('foo', StringFilter::strip('foo '));
    }

    public function testStripHtml(): void
    {
        $this->assertEquals(1, StringFilter::stripHtml(1));
        $this->assertEquals('foo', StringFilter::stripHtml('<b>foo</b>'));
    }

    public function testStripNewLine(): void
    {
        $this->assertEquals(1, StringFilter::stripNewLine(1));
        $this->assertEquals('foobarbaz', StringFilter::stripNewLine("foo\nbar\rbaz"));
        $this->assertEquals('foobarbaz', StringFilter::stripNewLine("foo\nbar\rbaz"));
    }
}
