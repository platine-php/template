<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\CaseTag;

/**
 * CaseTag class tests
 *
 * @group core
 * @group template
 */
class CaseTagTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcase %}'];
        $b = new CaseTag('nb', $tokens, $parser);

        $this->assertEquals('nb', $this->getPropertyValue(CaseTag::class, $b, 'left'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcase %}'];
        (new CaseTag('', $tokens, $parser));
    }

    public function testRenderEmpty(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcase %}'];
        $b = new CaseTag('nb', $tokens, $parser);
        $c = new Context();

        $output = $b->render($c);
        $this->assertEmpty($output);
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% when 4 %}', '4', '{% endcase %}'];
        $b = new CaseTag('nb 4', $tokens, $parser);
        $c = new Context(['nb' => 4]);

        $output = $b->render($c);
        $this->assertEquals(4, $output);
    }

    public function testRenderWhenSyntaxError(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% when %}', '4', '{% endcase %}'];
        $b = new CaseTag('nb 4', $tokens, $parser);
        $c = new Context(['nb' => 4]);

        $b->render($c);
    }

    public function testRenderSyntaxErrorUnknownTags(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% when_foo %}', '4', '{% endcase %}'];
        $b = new CaseTag('nb 4', $tokens, $parser);
        $c = new Context(['nb' => 4]);

        $b->render($c);
    }

    public function testRenderElse(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% when 4 %}', '4', '{% else %}', 'else result', '{% endcase %}'];
        $b = new CaseTag('nb 4', $tokens, $parser);
        $c = new Context(['nb' => 41]);

        $output = $b->render($c);
        $this->assertEquals('else result', $output);
    }
}
