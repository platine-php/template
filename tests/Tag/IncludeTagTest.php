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
use Platine\Template\Tag\IncludeTag;
use Platine\Template\Template;

/**
 * IncludeTag class tests
 *
 * @group core
 * @group template
 */
class IncludeTagTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $cache = $this->getMockInstance(NullCache::class, ['read' => false]);
        $template = $this->getMockInstance(Template::class, ['getCache' => $cache]);
        $parser = $this->getMockInstance(Parser::class, ['getTemplate' => $template]);
        $tokens = [];
        $b = new IncludeTag('"myname"', $tokens, $parser);

        $this->assertEquals('myname', $this->getPropertyValue(IncludeTag::class, $b, 'templateName'));
        $this->assertFalse($this->getPropertyValue(IncludeTag::class, $b, 'isCollection'));
        $this->assertEmpty($this->getPropertyValue(IncludeTag::class, $b, 'variable'));
    }

    public function testConstructorInvalidSyntax(): void
    {
        $this->expectException(ParseException::class);
        $parser = $this->getMockInstance(Parser::class);
        $tokens = [];
        (new IncludeTag('', $tokens, $parser));
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
        $b = new IncludeTag('"child"', $tokens, $parser);

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
        $b = new IncludeTag('"header"', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('Master content', $output);
    }

    public function testRenderSimple(): void
    {
        $loader = new StringLoader([
            'header' => 'My include content',
            'page' => '{% include "header" %}<br />'
                        . 'My own content'
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
        $b = new IncludeTag('page', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('My include content<br />My own content', $output);
    }

    public function testRenderWithVariable(): void
    {
        $loader = new StringLoader([
            'header' => 'My include content {{ foo }}',
            'page' => '{% include "header" with foo:"bar" %}<br />'
                        . 'My own content'
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
        $b = new IncludeTag('page', $tokens, $parser);

        $c = new Context();

        $output = $b->render($c);

        $this->assertEquals('My include content bar<br />My own content', $output);
    }

    public function testRenderWithCollectionVariable(): void
    {
        $loader = new StringLoader([
            'header' => 'My array: {{ header }}<br />',
            'page' => '{% include "header" for tags %}<br />'
                        . 'My own content'
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
        $b = new IncludeTag('page', $tokens, $parser);

        $c = new Context(['tags' => [1, 2]]);

        $output = $b->render($c);

        $this->assertEquals(
            'My array: 1<br />My array: 2<br /><br />My own content',
            $output
        );
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
        $b = new IncludeTag('"master"', $tokens, $parser);

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
        $b = new IncludeTag('"page"', $tokens, $parser);

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
        $b = new IncludeTag('"page"', $tokens, $parser);

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
        $b = new IncludeTag('"page"', $tokens, $parser);

        $this->assertFalse($b->hasIncludes());
    }
}
