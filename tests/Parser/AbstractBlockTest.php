<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use InvalidArgumentException;
use Platine\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Exception\RenderException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Template;
use Platine\Test\Fixture\CustomBlock;
use stdClass;

/**
 * AbstractBlock class tests
 *
 * @group core
 * @group template
 */
class AbstractBlockTest extends PlatineTestCase
{


    public function testDefault(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endtnh %}'];
        $b = new CustomBlock('myname', $tokens, $parser);

        $this->assertEquals('platine\test\fixture\customblock', $this->runPrivateProtectedMethod($b, 'getName'));
        $this->assertEquals('tnh', $this->runPrivateProtectedMethod($b, 'getTagName'));
        $this->assertEmpty($b->getNodeList());
    }

    public function testParseNodeIsNotTag(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $template = $this->getMockInstance(Template::class, ['getTags' => ['tnh' => stdClass::class]]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = ['{% tnh %}', '{% endtnh %}'];
        $b = new CustomBlock('(+', $tokens, $parser);
    }

    public function testParseTagNotProperlyTerminated(): void
    {
        $this->expectException(ParseException::class);
        $template = $this->getMockInstance(Template::class, ['getTags' => ['tnh' => CustomBlock::class]]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = ['{% tnh '];
        $b = new CustomBlock('(+', $tokens, $parser);
    }

    public function testRenderDirectArray(): void
    {
        $this->expectException(RenderException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{ tags }}', '{% endtnh %}'];
        $b = new CustomBlock('(+', $tokens, $parser);

        $c = new Context(['tags' => [1, 2, 4]]);
        $b->render($c);
    }

    public function testUsingElse(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% else %}', '{% endtnh %}'];
        $b = new CustomBlock('(+', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('ss', $output);
    }

    public function testUsingInvalidEndTag(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% end %}'];
        $b = new CustomBlock('(+', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('ss', $output);
    }

    public function testUsingVariableWasNotTerminated(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{ foo', '{% end %}'];
        $b = new CustomBlock('(+', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('ss', $output);
    }


    public function testParseUsingWhitespaceControl(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{{-foo-}}', 'tt-ss-', '{%-endtnh-%}'];
        $b = new CustomBlock('(+', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('tt-ss-', $output);
    }
}
