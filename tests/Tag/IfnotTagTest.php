<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\IfnotTag;

/**
 * IfnotTag class tests
 *
 * @group core
 * @group template
 */
class IfnotTagTest extends PlatineTestCase
{


    public function testRenderElse(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else result', '{% endifnot %}'];
        $b = new IfnotTag('nb < 40', $tokens, $parser);
        $c = new Context(['nb' => 41]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }


    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['true value', '{% else %}', 'else value', '{% endifnot %}'];
        $b = new IfnotTag('nb < 40', $tokens, $parser);
        $c = new Context(['nb' => 51]);

        $output = $b->render($c);
        $this->assertEquals('true value', $output);
    }
}
