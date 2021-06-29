<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Parser\Lexer;

/**
 * Lexer class tests
 *
 * @group core
 * @group template
 */
class LexerTest extends PlatineTestCase
{

    public function testScan(): void
    {
        $l = new Lexer('/([a-z]+)/');
        $matches = $l->scan('foo');
        $this->assertIsArray($matches);
        $this->assertIsArray($matches[0]);
        $this->assertEquals('foo', $matches[0][0]);
    }

    public function testScanOnlyOneResult(): void
    {
        $l = new Lexer('/[a-z]+/');
        $matches = $l->scan('az');

        $this->assertIsArray($matches);
        $this->assertCount(1, $matches);
        $this->assertEquals('az', $matches[0]);
    }

    public function testMatch(): void
    {
        $l = new Lexer('/^([a-z]+)$/');
        $this->assertTrue($l->match('f'));
        $this->assertTrue($l->match('foo'));
        $this->assertFalse($l->match('1f12'));
        $this->assertFalse($l->match('Foo'));
    }

    public function testMatchAll(): void
    {
        $l = new Lexer('/^([a-z]+){1,3}$/');
        $this->assertTrue($l->matchAll('f'));
        $this->assertTrue($l->matchAll('foo'));
        $this->assertFalse($l->matchAll('1f12'));
        $this->assertFalse($l->matchAll('Foo'));
    }

    public function testSplitFailed(): void
    {
        global $mock_preg_split_to_false;

        $mock_preg_split_to_false = true;
        $l = new Lexer('/^([a-z]+)$/');

        $this->assertEmpty($l->split('Foo'));
    }

    public function testSplitSuccess(): void
    {
        $l = new Lexer('/^([a-z]+)$/');
        $result = $l->split('Foo');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Foo', $result[0]);
    }

    public function testStringRepresentation(): void
    {
        $l = new Lexer('([a-z]+)');
        $result = $l->__toString();
        $this->assertEquals('/\(\[a\-z\]\+\)/', $result);
    }

    public function testGetResults(): void
    {
        $l = new Lexer('/^([a-z]+)$/'); //will add / and /
        $l->match('foo');
        $this->assertEquals('foo', $l->getStringMatch(0));

        $this->assertCount(2, $l->getArrayMatch(-1));
        $this->assertEquals('foo', $l->getArrayMatch(0));
        $this->assertEquals('foo', $l->getArrayMatch(1));
        $this->assertEquals('foo', $l->getStringMatch(1));
        $this->assertEquals('foo', $l->getMixedMatch(1));
        $this->assertTrue($l->isMatchNotNull(1));
        //Key does not exist
        $this->assertEmpty($l->getArrayMatch(90));
        $this->assertEmpty($l->getStringMatch(90));
        $this->assertNull($l->getMixedMatch(90));
    }
}
