<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use ArrayIterator;
use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\ForTag;

use function Platine\Test\Fixture\func_return_generator;

/**
 * ForTag class tests
 *
 * @group core
 * @group template
 */
class ForTagTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endfor %}'];
        $b = new ForTag('i in (1..4)', $tokens, $parser);

        $this->assertEquals('i', $this->getPropertyValue(ForTag::class, $b, 'variableName'));
        $this->assertEquals('i-digit', $this->getPropertyValue(ForTag::class, $b, 'name'));
        $this->assertEquals('1', $this->getPropertyValue(ForTag::class, $b, 'start'));
        $this->assertEquals('4', $this->getPropertyValue(ForTag::class, $b, 'collectionName'));
    }

    public function testConstructorCollection(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endfor %}'];
        $b = new ForTag('i in tags', $tokens, $parser);

        $this->assertEquals('i', $this->getPropertyValue(ForTag::class, $b, 'variableName'));
        $this->assertEquals('i-tags', $this->getPropertyValue(ForTag::class, $b, 'name'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endfor %}'];
        $b = new ForTag('(+', $tokens, $parser);
    }

    public function testRenderDigit(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in (1..4)', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('1234', $output);
    }

    public function testRenderDigitUsingVariable(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in (1..a)', $tokens, $parser);

        $c = new Context(['a' => 2]);

        $output = $b->render($c);

        $this->assertEquals('12', $output);
    }

    public function testRenderBreak(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% break %}', '{% endfor %}'];
        $b = new ForTag('i in (1..4)', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('1', $output);
    }

    public function testRenderContinue(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% continue %}', '{% endfor %}'];
        $b = new ForTag('i in (1..4)', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('1234', $output);
    }

    public function testRenderCollection(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('348', $output);
    }

    public function testRenderCollectionUsingTraversable(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context(['a' => new ArrayIterator([3, 4, 8])]);

        $output = $b->render($c);

        $this->assertEquals('348', $output);
    }

    public function testRenderCollectionUsingGenerator(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context(['a' => func_return_generator()]);

        $output = $b->render($c);

        $this->assertEquals('012', $output);
    }

    public function testRenderCollectionWithGeneratorNotValid(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);
        $g = func_return_generator();
        foreach ($g as $i) {
        }
        //Generator is closed
        $c = new Context(['a' => $g]);

        $output = $b->render($c);

        $this->assertEmpty($output);
    }

    public function testRenderCollectionIsNull(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEmpty($output);
    }

    public function testRenderCollectionOffset(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a offset:1', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('48', $output);
    }

    public function testRenderCollectionOffsetContinueNoContext(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a offset:continue', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('348', $output);
    }

    public function testRenderCollectionOffsetContinueWithContext(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a offset:continue', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]], ['for' => ['i-a' => 2]]);

        $output = $b->render($c);

        $this->assertEquals('8', $output);
    }

    public function testRenderCollectionLimit(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a limit:1', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('3', $output);
    }

    public function testRenderCollectionOffsetLimitNoData(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% endfor %}'];
        $b = new ForTag('i in a offset:4', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEmpty($output);
    }

    public function testRenderCollectionBreak(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% break %}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('3', $output);
    }

    public function testRenderCollectionContinue(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{i}}', '{% continue %}', '{% endfor %}'];
        $b = new ForTag('i in a', $tokens, $parser);

        $c = new Context(['a' => [3, 4, 8]]);

        $output = $b->render($c);

        $this->assertEquals('348', $output);
    }
}
