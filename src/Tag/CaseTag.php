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
 *  @file CaseTag.php
 *
 *  The "case" Template tag class
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
use Platine\Template\Parser\AbstractCondition;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;
use Platine\Template\Parser\Variable;

/**
 * Class CaseTag
 * @package Platine\Template\Tag
 */
class CaseTag extends AbstractCondition
{
    /**
     * Stack of node list
     * @var array<int, array<int, mixed>>
     */
    protected array $nodeLists = [];

    /**
     * The node list for the "else"
     * @var AbstractTag[]|Variable[]|string[]
     */
    protected array $elseNodeList = [];

    /**
     * Left value to compare
     * @var mixed|null
     */
    protected $left = null;

    /**
     * Right value to compare
     * @var mixed|null
     */
    protected $right = null;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        parent::__construct($markup, $tokens, $parser);

        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->left = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: case [condition]',
                'case'
            ));
        }
    }

    /**
    * {@inheritdoc}
    */
    protected function endTag(): void
    {
        $this->pushNodeList();
    }

    /**
    * {@inheritdoc}
    */
    protected function unknownTag(string $tag, string $param, array $tokens): void
    {
        $whenLexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        switch ($tag) {
            case 'when':
                // push the current node list onto
                // the stack and prepare for a new one
                if ($whenLexer->match($param)) {
                    $this->pushNodeList();
                    $this->right = $whenLexer->getMixedMatch(0);
                    $this->nodeList = [];
                } else {
                    throw new ParseException(sprintf(
                        'Syntax Error in "%s" - Valid "when" condition: when [condition]',
                        'case'
                    ));
                }
                break;
            case 'else':
                // push the last node list onto the stack
                // and prepare to receive the else nodes
                $this->pushNodeList();
                $this->right = null;
                $this->elseNodeList = &$this->nodeList;
                $this->nodeList = [];
                break;
            default:
                parent::unknownTag($tag, $param, $tokens);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        $output = '';
        $runElseBlock = true;

        foreach ($this->nodeLists as $data) {
            list($right, $nodeList) = $data;

            if ($this->variableIsEqual($this->left, $right, $context)) {
                $runElseBlock = false;

                $context->push();
                $output .= $this->renderAll($nodeList, $context);
                $context->pop();
            }
        }

        if ($runElseBlock) {
            $context->push();
            $output .= $this->renderAll($this->elseNodeList, $context);
            $context->pop();
        }

        return $output;
    }

    /**
     * Pushes the current right value and node list
     * into the node list stack
     * @return void
     */
    protected function pushNodeList(): void
    {
        if ($this->right !== null) {
            $this->nodeLists[] = [$this->right, $this->nodeList];
        }
    }
}
