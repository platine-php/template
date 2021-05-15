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
 *  @file Token.php
 *
 *  The template token class
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

/**
 * Class Token
 * @package Platine\Template\Parser
 */
class Token
{
    /**
     * The template opened variable
     */
    public const VARIABLE_OPEN = '{{';

    /**
     * The template closed variable
     */
    public const VARIABLE_CLOSE = '}}';

    /**
     * Variable name pattern
     */
    public const VARIABLE_NAME = '[a-zA-Z_][a-zA-Z_0-9.-]*';

    /**
     * The template variable attribute separator
     */
    public const VARIABLE_ATTR_SEPARATOR = '.';

    /**
     * The template opened block
     */
    public const BLOCK_OPEN = '{%';

    /**
     * The template closed block
     */
    public const BLOCK_CLOSE = '%}';
    /**
     * The template white space control char.
     */
    public const WHITESPACE_CONTROL = '-';

    /**
     * Separator between filters.
     */
    public const FILTER_SEPARATOR = '\|';

    /**
     * Separator for filter function/method arguments.
     */
    public const FILTER_METHOD_ARGS_SEPARATOR = ',';

    /**
     * Separator for argument names and values.
     */
    public const FILTER_NAME_ARG_SEPARATOR = ':';

    /**
     * Quoted string pattern
     */
    public const QUOTED_STRING = '(?:"[^"]*"|\'[^\']*\')';

    /**
     * Quoted fragment pattern
     */
    public const QUOTED_FRAGMENT = '(?:' . self::QUOTED_STRING
                                   . '|(?:[^\s,\|\'"]|'
                                   . self::QUOTED_STRING . ')+)';

    /**
     * Tag attributes pattern expression
     */
    public const TAG_ATTRIBUTES = '/(\w+)\s*\:\s*(' . self::QUOTED_FRAGMENT . ')/';

    /**
     * Template parse pattern
     */
    public const TOKENIZATION_REGEXP = '/(' . self::BLOCK_OPEN
                                       . '.*?' . self::BLOCK_CLOSE
                                       . '|' . self::VARIABLE_OPEN
                                       . '.*?' . self::VARIABLE_CLOSE
                                       . ')/';
}
