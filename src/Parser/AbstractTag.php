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
 *  @file AbstractTag.php
 *
 *  The base Template tag class
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

use ReflectionClass;

/**
 * Class AbstractTag
 * @package Platine\Template\Parser
 */
abstract class AbstractTag
{
    /**
     * The name of this class tag
     * @var string
     */
    protected string $name;

    /**
     * The tag markup
     * @var string
     */
    protected string $markup;

    /**
     * The parser instance to use
     * @var Parser
     */
    protected Parser $parser;

    /**
     * The list of tag attributes
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Create new instance
     * @param string $markup
     * @param array<int, string> $tokens
     * @param Parser $parser
     */
    public function __construct(string $markup, array &$tokens, Parser $parser)
    {
        $this->markup = $markup;
        $this->parser = $parser;

        $this->parse($tokens);
    }

    /**
     * Parse the token
     * @param array<int, string> $tokens
     * @return void
     */
    public function parse(array &$tokens): void
    {
        //Do nothing now
    }

    /**
     * Render the tag with the given context.
     * @param Context $context
     * @return string
     */
    abstract public function render(Context $context): string;

    /**
     * Extracts tag attributes from a markup string.
     * @param string $markup
     * @return void
     */
    protected function extractAttributes(string $markup): void
    {
        $this->attributes = [];

        $lexer = new Lexer(Token::TAG_ATTRIBUTES);

        $matches = $lexer->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $match[1];
        }
    }

    /**
     * Return the name of this tag
     * @return string
     */
    protected function getTagName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        $reflection = new ReflectionClass($this);

        return str_replace('tag', '', strtolower($reflection->getShortName()));
    }

    /**
    * Returns the class name of the tag.
    *
    * @return string
    */
    protected function getName(): string
    {
        return strtolower(get_class($this));
    }
}
