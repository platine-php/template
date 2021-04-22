<?php

declare(strict_types=1);

namespace Platine\Test\Template;

use Platine\PlatineTestCase;
use Platine\Template\Template;

/**
 * Template class tests
 *
 * @group core
 * @group template
 */
class TemplateTest extends PlatineTestCase
{

    public function testConstructor()
    {
        $t = new Template();
        $this->assertInstanceOf(Template::class, $t);
    }
}
