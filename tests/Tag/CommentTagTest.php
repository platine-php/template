<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\CommentTag;

/**
 * CommentTag class tests
 *
 * @group core
 * @group template
 */
class CommentTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcomment %}'];
        $b = new CommentTag('foobar', $tokens, $parser);

        $c = new Context();
        $output = $b->render($c);
        $this->assertEmpty($output);
    }
}
