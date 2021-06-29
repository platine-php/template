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
 *  @file Variable.php
 *
 *  The Template Variable class
 *
 *  @package    Platine\Template\Parser
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Parser;

use Platine\Template\Util\Helper;

/**
 * Class Variable
 * @package Platine\Template\Parser
 */
class Variable
{
    /**
     * The variable markup
     * @var string
     */
    protected string $markup;

    /**
     * The filters to execute on the variable
     * @var array<int, array<int, mixed>|string>
     */
    protected array $filters = [];

    /**
     * The name of the variable
     * @var string
     */
    protected string $name;

    /**
     * The parser instance
     * @var Parser
     */
    protected Parser $parser;

    /**
     * Create new instance
     * @param string $markup
     * @param Parser $parser
     */
    public function __construct(string $markup, Parser $parser)
    {
        $this->markup = $markup;
        $this->parser = $parser;

        $filterSeparatorRegex = '/'
                                . Token::FILTER_SEPARATOR
                                . '\s*(.*)/m';
        $syntaxRegex = '/('
                       . Token::QUOTED_FRAGMENT
                       . ')(.*)/m';
        $filterRegex = '/(?:\s+|'
                       . Token::QUOTED_FRAGMENT
                       . '|'
                       . Token::FILTER_METHOD_ARGS_SEPARATOR
                       . ')+/';
        $filterArgumentsRegex = '/(?:'
                                . Token::FILTER_NAME_ARG_SEPARATOR
                                . '|'
                                . Token::FILTER_METHOD_ARGS_SEPARATOR
                                . ')\s*((?:\w+\s*\:\s*)?'
                                . Token::QUOTED_FRAGMENT
                                . ')/';

        $lexerFilterSeparator = new Lexer($filterSeparatorRegex);
        $lexerSyntax = new Lexer($syntaxRegex);
        $lexerFilter = new Lexer($filterRegex);
        $lexerFilterArguments = new Lexer($filterArgumentsRegex);

        $this->filters = [];
        if ($lexerSyntax->match($markup)) {
            $nameMarkup = $lexerSyntax->getStringMatch(1);
            $this->name = $nameMarkup;
            $filterMarkup = $lexerSyntax->getStringMatch(2);

            if ($lexerFilterSeparator->match($filterMarkup)) {
                $lexerFilter->matchAll($lexerFilterSeparator->getStringMatch(1));

                foreach ($lexerFilter->getArrayMatch(0) as $filter) {
                    $filter = trim($filter);
                    $matches = [];
                    if (preg_match('/\w+/', $filter, $matches)) {
                        $filterName = $matches[0];
                        $lexerFilterArguments->matchAll($filter);

                        $matches = Helper::arrayFlatten($lexerFilterArguments->getArrayMatch(1));
                        $this->filters[] = $this->parseFilterExpressions($filterName, $matches);
                    }
                }
            }
        }

        if ($this->parser->getConfig()->get('auto_escape')) {
            // if auto_escape is enabled, and
            // - there's no raw filter, and
            // - no escape filter
            // - no other standard html-adding filter
            // then
            // - add a mandatory escape filter
            $addEscapeFilter = true;

            foreach ($this->filters as $filter) {
                // with empty filters set we would just move along
                if (in_array($filter[0], ['escape', 'raw', 'nl2br', 'escape_once'])) {
                    // if we have any raw-like filter, stop
                    $addEscapeFilter = false;
                    break;
                }
            }

            if ($addEscapeFilter) {
                $this->filters[] = ['escape', []];
            }
        }
    }

    /**
     * Return the name of variable
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the list of filter
     * @return array<int, array<int, mixed>|string>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Renders the variable with the data in the context
     * @param Context $context
     * @return mixed
     */
    public function render(Context $context)
    {
        $output = $context->get($this->name);
        foreach ($this->filters as $filter) {
            list($filterName, $filterArgKeys) = $filter;

            $filterArgValues = [];
            $keywordArgValues = [];

            foreach ($filterArgKeys as $argKey) {
                if (is_array($argKey)) {
                    foreach ($argKey as $keywordArgName => $keywordArgKey) {
                        $keywordArgValues[$keywordArgName] = $context->get($keywordArgKey);
                    }

                    $filterArgValues[] = $keywordArgValues;
                } else {
                    $filterArgValues[] = $context->get($argKey);
                }
            }

            $output = $context->invokeFilter(
                $filterName,
                $output,
                $filterArgValues
            );
        }

        return $output;
    }

    /**
     * Parse filter expression
     * @param string $filterName
     * @param array<int, mixed> $args
     * @return array<int, array<int, mixed>|string>
     */
    protected function parseFilterExpressions(string $filterName, array $args): array
    {
        $filterArgts = [];
        $keywordArgs = [];
        $lexerTagAtt = new Lexer(
            '/\A'
            . trim(Token::TAG_ATTRIBUTES, '/')
            . '\z/'
        );

        foreach ($args as $arg) {
            if ($lexerTagAtt->match($arg)) {
                $keywordArgs[$lexerTagAtt->getStringMatch(1)] = $lexerTagAtt->getStringMatch(2);
            } else {
                $filterArgts[] = $arg;
            }
        }

        if (count($keywordArgs) > 0) {
            $filterArgts[] = $keywordArgs;
        }

        return [$filterName, $filterArgts];
    }
}
