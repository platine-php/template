<?php

declare(strict_types=1);

namespace Platine\Test\Template\Filter;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Filter\HtmlFilter;

/**
 * HtmlFilter class tests
 *
 * @group core
 * @group template
 */
class HtmlFilterTest extends PlatineTestCase
{

    public function testNl2brParamsIsNotString(): void
    {
        $this->assertEquals(23, HtmlFilter::nl2br(23));
    }

    public function testNl2brDefault(): void
    {
        $this->assertEquals("foo<br />\nbar", HtmlFilter::nl2br("foo\nbar"));
    }
}
