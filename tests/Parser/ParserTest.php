<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\NullCache;
use Platine\Template\Configuration;
use Platine\Template\Parser\Document;
use Platine\Template\Parser\Parser;
use Platine\Template\Template;

/**
 * Parser class tests
 *
 * @group core
 * @group template
 */
class ParserTest extends PlatineTestCase
{
    public function testGetConfig(): void
    {
        $template = $this->getMockInstance(Template::class);
        $b = new Parser($template);

        $this->assertInstanceOf(Configuration::class, $b->getConfig());
    }

    public function testParseWithoutCache(): void
    {
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $b = new Parser($template);

        $b->parse('myname');
        $this->assertInstanceOf(Document::class, $this->getPropertyValue(Parser::class, $b, 'root'));
    }

    public function testParseUsingCache(): void
    {
        $document = $this->getMockInstance(Document::class, ['render' => 'doc render mock']);
        $cache = $this->getMockInstance(NullCache::class, ['read' => $document]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $b = new Parser($template);

        $b->parse('myname');
        $this->assertInstanceOf(Document::class, $this->getPropertyValue(Parser::class, $b, 'root'));
        $this->assertEquals($document, $this->getPropertyValue(Parser::class, $b, 'root'));
    }
}
