<?php

/**
 * Platine Template
 *
 * Platine Template is a template engine that has taken a lot of inspiration from Django.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Template
 * Copyright (c) 2014 Guz Alexander, http://guzalexander.com
 * Copyright (c) 2011, 2012 Harald Hanek, http://www.delacap.com
 * Copyright (c) 2006 Mateo Murphy
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file RawTag.php
 *
 *  The "raw" Template tag class
 *
 *  @package    Platine\Template\Tag
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Tag;

use Platine\Template\Parser\AbstractBlock;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Token;

/**
 * Class RawTag
 * @package Platine\Template\Tag
 */
class RawTag extends AbstractBlock
{
    /**
    * {@inheritdoc}
    */
    public function parse(array &$tokens): void
    {
        $lexer = new Lexer(
            '/^'
            . Token::BLOCK_OPEN
            . '\s*(\w+)\s*(.*)?'
            . Token::BLOCK_CLOSE
            . '$/'
        );

        $this->nodeList = [];

        while (count($tokens) > 0) {
            $token = array_shift($tokens);
            if ($lexer->match($token)) {
                // If we found the proper block delimiter
                // just end parsing here and let the outer block proceed
                if ($lexer->getStringMatch(1) === $this->blockDelimiter()) {
                    break;
                }
            }

            $this->nodeList[] = $token;
        }
    }
}
