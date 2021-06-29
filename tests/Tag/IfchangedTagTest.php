<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\IfchangedTag;

/**
 * IfchangedTag class tests
 *
 * @group core
 * @group template
 */
class IfchangedTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endifchanged %}'];
        $b = new IfchangedTag('myname', $tokens, $parser);

        $this->assertEmpty($this->getPropertyValue(IfchangedTag::class, $b, 'lastValue'));
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endifchanged %}'];
        $b = new IfchangedTag('foo value', $tokens, $parser);
        $c = new Context();
        $output = $b->render($c);
        $this->assertEquals('tnh', $output);
        $outputEmpty = $b->render($c);
        $this->assertEmpty($outputEmpty);
    }
}
