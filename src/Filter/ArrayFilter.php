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
 *  @file NumberFilter.php
 *
 *  The Array Filter class
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

use Iterator;
use Platine\Stdlib\Helper\Json;
use Platine\Template\Parser\AbstractFilter;
use Traversable;

/**
 * Class ArrayFilter
 * @package Platine\Template\Filter
 */
class ArrayFilter extends AbstractFilter
{
    /**
     * Returns the first element of an array
     * @param array<mixed>|Iterator<mixed>|mixed $value
     * @return mixed
     */
    public static function first($value)
    {
        if (is_array($value)) {
            return reset($value);
        }

        if ($value instanceof Iterator) {
            $value->rewind();

            return $value->current();
        }

        return $value;
    }

    /**
     * Returns the last element of an array
     * @param array<mixed>|Traversable<mixed>|mixed $value
     * @return mixed
     */
    public static function last($value)
    {
        if (is_array($value)) {
            return end($value);
        }

        if ($value instanceof Traversable) {
            $last = null;
            foreach ($value as $elem) {
                $last = $elem;
            }

            return $last;
        }

        return $value;
    }

    /**
     * Sort an array.
     * @param array<int|string, mixed>|mixed $variable
     * @param mixed $property
     * @return mixed
     */
    public static function sort($variable, $property = null)
    {
        if ($variable instanceof Traversable) {
            $variable = iterator_to_array($variable);
        }

        if ($property === null) {
            asort($variable);
        } else {
            $first = reset($variable);
            if ($first !== false && is_array($first) && array_key_exists($property, $first)) {
                uasort($variable, function ($a, $b) use ($property) {
                    return $a[$property] <=> $b[$property];
                });
            }
        }

        return $variable;
    }

    /**
     * Sort an array by key.
     * @param array<int|string, mixed>|mixed $variable
     * @return mixed
     */
    public static function sortKey($variable)
    {
        if (is_array($variable)) {
            ksort($variable);
            return $variable;
        }

        return $variable;
    }

    /**
     * Remove duplicate elements from an array
     * @param array<int|string, mixed>|mixed $variable
     * @return mixed
     */
    public static function unique($variable)
    {
        if ($variable instanceof Traversable) {
            $variable = iterator_to_array($variable);
        }

        return array_unique($variable);
    }

    /**
     * Map/collect on a given property
     * @param array<mixed>|Traversable|mixed $variable
     * @param mixed $property
     * @return string|mixed
     */
    public static function map($variable, $property)
    {
        if ($variable instanceof Traversable) {
            $variable = iterator_to_array($variable);
        }

        if (!is_array($variable)) {
            return $variable;
        }

        return array_map(function ($element) use ($property) {
            if (is_callable($element)) {
                return $element();
            } elseif (is_array($element) && array_key_exists($property, $element)) {
                return $element[$property];
            }

            return null;
        },
        $variable);
    }

    /**
     * Reverse the elements of an array
     * @param array<mixed>|Traversable|mixed $variable
     * @return string|mixed
     */
    public static function reverse($variable)
    {
        if ($variable instanceof Traversable) {
            $variable = iterator_to_array($variable);
        }

        if (!is_array($variable)) {
            return $variable;
        }

        return array_reverse($variable);
    }

    /**
     * Return the JSON representation
     * @param mixed $variable
     * @param mixed $pretty whether use pretty print
     * @return string
     */
    public static function json($variable, $pretty = false)
    {
        $prettyPrint = boolval($pretty);
        $options = 0;
        if ($prettyPrint) {
            $options = JSON_PRETTY_PRINT;
        }

        return Json::encode($variable, $options);
    }
}
