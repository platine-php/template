<?php

declare(strict_types=1);

namespace Platine\Test\Template\Parser;

use Platine\PlatineTestCase;
use Platine\Template\Parser\Token;

/**
 * Token class tests
 *
 * @group core
 * @group template
 */
class TokenTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $t = new Token();
        $this->assertInstanceOf(Token::class, $t);
    }
}
