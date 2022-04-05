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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Filter;

use Platine\Template\Parser\AbstractFilter;
use Traversable;

/**
 * Class StringFilter
 * @package Platine\Template\Filter
 */
class StringFilter extends AbstractFilter
{
    /**
     * Return the length of string or array
     * @param mixed $variable
     * @return int|mixed
     */
    public static function length($variable)
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
     * @return string|mixed
     */
    public static function append($variable, $value)
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
     * @return string|mixed
     */
    public static function prepend($variable, $value)
    {
        if (!is_string($variable) || !is_string($value)) {
            return $variable;
        }

        return $value . $variable;
    }

    /**
     * Remove a string to variable
     * @param mixed $variable
     * @param mixed $value
     * @return string|mixed
     */
    public static function remove($variable, $value)
    {
        if (!is_string($variable) || !is_string($value)) {
            return $variable;
        }

        return str_replace($value, '', $variable);
    }

    /**
     * Replace occurrences of a string with another
     * @param mixed $variable
     * @param mixed $value
     * @param mixed $replacement
     * @return string|mixed
     */
    public static function replace($variable, $value, $replacement = '')
    {
        if (!is_string($variable) || !is_string($value) || !is_string($replacement)) {
            return $variable;
        }

        return str_replace($value, $replacement, $variable);
    }

    /**
     * Truncate a string down to x characters
     * @param mixed $variable
     * @param int|mixed $count
     * @param string|mixed $ending
     * @return string|mixed
     */
    public static function truncate($variable, $count = 100, $ending = '...')
    {
        if (!is_string($variable) || !is_string($ending) || !is_numeric($count)) {
            return $variable;
        }

        $numberChar = (int) $count;
        if (strlen($variable) > $numberChar) {
            return substr($variable, 0, $numberChar) . $ending;
        }

        return $variable;
    }

    /**
     * Truncate string down to x words
     * @param mixed $variable
     * @param int|mixed $count
     * @param string|mixed $ending
     * @return string|mixed
     */
    public static function truncateWord($variable, $count = 3, $ending = '...')
    {
        if (!is_string($variable) || !is_string($ending) || !is_numeric($count)) {
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
     * @param mixed $variable
     * @return string|mixed
     */
    public static function upper($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($variable);
        }

        return strtoupper($variable);
    }

    /**
     * URL encodes a string
     * @param mixed $variable
     * @return string|mixed
     */
    public static function urlEncode($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return urlencode($variable);
    }

    /**
     * Decodes a URL-encoded string
     * @param mixed $variable
     * @return string|mixed
     */
    public static function urlDecode($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return urldecode($variable);
    }

    /**
     * Explicit string conversion.
     * @param mixed $variable
     * @return string|mixed
     */
    public static function stringfy($variable)
    {
        if (is_array($variable)) {
            return $variable;
        }

        return strval($variable);
    }

    /**
     * Split input string into an array of sub
     * strings separated by given pattern.
     * @param string|mixed $variable
     * @param mixed $pattern
     * @return array<mixed>|mixed
     */
    public static function split($variable, $pattern)
    {
        if (!is_string($variable) || !is_string($pattern)) {
            return $variable;
        }

        return explode($pattern, $variable);
    }

    /**
     * Pseudo-filter: negates auto-added escape filter
     * @param mixed $variable
     * @return string|mixed
     */
    public static function raw($variable)
    {
        return $variable;
    }

    /**
     * Escape a string
     * @param mixed $variable
     * @return string|mixed
     */
    public static function escape($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return htmlspecialchars($variable, ENT_QUOTES);
    }

    /**
     * Escape a string once, keeping all previous HTML entities intact
     * @param mixed $variable
     * @return string|mixed
     */
    public static function escapeOnce($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return htmlentities($variable, ENT_QUOTES, null, false);
    }

    /**
     * Set default value if is blank
     * @param mixed $variable
     * @param mixed $value
     * @return mixed
     */
    public static function defaultValue($variable, $value)
    {
        $isBlank = (is_string($variable) && $variable === '')
                    || is_bool($variable) && $variable === false
                    || $variable === null;


        return $isBlank ? $value : $variable;
    }

    /**
     * Join elements of an array with a given
     * character between them
     * @param array<mixed>|Traversable|mixed $variable
     * @param mixed $glue
     * @return string|mixed
     */
    public static function join($variable, $glue = ' ')
    {
        if (!is_string($glue)) {
            return $variable;
        }

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

        return is_array($variable)
                ? implode($glue, $variable)
                : $variable;
    }

    /**
     * Put all letter to lower case
     * @param mixed $variable
     * @return string|mixed
     */
    public static function lower($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($variable);
        }

        return strtolower($variable);
    }

    /**
     * Capitalize words in the input sentence
     * @param mixed $variable
     * @return string|mixed
     */
    public static function capitalize($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

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
     * @param mixed $variable
     * @return string|mixed
     */
    public static function lstrip($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return ltrim($variable);
    }

    /**
     * Remove the right blank characters
     * @param mixed $variable
     * @return string|mixed
     */
    public static function rstrip($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return rtrim($variable);
    }

    /**
     * Remove the left and right blank characters
     * @param mixed $variable
     * @return string|mixed
     */
    public static function strip($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return trim($variable);
    }

    /**
     * Removes HTML tags from text
     * @param mixed $variable
     * @return string|mixed
     */
    public static function stripHtml($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return strip_tags($variable);
    }

    /**
     * Strip all newlines (\n, \r) from string
     * @param mixed $variable
     * @return string|mixed
     */
    public static function stripNewLine($variable)
    {
        if (!is_string($variable)) {
            return $variable;
        }

        return str_replace(["\n", "\r"], '', $variable);
    }
}
