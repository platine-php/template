<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\RawTag;

/**
 * RawTag class tests
 *
 * @group core
 * @group template
 */
class RawTagTest extends PlatineTestCase
{



    public function testParse(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endraw %}'];
        $b = new RawTag('foo value', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('tnh', $output);
    }
}
