<?php

declare(strict_types=1);

namespace Platine\Test\Template\Tag;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Cache\NullCache;
use Platine\Template\Exception\ParseException;
use Platine\Template\Loader\StringLoader;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Document;
use Platine\Template\Parser\Parser;
use Platine\Template\Tag\ExtendsTag;
use Platine\Template\Template;

/**
 * ExtendsTag class tests
 *
 * @group core
 * @group template
 */
class ExtendsTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = [];
        $b = new ExtendsTag('"myname"', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(ExtendsTag::class, $b, 'templateName'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new ExtendsTag('(+', $tokens, $parser));
    }

    public function testParse(): void
    {
        $loader = new StringLoader([
            'master' => '{% block master %}Master content{% endblock %}',
            'header' => '{% extends "master" %}'
                        . '{% block master %}Master content {% endblock %}',
            'child' => '{% extends "header" %}'
                        . '{% block header %}My own header {% endblock %}'
                        . '{% block master %}My own block{% endblock %}',
        ]);

        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"child"', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('My own block', $output);
    }

    public function testParseKeepParent(): void
    {
        $loader = new StringLoader([
            'master' => '{% block master %}Master content{% endblock %}',
            'header' => '{% extends "master" %}'
                        . '{% block header %}Master content {% endblock %}',
        ]);

        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"header"', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('Master content', $output);
    }

    public function testParseUsingCachedDocument(): void
    {
        $loader = new StringLoader([
            'master' => '{% block master %}Master content{% endblock %}',
        ]);
        $document = $this->getMockInstance(Document::class, ['render' => 'doc render mock']);
        $cache = $this->getMockInstance(NullCache::class, ['read' => $document]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"master"', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('doc render mock', $output);
    }

    public function testHasIncludeUsingDocument(): void
    {
        $loader = new StringLoader([
            'page' => '{% block master %}Master content{% endblock %}'
        ]);

        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"page"', $tokens, $parser);

        $this->assertTrue($b->hasIncludes());
    }

    public function testHasIncludeUsingDocumentNoCached(): void
    {
        $loader = new StringLoader([
            'page' => 'Master content'
        ]);

        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"page"', $tokens, $parser);

        $this->assertTrue($b->hasIncludes());
    }

    public function testHasIncludeUsingDocumentWithCache(): void
    {
        $loader = new StringLoader([
            'page' => 'Master content'
        ]);

        $cache = $this->getMockInstance(NullCache::class, ['read' => false, 'exists' => true]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(
            Parser::class,
            [
                'getTemplate' => $template,
                'getLoader' => $loader,
            ],
            ['tokenize']
        );
        $tokens = [];
        $b = new ExtendsTag('"page"', $tokens, $parser);

        $this->assertFalse($b->hasIncludes());
    }
}
