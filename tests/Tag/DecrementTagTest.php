<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\DecrementTag;

/**
 * DecrementTag class tests
 *
 * @group core
 * @group template
 */
class DecrementTagTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new DecrementTag('myname', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(DecrementTag::class, $b, 'name'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new DecrementTag('(+67888)', $tokens, $parser));
    }

    public function testRenderEmpty(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new DecrementTag('myvar', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEmpty($output);
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new DecrementTag('myvar', $tokens, $parser);
        $c = new Context(['myvar' => 123]);

        $b->render($c);
        $this->assertEquals(122, $c->getEnvironment('myvar'));

        $b->render($c);
        $this->assertEquals(121, $c->getEnvironment('myvar'));
    }
}
