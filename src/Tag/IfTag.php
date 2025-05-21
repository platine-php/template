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
 *  @file IfTag.php
 *
 *  The "if" Template tag class
 *
 *  @package    Platine\Template\Tag
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Tag;

use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractCondition;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * @class IfTag
 * @package Platine\Template\Tag
 */
class IfTag extends AbstractCondition
{
    /**
     * holding the nodes to render for each logical block
     * @var array<int, mixed>
     */
    protected array $nodeListHolders = [];

    /**
     * holding the block type, block markup (conditions)
     *  and block node list
     * @var array<int, mixed>
     */
    protected array $blocks = [];

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        //Initialize
        $this->nodeListHolders[count($this->blocks)] = [];
        $this->nodeList = & $this->nodeListHolders[count($this->blocks)];
        array_push($this->blocks, ['if', $markup, & $this->nodeList]);

        parent::__construct($markup, $tokens, $parser);
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        $context->push();
        $lexerLogical = new Lexer('/\s+(and|or)\s+/');
        $lexerConditional = new Lexer(
            '/('
                . Token::QUOTED_FRAGMENT
                . ')\s*([=!<>a-z_]+)?\s*('
                . Token::QUOTED_FRAGMENT
                . ')?/'
        );

        $result = '';
        foreach ($this->blocks as $block) {
            if ($block[0] === 'else') {
                $result = $this->renderAll($block[2], $context);

                break;
            }

            if ($block[0] === 'if' || $block[0] === 'elseif') {
                // Extract logical operators
                $lexerLogical->matchAll($block[1]);
                $operators = $lexerLogical->getArrayMatch(1);
                // Extract individual conditions
                $individualConditions = $lexerLogical->split($block[1]);

                $conditions = [];
                foreach ($individualConditions as $condition) {
                    if ($lexerConditional->match($condition)) {
                        $left = $lexerConditional->isMatchNotNull(1)
                                ? $lexerConditional->getStringMatch(1)
                                : null;
                        $operator = $lexerConditional->isMatchNotNull(2)
                                ? $lexerConditional->getStringMatch(2)
                                : null;
                        $right = $lexerConditional->isMatchNotNull(3)
                                ? $lexerConditional->getStringMatch(3)
                                : null;

                        array_push($conditions, [
                            'left' => $left,
                            'operator' => $operator,
                            'right' => $right,
                        ]);
                    } else {
                        throw new ParseException(sprintf(
                            'Syntax Error in "%s" - Valid syntax: if [conditions]',
                            'if'
                        ));
                    }
                }

                if (count($operators) > 0) {
                    // If statement contains and/or
                    $display = $this->evaluateCondition(
                        $conditions[0]['left'],
                        $conditions[0]['right'],
                        $conditions[0]['operator'],
                        $context
                    );

                    foreach ($operators as $key => $operator) {
                        if ($operator === 'and') {
                            $display = (
                                $display
                                && $this->evaluateCondition(
                                    $conditions[$key + 1]['left'],
                                    $conditions[$key + 1]['right'],
                                    $conditions[$key + 1]['operator'],
                                    $context
                                )
                            );
                        } else {
                            $display = (
                                $display
                                || $this->evaluateCondition(
                                    $conditions[$key + 1]['left'],
                                    $conditions[$key + 1]['right'],
                                    $conditions[$key + 1]['operator'],
                                    $context
                                )
                            );
                        }
                    }
                } else {
                    // If statement is a single condition
                    $display = $this->evaluateCondition(
                        $conditions[0]['left'],
                        $conditions[0]['right'],
                        $conditions[0]['operator'],
                        $context
                    );
                }

                // hook for if not tag
                $display = $this->negateCondition($display);

                if ($display) {
                    $result = $this->renderAll($block[2], $context);

                    break;
                }
            }
        }

        $context->pop();

        return $result;
    }

    /**
     * Handler negate condition
     * @param mixed $value
     * @return mixed
     */
    protected function negateCondition(mixed $value): mixed
    {
        // no need to negate a condition in a regular `if`
        // tag (will do that in `ifnot` tag)

        return $value;
    }

    /**
    * {@inheritdoc}
    */
    protected function unknownTag(string $tag, string $param, array $tokens): void
    {
        if ($tag === 'else' || $tag === 'elseif') {
            // Update reference to node list holder for this block
            $this->nodeListHolders[count($this->blocks) + 1] = [];
            $this->nodeList = & $this->nodeListHolders[count($this->blocks) + 1];
            array_push($this->blocks, [$tag, $param, &$this->nodeList]);
        } else {
            parent::unknownTag($tag, $param, $tokens);
        }
    }
}
