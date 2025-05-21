<?php

declare(strict_types=1);

namespace Platine\Test\Template\Filter;

use DateTime;
use Platine\Dev\PlatineTestCase;
use Platine\Template\Filter\DatetimeFilter;

/**
 * DatetimeFilter class tests
 *
 * @group core
 * @group template
 */
class DatetimeFilterTest extends PlatineTestCase
{
    public function testDateValueIsNotNumeric(): void
    {
        $this->assertEquals(2021, DatetimeFilter::date('2021-01-01', 'Y'));
    }

    public function testDateValueIsDateTime(): void
    {
        $this->assertEquals('2021', DatetimeFilter::date(new DateTime('20210101'), 'Y'));
    }

    public function testDateValueIsNumeric(): void
    {
        $this->assertEquals('1973-03-03', DatetimeFilter::date('100000000', 'Y-m-d'));
    }
}
