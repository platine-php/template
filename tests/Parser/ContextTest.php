<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Exception\TemplateException;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Test\Fixture\ContextCountable;
use Platine\Test\Fixture\ContextDrop;
use Platine\Test\Fixture\ContextMethodCallGetMagicGet;
use Platine\Test\Fixture\ContextObjectToArray;
use Platine\Test\Fixture\ContextObjectToArrayInteger;
use Platine\Test\Fixture\ContextObjectToArrayNull;
use Platine\Test\Fixture\ContextObjectToObject;
use Platine\Test\Fixture\CustomFilter;
use stdClass;

/**
 * Context class tests
 *
 * @group core
 * @group template
 */
class ContextTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $c = new Context([], []);
        $this->assertInstanceOf(Context::class, $c);
    }

    public function testSetParser(): void
    {
        $parser = $this->getMockInstance(Parser::class);
        $c = new Context();
        $c->setParser($parser);

        $p = $this->getPropertyValue(Context::class, $c, 'parser');
        $this->assertInstanceOf(Parser::class, $p);
    }

    public function testSetTickCallback(): void
    {
        $c = new Context();

        $cb = $this->getPropertyValue(Context::class, $c, 'tickCallback');
        $this->assertEquals($cb, null);

        //callback
        $tcb = function () {
        };

        $c->setTickCallback($tcb);
        $this->assertEquals(
            $tcb,
            $this->getPropertyValue(Context::class, $c, 'tickCallback')
        );
    }

    public function testInvokeFilter(): void
    {
        $c = new Context();

        $c->addFilter(CustomFilter::class);
        $res = $c->invokeFilter('char', 'foobar', []);
        $this->assertEquals('f', $res);
    }

    public function testInvokeFilterStrictType(): void
    {
        $c = new Context();

        $c->addFilter(CustomFilter::class);
        $this->expectException(TemplateException::class);
        $res = $c->invokeFilter('strict_type', 'foobar', []);
    }

    public function testMerge(): void
    {
        $c = new Context(['myvar' => 'foo']);

        $assigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $assigns);
        $this->assertIsArray($assigns[0]);
        $this->assertCount(1, $assigns[0]);
        $this->assertArrayHasKey('myvar', $assigns[0]);
        $this->assertEquals('foo', $assigns[0]['myvar']);

        $c->merge(['foo' => 'bar']);
        $newAssigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $newAssigns);
        $this->assertIsArray($newAssigns[0]);
        $this->assertCount(2, $newAssigns[0]);
        $this->assertArrayHasKey('myvar', $newAssigns[0]);
        $this->assertArrayHasKey('foo', $newAssigns[0]);
        $this->assertEquals('foo', $newAssigns[0]['myvar']);
        $this->assertEquals('bar', $newAssigns[0]['foo']);
    }

    public function testPush(): void
    {
        $c = new Context(['myvar' => 'foo']);

        $assigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $assigns);
        $this->assertCount(1, $assigns[0]);

        $c->push();
        $newAssigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(2, $newAssigns);
        $this->assertIsArray($newAssigns[0]);
        $this->assertCount(0, $newAssigns[0]);
        $this->assertEmpty($newAssigns[0]);
    }

    public function testPop(): void
    {
        $c = new Context(['myvar' => 'foo']);

        $assigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $assigns);
        $this->assertCount(1, $assigns[0]);

        $c->push();
        $newAssigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(2, $newAssigns);
        $this->assertIsArray($newAssigns[0]);
        $this->assertCount(0, $newAssigns[0]);
        $this->assertEmpty($newAssigns[0]);

        $c->pop();
        $newAssignsPop = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $newAssignsPop);
        $this->assertIsArray($newAssignsPop[0]);
        $this->assertCount(1, $newAssignsPop[0]);
        $this->assertArrayHasKey('myvar', $newAssignsPop[0]);
        $this->assertEquals('foo', $newAssignsPop[0]['myvar']);
    }

    public function testPopNotEnoughElement(): void
    {
        $c = new Context(['myvar' => 'foo']);

        $this->expectException(TemplateException::class);
        $c->pop();
    }

    public function testSetGlobal(): void
    {
        $c = new Context(['foo' => 'bar']);

        $assigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $assigns);
        $this->assertCount(1, $assigns[0]);
        $this->assertArrayHasKey('foo', $assigns[0]);
        $this->assertEquals('bar', $assigns[0]['foo']);

        $c->set('myvar', 'foobar', true);
        $newAssigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $newAssigns);
        $this->assertIsArray($newAssigns[0]);
        $this->assertCount(2, $newAssigns[0]);
        $this->assertArrayHasKey('myvar', $newAssigns[0]);
        $this->assertArrayHasKey('foo', $newAssigns[0]);
        $this->assertEquals('foobar', $newAssigns[0]['myvar']);
        $this->assertEquals('bar', $newAssigns[0]['foo']);
    }

    public function testSetNotGlobal(): void
    {
        $c = new Context(['foo' => 'bar']);

        $assigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $assigns);
        $this->assertCount(1, $assigns[0]);
        $this->assertArrayHasKey('foo', $assigns[0]);
        $this->assertEquals('bar', $assigns[0]['foo']);

        $c->set('myvar', 'foobar', false);
        $newAssigns = $this->getPropertyValue(Context::class, $c, 'assigns');
        $this->assertCount(1, $newAssigns);
        $this->assertIsArray($newAssigns[0]);
        $this->assertCount(2, $newAssigns[0]);
        $this->assertArrayHasKey('myvar', $newAssigns[0]);
        $this->assertArrayHasKey('foo', $newAssigns[0]);
        $this->assertEquals('foobar', $newAssigns[0]['myvar']);
        $this->assertEquals('bar', $newAssigns[0]['foo']);
    }

    public function testHasKey(): void
    {
        $c = new Context(['foo' => 'bar']);

        $this->assertTrue($c->hasKey('foo'));
        $this->assertFalse($c->hasKey('bar'));
    }

    public function testRegister(): void
    {
        $c = new Context([]);

        $this->assertFalse($c->hasRegister('foo'));
        $c->setRegister('foo', 'bar');
        $this->assertTrue($c->hasRegister('foo'));
        $this->assertEquals('bar', $c->getRegister('foo'));
        $this->assertNull($c->getRegister('bar'));
        $c->clearRegister('foo');
        $this->assertFalse($c->hasRegister('foo'));
    }

    public function testEnvironment(): void
    {
        $c = new Context([]);

        $this->assertFalse($c->hasEnvironment('foo'));
        $c->setEnvironment('foo', 'bar');
        $this->assertTrue($c->hasEnvironment('foo'));
        $this->assertEquals('bar', $c->getEnvironment('foo'));
        $this->assertNull($c->getEnvironment('bar'));
    }

    public function testTick(): void
    {
        $c = new Context([]);

        $tcb = function (Context $c) {
            $c->setEnvironment('foo', 'bar');
        };

        $this->assertFalse($c->hasEnvironment('foo'));
        $c->tick(); //no callback
        $this->assertFalse($c->hasEnvironment('foo'));
        $c->setTickCallback($tcb);
        $c->tick(); //callback set
        $this->assertTrue($c->hasEnvironment('foo'));
    }

    public function testGetSimple(): void
    {
        $c = new Context([]);

        $this->assertNull($c->get('foo')); //no exits
        $this->assertNull($c->get('null')); // null as string
        $this->assertFalse($c->get('false')); // false as string
        $this->assertTrue($c->get('true')); // true as string
        $this->assertEquals('foobar', $c->get('\'foobar\'')); //single quote
        $this->assertEquals('foobar', $c->get('"foobar"')); //double quote
        $this->assertEquals('123', $c->get('123')); //numeric as string
        $this->assertEquals('12.3', $c->get('12.3')); //numeric float as string
    }

    public function testGetArrayIndexed(): void
    {
        $c = new Context([
            'arr' => ['foo', 'bar', 'key' => 'val'],
            'arrkey' => 'key',
        ]);

        $this->assertEquals('foo', $c->get('arr.0'));
        $this->assertEquals('foo', $c->get('arr[0]'));
        $this->assertEquals('val', $c->get('arr.key'));
        //using variable to reseolve array key
        $this->assertEquals('val', $c->get('arr[arrkey]'));
        $this->assertEquals('bar', $c->get('arr.1'));
        $this->assertEquals('bar', $c->get('arr[1]'));
    }

    public function testGetArrayShortcutMethod(): void
    {
        $c = new Context([
            'arr' => ['foo', 'bar'],
        ]);

        $this->assertEquals(3, $c->get('arr.0.size'));
        $this->assertNull($c->get('arr.0.foo.key')); //not found
        $this->assertEquals('foo', $c->get('arr.first'));
        $this->assertEquals(2, $c->get('arr.size'));
        $this->assertEquals('bar', $c->get('arr.last'));
        $this->assertNull($c->get('arr.not.found.array.key'));
    }

    public function testGetObjectOfCountable(): void
    {
        $c = new Context([
            'c' => new ContextCountable(),
        ]);

        $this->assertEquals(100, $c->get('c.size'));
    }

    public function testGetCompositeUsingEnvironment(): void
    {
        $c = new Context([
            'myvar' => 'foo'
        ]);

        $c->setEnvironment('myenv', 10);

        $this->assertNull($c->get('myenv.myvar'));
    }

    public function testGetCompositeObjectContaintsToObjectOrToArray(): void
    {
        $to = new ContextObjectToObject();
        $ta = new ContextObjectToArray();
        $c = new Context([
            'to' => $to,
            'ta' => $ta,
        ]);

        $this->assertEquals(ContextObjectToObject::class, $c->get('to.toObject'));
        $this->assertNull($c->get('to.toObject.not.found.key'));
        $this->assertEquals(ContextDrop::class, $c->get('ta.bar.foo'));
        $this->assertNull($c->get('ta.bar.baz'));
        $this->assertEquals(ContextObjectToObject::class, $c->get('ta.myarr.toObject'));
    }

    public function testGetCompositeNull(): void
    {
        $ta = new ContextObjectToArrayNull();
        $c = new Context([
            'myvar' => $ta
        ]);

        $this->assertNull($c->get('myvar.b.exist.key'));
    }

    public function testGetCompositeToArrayReturnNull(): void
    {
        $a = new stdClass();
        $a->baz = new ContextObjectToArrayInteger();
        $a->foo = 'bar';
        $b = new stdClass();
        $b->a = $a;

        $o = new stdClass();
        $o->b = $b;
        $c = new Context(['o' => $o]);

        $this->assertNull($c->get('o.b.a.baz.c')); //not exits
        $this->assertEquals('bar', $c->get('o.b.a.foo'));
    }

    public function testGetCompositeUsingDrop(): void
    {
        $o = new ContextDrop();
        $c = new Context([
            'myvar' => $o
        ]);

        $this->assertEquals(ContextDrop::class, $c->get('myvar.foo'));
        $this->assertNull($c->get('myvar.method.not.found'));
    }

    public function testGetCompositeObjectMethodCallAndMagicGet(): void
    {
        $o = new ContextMethodCallGetMagicGet();
        $c = new Context([
            'myvar' => $o
        ]);

        $this->assertEquals(ContextMethodCallGetMagicGet::class, $c->get('myvar.mymethod'));
        $this->assertEquals('bar_foo', $c->get('myvar.bar')); //using __get
        $this->assertNull($c->get('myvar.foo')); //using __get
    }

    public function testGetObject(): void
    {
        $a = new stdClass();
        $a->baz = new ContextObjectToObject();
        $a->foo = 'bar';
        $b = new stdClass();
        $b->a = $a;

        $o = new stdClass();
        $o->b = $b;
        $c = new Context(['o' => $o]);

        $this->assertNull($c->get('o.b.a.foo.c')); //not exits
        $this->assertEquals('bar', $c->get('o.b.a.foo'));
        $this->assertNull($c->get('o.b.a.bar.c'));
        $this->assertInstanceOf(stdClass::class, $c->get('o.b.a.baz'));
    }

    public function testGetExplodeReturnFalse(): void
    {
        global $mock_explode_false;

        $mock_explode_false = true;

        $c = new Context(['o' => 'val']);

        $this->assertNull($c->get('o'));
    }
}
