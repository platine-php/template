<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Drop;
use Platine\Test\Fixture\DropTestClass;

/**
 * Drop class tests
 *
 * @group core
 * @group template
 */
class DropTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $d = new DropTestClass();
        $this->assertInstanceOf(Drop::class, $d);
    }

    public function testDefaultValues(): void
    {
        $d = new DropTestClass();
        $this->assertTrue($d->hasKey('foo'));
        $this->assertNull($this->runPrivateProtectedMethod(
            $d,
            'beforeMethod',
            ['foo']
        ));
        $this->assertInstanceOf(Drop::class, $d->toObject());
        $this->assertEquals(DropTestClass::class, $d->__toString());
    }

    public function testSetContext(): void
    {
        $context = $this->getMockInstance(Context::class);
        $d = new DropTestClass();
        $d->setContext($context);

        $c = $this->getPropertyValue(Drop::class, $d, 'context');
        $this->assertInstanceOf(Context::class, $c);
        $this->assertEquals($context, $c);
    }

    public function testInvokeDrop(): void
    {
        $d = new DropTestClass();

        $this->assertEquals(DropTestClass::class, $d->invokeDrop('myMethod'));
    }
}
