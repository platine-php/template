<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\CycleTag;

/**
 * CycleTag class tests
 *
 * @group core
 * @group template
 */
class CycleTagTest extends PlatineTestCase
{

    public function testConstructorSimple(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcycle %}'];
        $b = new CycleTag('one,two,three', $tokens, $parser);

        $this->assertEquals('\'onetwothree\'', $this->getPropertyValue(CycleTag::class, $b, 'name'));

        $vars = $this->getPropertyValue(CycleTag::class, $b, 'variables');
        $this->assertCount(3, $vars);
        $this->assertEquals('one', $vars[0]);
        $this->assertEquals('two', $vars[1]);
        $this->assertEquals('three', $vars[2]);
    }

    public function testConstructorNamed(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcycle %}'];
        $b = new CycleTag('tnh: one,two,three', $tokens, $parser);

        $this->assertEquals('tnh', $this->getPropertyValue(CycleTag::class, $b, 'name'));

        $vars = $this->getPropertyValue(CycleTag::class, $b, 'variables');
        $this->assertCount(3, $vars);
        $this->assertEquals('one', $vars[0]);
        $this->assertEquals('two', $vars[1]);
        $this->assertEquals('three', $vars[2]);
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new CycleTag('', $tokens, $parser));
    }

    public function testRenderEmpty(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endcycle %}'];
        $b = new CycleTag('myname', $tokens, $parser);
        $c = new Context();

        $output = $b->render($c);
        $this->assertEmpty($output);
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcycle %}'];
        $b = new CycleTag('tnh: one,two,three', $tokens, $parser);
        $c = new Context();

        $c->setRegister('cycle', ['tnh' => 1]);
        $c->set('tnh', 'tnh');
        $c->set('two', 'two');

        $output = $b->render($c);
        $this->assertEquals('two', $output);
    }
}
