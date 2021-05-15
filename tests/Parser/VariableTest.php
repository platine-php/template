<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\PlatineTestCase;
use Platine\Template\Configuration;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Variable;

/**
 * Variable class tests
 *
 * @group core
 * @group template
 */
class VariableTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable('foobar', $parser);
        $this->assertEquals('foobar', $this->getPropertyValue(Variable::class, $v, 'markup'));
        $this->assertEquals($parser, $this->getPropertyValue(Variable::class, $v, 'parser'));
    }

    public function testConstructorUsingFilter(): void
    {
        $markup = 'bar|char';
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable($markup, $parser);
        $this->assertEquals($markup, $this->getPropertyValue(Variable::class, $v, 'markup'));
        $this->assertEquals($parser, $this->getPropertyValue(Variable::class, $v, 'parser'));

        $filters = $v->getFilters();
        $this->assertNotEmpty($filters);
        $this->assertCount(1, $filters);
        $this->assertIsArray($filters[0]);
        $this->assertEquals('char', $filters[0][0]);
        $this->assertEmpty($filters[0][1]);

        $this->assertEquals('bar', $v->getName());
    }

    public function testConstructorUsingFilterWithArgumentAndTagAttribute(): void
    {
        $markup = 'bar|charAt: limit:2, 3';
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable($markup, $parser);
        $this->assertEquals($markup, $this->getPropertyValue(Variable::class, $v, 'markup'));
        $this->assertEquals($parser, $this->getPropertyValue(Variable::class, $v, 'parser'));

        $filters = $v->getFilters();
        $this->assertNotEmpty($filters);
        $this->assertCount(1, $filters);
        $this->assertIsArray($filters[0]);
        $this->assertEquals('charAt', $filters[0][0]);
        $this->assertNotEmpty($filters[0][1]);
        $this->assertCount(2, $filters[0][1]);
        $this->assertEquals('3', $filters[0][1][0]);
        $this->assertIsArray($filters[0][1][1]);
        $this->assertCount(1, $filters[0][1][1]);
        $this->assertArrayHasKey('limit', $filters[0][1][1]);
        $this->assertEquals(2, $filters[0][1][1]['limit']);
    }

    public function testConstructorUsingAutoEscape(): void
    {
        $markup = 'bar|char';
        $cfg = $this->getMockInstance(Configuration::class, ['isAutoEscape' => true]);
        $parser = $this->getMockInstance(Parser::class, ['getConfig' => $cfg]);
        $v = new Variable($markup, $parser);
        $this->assertEquals($markup, $this->getPropertyValue(Variable::class, $v, 'markup'));
        $this->assertEquals($parser, $this->getPropertyValue(Variable::class, $v, 'parser'));

        $filters = $v->getFilters();
        $this->assertNotEmpty($filters);
        $this->assertCount(2, $filters);
        $this->assertIsArray($filters[0]);
        $this->assertIsArray($filters[1]);
        $this->assertEquals('char', $filters[0][0]);
        $this->assertEquals('escape', $filters[1][0]);
        $this->assertEmpty($filters[0][1]);
        $this->assertEmpty($filters[1][1]);
    }

    public function testConstructorUsingForceAutoEscapeToFalse(): void
    {
        $markup = 'bar|char|raw';
        $cfg = $this->getMockInstance(Configuration::class, ['isAutoEscape' => true]);
        $parser = $this->getMockInstance(Parser::class, ['getConfig' => $cfg]);
        $v = new Variable($markup, $parser);
        $this->assertEquals($markup, $this->getPropertyValue(Variable::class, $v, 'markup'));
        $this->assertEquals($parser, $this->getPropertyValue(Variable::class, $v, 'parser'));

        $filters = $v->getFilters();
        $this->assertNotEmpty($filters);
        $this->assertCount(2, $filters);
        $this->assertIsArray($filters[0]);
        $this->assertIsArray($filters[1]);
        $this->assertEquals('char', $filters[0][0]);
        $this->assertEquals('raw', $filters[1][0]);
        $this->assertEmpty($filters[0][1]);
        $this->assertEmpty($filters[1][1]);
    }

    public function testRenderSimple(): void
    {
        $context = $this->getMockInstance(Context::class, ['get' => 'var_value']);
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable('bar', $parser);
        $this->assertEquals('var_value', $v->render($context));
    }

    public function testRenderContextReturnNull(): void
    {
        $context = $this->getMockInstance(Context::class, ['get' => null]);
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable('bar', $parser);
        $this->assertNull($v->render($context));
    }

    public function testRenderUsingFilter(): void
    {
        $context = $this->getMockInstance(Context::class, [
            'get' => 'var_value',
            'invokeFilter' => 'baz'
         ]);
        $parser = $this->getMockInstance(Parser::class, []);
        $v = new Variable('bar|charAt: limit:2,4', $parser);
        $this->assertEquals('baz', $v->render($context));
    }
}
