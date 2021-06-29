<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Exception\RenderException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\IfTag;
use Platine\Test\Fixture\IfTagObjectToString;
use Platine\Test\Fixture\IfTagObjectWithoutToString;

use function Platine\Test\Fixture\func_return_generator;

/**
 * IfTag class tests
 *
 * @group core
 * @group template
 */
class IfTagTest extends PlatineTestCase
{

    public function testRenderEmpty(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endif %}'];
        $b = new IfTag('nb', $tokens, $parser);
        $c = new Context();

        $output = $b->render($c);
        $this->assertEmpty($output);
    }

    public function testRenderSimple(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['4', '{% endif %}'];
        $b = new IfTag('nb', $tokens, $parser);
        $c = new Context(['nb' => 4]);

        $output = $b->render($c);
        $this->assertEquals(4, $output);
    }

    public function testRenderSyntaxErrorUnknownTags(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% if_foo %}', '4', '{% endif %}'];
        $b = new IfTag('nb', $tokens, $parser);
        $c = new Context(['nb' => 4]);

        $b->render($c);
    }

    public function testRenderElseIf(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% elseif nb == 41 %}', 'elseif result', '{% endif %}'];
        $b = new IfTag('nb < 40', $tokens, $parser);
        $c = new Context(['nb' => 41]);

        $output = $b->render($c);
        $this->assertEquals('elseif result', $output);
    }

    public function testRenderElse(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb < 40', $tokens, $parser);
        $c = new Context(['nb' => 41]);

        $output = $b->render($c);
        $this->assertEquals('else result', $output);
    }

    public function testRenderInArray(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb contains 4', $tokens, $parser);
        $c = new Context(['nb' => [41, 3, 5]]);

        $output = $b->render($c);
        $this->assertEquals('else result', $output);
    }

    public function testRenderUsingRightEmptyKeyworkWithArray(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endif %}'];
        $b = new IfTag('nb != empty', $tokens, $parser);
        $c = new Context(['nb' => [41, 3, 5]]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }

    public function testRenderUsingLeftEmptyKeyworkWithArray(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endif %}'];
        $b = new IfTag('empty == nb', $tokens, $parser);
        $c = new Context(['nb' => [41, 3, 5]]);

        $output = $b->render($c);
        $this->assertEquals('else value', $output);
    }

    public function testRenderObjectContainToString(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb == 4', $tokens, $parser);
        $c = new Context(['nb' => new IfTagObjectToString()]);

        $output = $b->render($c);
        $this->assertEquals('else result', $output);
    }

    public function testRenderObjectGenerator(): void
    {
        $generator = func_return_generator();

        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endif %}'];
        $b = new IfTag('nb', $tokens, $parser);
        $c = new Context(['nb' => $generator]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }


    public function testRenderCompositeOr(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb < 40 or nb >= 50', $tokens, $parser);
        $c = new Context(['nb' => 51]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }

    public function testRenderCompositeAnd(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb > 50 and nb <= 60', $tokens, $parser);
        $c = new Context(['nb' => 55]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }

    public function testRenderSyntaxError(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('', $tokens, $parser);
        $c = new Context(['nb' => 0]);

        $b->render($c);
    }

    public function testRenderNullWithNull(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('a == b', $tokens, $parser);
        $c = new Context(['a' => null, 'b' => null]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }

    public function testRenderNullNotEqualOther(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endif %}'];
        $b = new IfTag('a != b', $tokens, $parser);
        $c = new Context(['a' => 34, 'b' => null]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }

    public function testRenderNullEqualOther(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endif %}'];
        $b = new IfTag('a == b', $tokens, $parser);
        $c = new Context(['a' => 34, 'b' => null]);

        $output = $b->render($c);
        $this->assertEquals('else value', $output);
    }

    public function testRenderInvalidObject(): void
    {
        $this->expectException(RenderException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb', $tokens, $parser);
        $c = new Context(['nb' => new IfTagObjectWithoutToString()]);

        $b->render($c);
    }

    public function testRenderInvalidOperator(): void
    {
        $this->expectException(RenderException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endif %}'];
        $b = new IfTag('nb = 4', $tokens, $parser);
        $c = new Context(['nb' => 11]);

        $b->render($c);
    }
}
