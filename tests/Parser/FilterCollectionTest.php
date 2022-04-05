<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\Dev\PlatineTestCase;
use Platine\Template\Parser\FilterCollection;
use Platine\Test\Fixture\CustomFilter;

/**
 * FilterCollection class tests
 *
 * @group core
 * @group template
 */
class FilterCollectionTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $fc = new FilterCollection();
        $this->assertInstanceOf(FilterCollection::class, $fc);
        //Core filters count
        $filterClasses = array_unique(array_values(
            $this->getPropertyValue(FilterCollection::class, $fc, 'filters')
        ));
        $this->assertCount(5, $filterClasses);
    }

    public function testAddFilter(): void
    {
        $fc = new FilterCollection();
        $fc->addFilter(CustomFilter::class);

        $filterClasses = array_unique(array_values(
            $this->getPropertyValue(FilterCollection::class, $fc, 'filters')
        ));
        $this->assertCount(6, $filterClasses);
    }

    public function testInvoke(): void
    {
        $fc = new FilterCollection();
        $fc->addFilter(CustomFilter::class);

        //Default
        $this->assertEquals(6, $fc->invoke('default', '', [6]));
        $this->assertEquals(true, $fc->invoke('default', false, [true]));
        $this->assertEquals('value', $fc->invoke('default', null, ['value']));

        //Custom
        $this->assertEquals('b', $fc->invoke('charAt', 'foobar', [3]));

        //Not found
        $this->assertEquals('foobar', $fc->invoke('not_found_filter', 'foobar', []));

        //Not callable
        $this->assertEquals('foobar', $fc->invoke('not_callable', 'foobar', []));
    }
}
