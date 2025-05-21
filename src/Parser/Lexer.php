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
 *  @file Lexer.php
 *
 *  The template lexer class
 *
 *  @package    Platine\Template\Parser
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Parser;

use Stringable;

/**
 * @class Lexer
 * @package Platine\Template\Parser
 */
class Lexer implements Stringable
{
    /**
     * The lexer pattern
     * @var string
     */
    protected string $pattern;

    /**
     * The lexer matches result
     * @var array<int, mixed>
     */
    protected array $matches = [];

    /**
    * Create new instance
    * @param string $pattern
    */
    public function __construct(string $pattern)
    {
        $this->pattern = (substr($pattern, 0, 1) !== '/')
                          ? '/' . $this->quote($pattern) . '/'
                          : $pattern;
    }

    /**
    * Return the array of matches for the given value
    * @param string $value
    * @return array<int|string, array<int|string, mixed>>
    */
    public function scan(string $value): array
    {
        $matches = [];
        preg_match_all($this->pattern, $value, $matches);

        if (count($matches) === 1) {
            return $matches[0];
        }

        array_shift($matches);
        $result = [];
        foreach ($matches as $matchKey => $subMatches) {
            foreach ($subMatches as $subMatchKey => $subMatch) {
                $result[$subMatchKey][$matchKey] = $subMatch;
            }
        }

        return $result;
    }

    /**
    * Whether the given value match regex
    * @param string $value
    * @return bool
    */
    public function match(string $value): bool
    {
        return (bool) preg_match($this->pattern, $value, $this->matches);
    }

    /**
    * Whether the given value match all regex
    * @param string $value
    * @return bool
    */
    public function matchAll(string $value): bool
    {
        return (bool) preg_match_all($this->pattern, $value, $this->matches);
    }

    /**
     *
     * @param string $value
     * @param int $limit
     * @return array<int, string>
     */
    public function split(string $value, int $limit = -1): array
    {
        $result = preg_split($this->pattern, $value, $limit);
        return $result !== false ? $result : [];
    }

    /**
     * The string representation of this class
     * useful in debug situation
     * @return string
     */
    public function __toString(): string
    {
        return $this->pattern;
    }

    /**
     * Return result of matches
     * @param int $index
     * @return mixed
     */
    public function getArrayMatch(int $index = -1): mixed
    {
        if ($index === -1) {
            return $this->matches;
        }

        if (array_key_exists($index, $this->matches)) {
            return $this->matches[$index];
        }

        return [];
    }

    /**
     * Return result of match for string
     * @param int $index
     * @return string
     */
    public function getStringMatch(int $index): string
    {
        if (array_key_exists($index, $this->matches)) {
            return $this->matches[$index];
        }

        return '';
    }

    /**
     * Return result of match for mixed type
     * @param int $index
     * @return mixed
     */
    public function getMixedMatch(int $index): mixed
    {
        if (array_key_exists($index, $this->matches)) {
            return $this->matches[$index];
        }

        return null;
    }

    /**
     * Check wether the match for given index exists
     * @param int $index
     * @return bool
     */
    public function isMatchNotNull(int $index): bool
    {
        return isset($this->matches[$index]);
    }

    /**
    * Quote the given value in order to use in regex expression
    * @param string $value
    * @return string
    */
    protected function quote(string $value): string
    {
        return preg_quote($value, '/');
    }
}
