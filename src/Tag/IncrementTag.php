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
 *  @file IncrementTag.php
 *
 *  The "increment" Template tag class
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

use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Class IncrementTag
 * @package Platine\Template\Tag
 */
class IncrementTag extends AbstractTag
{
     /**
     * Name of the variable to increment
     * @var string
     */
    protected string $name;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/(' . Token::VARIABLE_NAME . ')/');
        if ($lexer->match($markup)) {
            $this->name = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: increment [var]',
                'increment'
            ));
        }
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        // if the value is not set in the environment check to see if it
        // exists in the context, and if not set it to 0
        if (!$context->hasEnvironment($this->name)) {
            // check for a context value
            $fromContext = $context->get($this->name);

            $context->setEnvironment($this->name, $fromContext !== null ? $fromContext : 0);
        }
        // increment the environment value
        $currentValue = $context->getEnvironment($this->name);

        $context->setEnvironment($this->name, ++$currentValue);

        return '';
    }
}
