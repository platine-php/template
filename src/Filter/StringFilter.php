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
 *  @file StringFilter.php
 *
 *  The String Filter class
 *
 *  @package    Platine\Template\Filter
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Filter;

use Platine\Template\Parser\AbstractFilter;
use Traversable;

/**
 * @class StringFilter
 * @package Platine\Template\Filter
 */
class StringFilter extends AbstractFilter
{
    /**
     * Return the length of string or array
     * @param mixed $variable
     * @return mixed
     */
    public static function length(mixed $variable): mixed
    {
        if ($variable instanceof Traversable) {
            return iterator_count($variable);
        }

        if (is_array($variable)) {
            return count($variable);
        }

        if (is_object($variable)) {
            if (method_exists($variable, 'size')) {
                return $variable->size();
            }
        }

        if (!is_string($variable)) {
            return $variable;
        }

        if (function_exists('mb_strlen')) {
            return mb_strlen($variable);
        }

        return strlen($variable);
    }

    /**
     * Add one string to another
     * @param mixed $variable
     * @param mixed $value
     * @return mixed
     */
    public static function append(mixed $variable, mixed $value): mixed
    {
        if (!is_string($variable) || !is_string($value)) {
            return $variable;
        }

        return $variable . $value;
    }

    /**
     * Prefix a string to variable
     * @param mixed $variable
     * @param mixed $value
     * @return mixed
     */
    public static function prepend(mixed $variable, mixed $value): mixed
    {
        if (!is_string($variable) || !is_string($value)) {
            return $variable;
        }

        return $value . $variable;
    }

    /**
     * Remove a string to variable
     * @param string $variable
     * @param string $value
     * @return string
     */
    public static function remove(string $variable, string $value): string
    {
        return str_replace($value, '', $variable);
    }

    /**
     * Replace occurrences of a string with another
     * @param string $variable
     * @param string $value
     * @param string $replacement
     * @return string
     */
    public static function replace(
        string $variable,
        string $value,
        string $replacement = ''
    ): string {
        return str_replace($value, $replacement, $variable);
    }

    /**
     * Truncate a string down to x characters
     * @param string $variable
     * @param int|string $count
     * @param string $ending
     * @return string
     */
    public static function truncate(
        string $variable,
        int|string $count = 100,
        string $ending = '...'
    ): string {
        $numberChar = (int) $count;
        if (strlen($variable) > $numberChar) {
            return substr($variable, 0, $numberChar) . $ending;
        }

        return $variable;
    }

    /**
     * Truncate string down to x words
     * @param string $variable
     * @param int|string $count
     * @param string $ending
     * @return string
     */
    public static function truncateWord(
        string $variable,
        int|string $count = 3,
        string $ending = '...'
    ): string {
        if (!is_numeric($count)) {
            return $variable;
        }

        $numberWords = (int) $count;
        $wordList = explode(' ', $variable);
        if (count($wordList) > $numberWords) {
            return implode(' ', array_slice($wordList, 0, $numberWords)) . $ending;
        }

        return $variable;
    }

    /**
     * Put all letters to upper case
     * @param string $variable
     * @return string
     */
    public static function upper(string $variable): string
    {
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($variable);
        }

        return strtoupper($variable);
    }

    /**
     * URL encodes a string
     * @param string $variable
     * @return string
     */
    public static function urlEncode(string $variable): string
    {
        return urlencode($variable);
    }

    /**
     * Decodes a URL-encoded string
     * @param string $variable
     * @return string
     */
    public static function urlDecode(string $variable): string
    {
        return urldecode($variable);
    }

    /**
     * Explicit string conversion.
     * @param mixed $variable
     * @return array<mixed>|string
     */
    public static function stringfy(mixed $variable): array|string
    {
        if (is_array($variable)) {
            return $variable;
        }

        return strval($variable);
    }

    /**
     * Split input string into an array of sub
     * strings separated by given pattern.
     * @param string $variable
     * @param string $pattern
     * @return array<mixed>
     */
    public static function split(string $variable, string $pattern): array
    {
        if (empty($pattern)) {
            return [$variable];
        }

        return explode($pattern, $variable);
    }

    /**
     * If the given value is part of the variable
     * @param string $variable
     * @param string $value
     * @return int|false
     */
    public static function find(string $variable, string $value): int|false
    {
        return strpos($variable, $value);
    }

    /**
     * Pseudo-filter: negates auto-added escape filter
     * @param mixed $variable
     * @return mixed
     */
    public static function raw(mixed $variable): mixed
    {
        return $variable;
    }

    /**
     * Escape a string
     * @param string|null $variable
     * @return string|null
     */
    public static function escape(?string $variable): ?string
    {
        if ($variable === null) {
            return null;
        }

        return htmlspecialchars($variable, ENT_QUOTES);
    }

    /**
     * Escape a string once, keeping all previous HTML entities intact
     * @param string|null $variable
     * @return string|null
     */
    public static function escapeOnce(?string $variable): ?string
    {
        if ($variable === null) {
            return null;
        }

        return htmlentities($variable, ENT_QUOTES, null, false);
    }

    /**
     * Set default value if is blank
     * @param mixed $variable
     * @param mixed $value
     * @return mixed
     */
    public static function defaultValue(mixed $variable, mixed $value): mixed
    {
        $isBlank = (is_string($variable) && $variable === '')
                    || is_bool($variable) && $variable === false
                    || $variable === null;


        return $isBlank ? $value : $variable;
    }

    /**
     * Join elements of an array with a given
     * character between them
     * @param array<mixed>|Traversable|iterable<mixed> $variable
     * @param string $glue
     * @return string
     */
    public static function join(iterable $variable, string $glue = ' '): string
    {
        if ($variable instanceof Traversable) {
            $str = '';
            foreach ($variable as $element) {
                if ($str) {
                    $str .= $glue;
                }

                $str .= $element;
            }

            return $str;
        }

        return implode($glue, $variable);
    }

    /**
     * Put all letter to lower case
     * @param string $variable
     * @return string
     */
    public static function lower(string $variable): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($variable);
        }

        return strtolower($variable);
    }

    /**
     * Capitalize words in the input sentence
     * @param string $variable
     * @return string
     */
    public static function capitalize(string $variable): string
    {
        return (string) preg_replace_callback(
            '/(^|[^\p{L}\'])([\p{Ll}])/u',
            function ($matches) {
                return $matches[1] . ucfirst($matches[2]);
            },
            ucwords($variable)
        );
    }

    /**
     * Remove the left blank characters
     * @param string $variable
     * @return string
     */
    public static function lstrip(string $variable): string
    {
        return ltrim($variable);
    }

    /**
     * Remove the right blank characters
     * @param string $variable
     * @return string
     */
    public static function rstrip(string $variable): string
    {
        return rtrim($variable);
    }

    /**
     * Remove the left and right blank characters
     * @param string $variable
     * @return string
     */
    public static function strip(string $variable): string
    {
        return trim($variable);
    }

    /**
     * Removes HTML tags from text
     * @param string $variable
     * @return string
     */
    public static function stripHtml(string $variable): string
    {
        return strip_tags($variable);
    }

    /**
     * Strip all newlines (\n, \r) from string
     * @param string $variable
     * @return string
     */
    public static function stripNewLine(string $variable): string
    {
        return str_replace(["\n", "\r"], '', $variable);
    }
}
