<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\BreakTag;

/**
 * BreakTag class tests
 *
 * @group core
 * @group template
 */
class BreakTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new BreakTag('markup', $tokens, $parser);

        $c = new Context();

        $this->assertFalse($c->hasRegister('break'));

        $b->render($c);
        $this->assertTrue($c->hasRegister('break'));
    }
}
