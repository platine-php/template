<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Variable;
use Platine\Template\Tag\SetTag;
use Platine\Test\Fixture\CustomFilter;

/**
 * SetTag class tests
 *
 * @group core
 * @group template
 */
class SetTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SetTag('myname=1', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(SetTag::class, $b, 'variableName'));
        $this->assertInstanceOf(Variable::class, $this->getPropertyValue(SetTag::class, $b, 'variable'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new SetTag('(+', $tokens, $parser));
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SetTag('myname=1', $tokens, $parser);
        $c = new Context();
        $this->assertNull($c->get('myname'));
        $b->render($c);
        $this->assertEquals(1, $c->get('myname'));
    }

    public function testRenderUsingFilter(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new SetTag('myname = "foobar"|charAt: 3', $tokens, $parser);
        $c = new Context();
        $c->addFilter(CustomFilter::class);
        $this->assertNull($c->get('myname'));
        $b->render($c);
        $this->assertEquals('b', $c->get('myname'));
    }
}
