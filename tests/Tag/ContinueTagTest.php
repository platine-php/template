<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\ContinueTag;

/**
 * ContinueTag class tests
 *
 * @group core
 * @group template
 */
class ContinueTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new ContinueTag('markup', $tokens, $parser);

        $c = new Context();

        $this->assertFalse($c->hasRegister('continue'));

        $b->render($c);
        $this->assertTrue($c->hasRegister('continue'));
    }
}
