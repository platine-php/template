<?php

declare(strict_types=1);

namespace Platine\Test\Template\Filter;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Filter\NumberFilter;

/**
 * NumberFilter class tests
 *
 * @group core
 * @group template
 */
class NumberFilterTest extends PlatineTestCase
{

    public function testPlusParamsIsNotNumeric(): void
    {
        $this->assertEquals('a', NumberFilter::plus('a', 34));
        $this->assertEquals(34, NumberFilter::plus(34, 'b'));
    }

    public function testPlusOneOfParamIsFloat(): void
    {
        $this->assertEquals(2.2, NumberFilter::plus(1.1, 1.1));
        $this->assertEquals(2.55, NumberFilter::plus(1.55, 1));
    }

    public function testPlusInteger(): void
    {
        $this->assertEquals(12, NumberFilter::plus(7, '5'));
        $this->assertEquals(56, NumberFilter::plus('55', 1));
    }

    public function testMinusParamsIsNotNumeric(): void
    {
        $this->assertEquals('a', NumberFilter::minus('a', 34));
        $this->assertEquals(34, NumberFilter::minus(34, 'b'));
    }

    public function testMinusOneOfParamIsFloat(): void
    {
        $this->assertEquals(1.0, NumberFilter::minus(1.1, 0.1));
        $this->assertEquals(0.55, NumberFilter::minus(1.55, 1));
    }

    public function testMinusInteger(): void
    {
        $this->assertEquals(2, NumberFilter::minus(7, '5'));
        $this->assertEquals(54, NumberFilter::minus('55', 1));
    }

    public function testTimesParamsIsNotNumeric(): void
    {
        $this->assertEquals('a', NumberFilter::times('a', 34));
        $this->assertEquals(34, NumberFilter::times(34, 'b'));
    }

    public function testTimesOneOfParamIsFloat(): void
    {
        $this->assertEquals(3.0, NumberFilter::times(1.5, 2));
        $this->assertEquals(1.55, NumberFilter::times(1.55, 1));
    }

    public function testTimesInteger(): void
    {
        $this->assertEquals(35, NumberFilter::times(7, '5'));
        $this->assertEquals(110, NumberFilter::times('55', 2));
    }

    public function testModuloParamsIsNotNumeric(): void
    {
        $this->assertEquals('a', NumberFilter::modulo('a', 34));
        $this->assertEquals(34, NumberFilter::modulo(34, 'b'));
    }

    public function testModuloOneOfParamIsFloat(): void
    {
        $this->assertEquals(0.0, NumberFilter::modulo(1.1, 1.1));
        $this->assertEquals(0.55, NumberFilter::modulo(1.55, 1));
    }

    public function testModuloInteger(): void
    {
        $this->assertEquals(2.0, NumberFilter::modulo(7, '5'));
        $this->assertEquals(0.0, NumberFilter::modulo('55', 1));
    }

    public function testRoundParamsIsNotNumeric(): void
    {
        $this->assertEquals('a', NumberFilter::round('a', 34));
        $this->assertEquals(34, NumberFilter::round(34, 'b'));
    }

    public function testRoundOneOfParamIsFloat(): void
    {
        $this->assertEquals(1.1, NumberFilter::round(1.1, 1.1));
        $this->assertEquals(1.6, NumberFilter::round(1.55, 1));
    }
}
