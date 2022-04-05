<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\DebugTag;

/**
 * DebugTag class tests
 *
 * @group core
 * @group template
 */
class DebugTagTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new DebugTag('myname', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(DebugTag::class, $b, 'value'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new DebugTag('', $tokens, $parser));
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new DebugTag('myvar', $tokens, $parser);
        $c = new Context(['myvar' => 123]);
        $output = $b->render($c);
        $this->assertEquals('<pre>123</pre>', $output);
    }
}
