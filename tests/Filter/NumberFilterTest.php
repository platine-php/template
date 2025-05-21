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
    public function testPlusOneOfParamIsFloat(): void
    {
        $this->assertEquals(2.2, NumberFilter::plus(1.1, 1.1));
        $this->assertEquals(2.55, NumberFilter::plus(1.55, 1));
    }

    public function testPlusInteger(): void
    {
        $this->assertEquals(12, NumberFilter::plus(7, 5));
        $this->assertEquals(56, NumberFilter::plus(55, 1));
    }

    public function testMinusOneOfParamIsFloat(): void
    {
        $this->assertEquals(1.0, NumberFilter::minus(1.1, 0.1));
        $this->assertEquals(0.55, NumberFilter::minus(1.55, 1));
    }

    public function testMinusInteger(): void
    {
        $this->assertEquals(2, NumberFilter::minus(7, 5));
        $this->assertEquals(53.5, NumberFilter::minus(55.0, 1.5));
    }

    public function testTimesOneOfParamIsFloat(): void
    {
        $this->assertEquals(3.0, NumberFilter::times(1.5, 2));
        $this->assertEquals(1.55, NumberFilter::times(1.55, 1));
    }

    public function testTimesInteger(): void
    {
        $this->assertEquals(35, NumberFilter::times(7, 5));
        $this->assertEquals(110, NumberFilter::times(55.0, 2));
    }

    public function testModuloOneOfParamIsFloat(): void
    {
        $this->assertEquals(0.0, NumberFilter::modulo(1.1, 1.1));
        $this->assertEquals(0.55, NumberFilter::modulo(1.55, 1));
    }

    public function testModuloInteger(): void
    {
        $this->assertEquals(2.0, NumberFilter::modulo(7, 5));
        $this->assertEquals(0.0, NumberFilter::modulo(55, 1));
    }

    public function testRoundOneOfParamIsFloat(): void
    {
        $this->assertEquals(1.1, NumberFilter::round(1.1, 2));
        $this->assertEquals(1.6, NumberFilter::round(1.55, 1));
    }

    public function testDivWrongParams(): void
    {
        $this->assertEquals(4.1, NumberFilter::div(4.1, 0));
    }

    public function testDivSuccess(): void
    {
        $this->assertEquals(2, NumberFilter::div(4, 2));
        $this->assertEquals(1, NumberFilter::div(5, 5));
        $this->assertEquals(3.9, NumberFilter::div(19.5, 5));
    }

    public function testNumberFormat(): void
    {
        $this->assertEquals('4', NumberFilter::format('4'));
        $this->assertEquals('4,000', NumberFilter::format('4000'));
        $this->assertEquals('4,000.0000', NumberFilter::format('4000', 4));
        $this->assertEquals('4,000-0000', NumberFilter::format('4000', 4, '-'));
        $this->assertEquals('4 000-0000', NumberFilter::format('4000', 4, '-', ' '));
        $this->assertEquals('4a000', NumberFilter::format('4a000'));
    }

    public function testFormatMoney(): void
    {
        $this->assertEquals('4', NumberFilter::formatMoney('4'));
        $this->assertEquals('4,000', NumberFilter::formatMoney('4000'));
        $this->assertEquals('4,000', NumberFilter::formatMoney('4000', 4));
        $this->assertEquals('4,000', NumberFilter::formatMoney('4000', 4, '-'));
        $this->assertEquals('4 000', NumberFilter::formatMoney('4000', 4, '-', ' '));
        $this->assertEquals('4a000', NumberFilter::formatMoney('4a000'));
    }

    public function testNumberToString(): void
    {
        $this->assertEquals('4', NumberFilter::numberToString('4'));
        $this->assertEquals('4', NumberFilter::numberToString(4));
        $this->assertEquals('4000', NumberFilter::numberToString('4000'));
        $this->assertEquals('12.89', NumberFilter::numberToString('12,89'));
        $this->assertEquals('4a000', NumberFilter::numberToString('4a000'));
        $this->assertEquals('0.00004', NumberFilter::numberToString(4.3E-5));
        $this->assertEquals('0.00004', NumberFilter::numberToString('4.3E-5'));
        $this->assertEquals('4.00000', NumberFilter::numberToString('4,3E-5'));
        $this->assertEquals('0.00004', NumberFilter::numberToString('0,00004'));
        $this->assertEquals('45.004', NumberFilter::numberToString('45,004'));
    }

    public function testSizeFormat(): void
    {
        $this->assertEquals('4B', NumberFilter::sizeFormat('4'));
        $this->assertEquals('0', NumberFilter::sizeFormat('0'));
        $this->assertEquals('3.91K', NumberFilter::sizeFormat('4000'));
        $this->assertEquals('40B', NumberFilter::sizeFormat('40'));
        $this->assertEquals('4K', NumberFilter::sizeFormat('4000', 0));
        $this->assertEquals('4.2969K', NumberFilter::sizeFormat('4400', 4));
        $this->assertEquals('4.3G', NumberFilter::sizeFormat('4566576000', 1));
        $this->assertEquals('4.153T', NumberFilter::sizeFormat('4566789576000', 3));
        $this->assertEquals('4a000', NumberFilter::sizeFormat('4a000'));
    }
}
