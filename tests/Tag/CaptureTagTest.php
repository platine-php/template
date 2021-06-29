<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\CaptureTag;

/**
 * CaptureTag class tests
 *
 * @group core
 * @group template
 */
class CaptureTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endcapture %}'];
        $b = new CaptureTag('myname', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(CaptureTag::class, $b, 'variableName'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new CaptureTag('(+', $tokens, $parser));
    }

    public function testRender(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['tnh', '{% endcapture %}'];
        $b = new CaptureTag('myname', $tokens, $parser);
        $c = new Context();
        $this->assertNull($c->get('myname'));
        $b->render($c);
        $this->assertEquals('tnh', $c->get('myname'));
    }
}
