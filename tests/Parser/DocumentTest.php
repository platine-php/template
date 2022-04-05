<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\NullCache;
use Platine\Template\Parser\Document;
use Platine\Template\Parser\Parser;
use Platine\Template\Template;

/**
 * Document class tests
 *
 * @group core
 * @group template
 */
class DocumentTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        $b = new Document($tokens, $parser);

        $this->assertEmpty($b->getNodeList());
    }

    public function testHasIncludesExtends(): void
    {
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = ['{% extends "master" %}'];
        $b = new Document($tokens, $parser);

        $this->assertTrue($b->hasIncludes());
    }

    public function testHasIncludesInclude(): void
    {
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = ['{% include "master" %}'];
        $b = new Document($tokens, $parser);

        $this->assertTrue($b->hasIncludes());
    }
}
