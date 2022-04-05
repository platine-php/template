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
 *  @file CycleTag.php
 *
 *  The "cycle" Template tag class
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
 * Class CycleTag
 * @package Platine\Template\Tag
 */
class CycleTag extends AbstractTag
{
    /**
     * The name of the cycle; if none is given one
     * is created using the value list
     * @var string
     */
    protected string $name;

    /**
     * The variables to cycle between
     * @var array<int, string>
     */
    protected array $variables = [];

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexerSimple = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        $lexerNamed = new Lexer('/(' . Token::QUOTED_FRAGMENT . ')\s*\:\s*(.*)/');
        if ($lexerNamed->match($markup)) {
            $this->variables = $this->variablesFromString($lexerNamed->getStringMatch(2));
            $this->name = $lexerNamed->getStringMatch(1);
        } elseif ($lexerSimple->match($markup)) {
            $this->variables = $this->variablesFromString($markup);
            $this->name = "'" . implode('', $this->variables) . "'";
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: cycle [name :] var [, var2, var3 ...]',
                'cycle'
            ));
        }
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        $context->push();

        $iteration = 0;

        $key = $context->get($this->name);
        if ($context->hasRegister('cycle')) {
            $cycle = $context->getRegister('cycle');
            if (isset($cycle[$key])) {
                $iteration = $cycle[$key];
            }
        }

        $result = $context->get($this->variables[$iteration]);

        if ($result === null) {
            $result = '';
        }

        $iteration++;

        if ($iteration >= count($this->variables)) {
            $iteration = 0;
        }

        $context->setRegister('cycle', [$key => $iteration]);
        $context->pop();

        return $result;
    }

    /**
     *
     * @param string $markup
     * @return array<int,string>
     */
    protected function variablesFromString(string $markup): array
    {
        $lexer = new Lexer('/\s*(' . Token::QUOTED_FRAGMENT . ')\s*/');
        $parts = explode(',', $markup);

        $result = [];
        if ($parts !== false) {
            foreach ($parts as $part) {
                $lexer->match($part);

                if ($lexer->getStringMatch(1)) {
                    $result[] = $lexer->getStringMatch(1);
                }
            }
        }

        return $result;
    }
}
