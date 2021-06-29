<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\IncrementTag;

/**
 * IncrementTag class tests
 *
 * @group core
 * @group template
 */
class IncrementTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new IncrementTag('myname', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(IncrementTag::class, $b, 'name'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new IncrementTag('(+67888)', $tokens, $parser));
    }

    public function testRenderEmpty(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new IncrementTag('myvar', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEmpty($output);
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new IncrementTag('myvar', $tokens, $parser);
        $c = new Context(['myvar' => 123]);

        $b->render($c);
        $this->assertEquals(124, $c->getEnvironment('myvar'));

        $b->render($c);
        $this->assertEquals(125, $c->getEnvironment('myvar'));
    }
}
