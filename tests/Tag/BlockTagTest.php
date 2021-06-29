<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\BlockTag;

/**
 * BlockTag class tests
 *
 * @group core
 * @group template
 */
class BlockTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endblock %}'];
        $b = new BlockTag('myname', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(BlockTag::class, $b, 'blockName'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = ['{% endblock %}'];
        $b = new BlockTag('(+', $tokens, $parser);
    }
}
