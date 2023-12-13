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
 *  The Number Filter class
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

/**
 * Class NumberFilter
 * @package Platine\Template\Filter
 */
class NumberFilter extends AbstractFilter
{
    /**
     * Addition
     * @param mixed $variable
     * @param mixed $operand
     * @return float|int|mixed
     */
    public static function plus($variable, $operand)
    {
        if (!is_numeric($variable) || !is_numeric($operand)) {
            return $variable;
        }

        if (is_float($operand) || is_float($variable)) {
            return (float) $variable + (float) $operand;
        }

        return (int) $variable + (int) $operand;
    }

    /**
     * subtraction
     * @param mixed $variable
     * @param mixed $operand
     * @return int|float|mixed
     */
    public static function minus($variable, $operand)
    {
        if (!is_numeric($variable) || !is_numeric($operand)) {
            return $variable;
        }

        if (is_float($operand) || is_float($variable)) {
            return (float) $variable - (float) $operand;
        }

        return (int) $variable - (int) $operand;
    }

    /**
     * Times
     * @param mixed $variable
     * @param mixed $operand
     * @return int|float|mixed
     */
    public static function times($variable, $operand)
    {
        if (!is_numeric($variable) || !is_numeric($operand)) {
            return $variable;
        }

        if (is_float($operand) || is_float($variable)) {
            return (float) $variable * (float) $operand;
        }

        return (int) $variable * (int) $operand;
    }

    /**
     * Modulo
     * @param mixed $variable
     * @param mixed $operand
     * @return int|float|mixed
     */
    public static function modulo($variable, $operand)
    {
        if (!is_numeric($variable) || !is_numeric($operand)) {
            return $variable;
        }

        if (is_float($operand) || is_float($variable)) {
            return fmod((float) $variable, (float) $operand);
        }

        return fmod((int) $variable, (int) $operand);
    }

    /**
     * Division filter
     * @param mixed $variable
     * @param mixed $operand
     * @return int|float|mixed
     */
    public static function div($variable, $operand)
    {
        if (!is_numeric($variable) || !is_numeric($operand) || $operand == 0) {
            return $variable;
        }

        if (is_float($operand) || is_float($variable)) {
            return (float) ($variable / $operand);
        }

        return (int) ($variable / $operand);
    }

    /**
     * Round the number
     * @param mixed $variable
     * @param mixed $number
     * @return float|mixed
     */
    public static function round($variable, $number = 0)
    {
        if (!is_numeric($variable) || !is_numeric($number)) {
            return $variable;
        }

        return round((float) $variable, (int) $number);
    }

    /**
     * Number format
     * @param mixed $variable
     * @param mixed $decimal
     * @param string $decimalPoint
     * @param string $separator
     * @return float|mixed
     */
    public static function format(
        $variable,
        $decimal = 0,
        $decimalPoint = '.',
        $separator = ','
    ) {
        if (!is_numeric($variable)) {
            return $variable;
        }

        return number_format(
            (float) $variable,
            (int) $decimal,
            $decimalPoint,
            $separator
        );
    }

    /**
     * Number format for money
     * @param mixed $variable
     * @param mixed $decimal
     * @param string $decimalPoint
     * @param string $separator
     * @return float|mixed
     */
    public static function formatMoney(
        $variable,
        $decimal = 0,
        $decimalPoint = '.',
        $separator = ','
    ) {
        if (!is_numeric($variable)) {
            return $variable;
        }

        $number = (string) $variable;
        if (strpos($number, '.') === false && strpos($number, ',') === false) {
            $decimal = 0;
        }

        return number_format(
            (float) $variable,
            (int) $decimal,
            $decimalPoint,
            $separator
        );
    }

    /**
     * Return the given number to string
     * @param mixed $variable
     * @return string|mixed
     */
    public static function numberToString($variable)
    {
        $value = (string) $variable;
        if (stripos($value, 'e') !== false) {
            // PHP use scientific notation if decimal has 4 zeros
            // after dot. so use number format instead of
            list($base, $decimal) = explode('E', $value);

            // Some system use "," instead of "."
            if (strpos($value, ',') !== false) {
                $arr = explode(',', $base);
            } else {
                $arr = explode('.', $base);
            }
            $separator = '%.' . (string)(strlen($arr[1]) + (abs($decimal) - 1)) . 'f';

            $value = sprintf($separator, $variable);
        }

        return str_replace(',', '.', $value);
    }

    /**
     * Units format
     * @param mixed $variable
     * @param mixed $precision
     * @return float|int|string
     */
    public static function sizeFormat(
        $variable,
        $precision = 2
    ) {
        if (!is_numeric($variable)) {
            return $variable;
        }

        $size = (double) $variable;
        if ($size > 0) {
            $base = log($size) / log(1024);
            $suffixes = ['B', 'K', 'M', 'G', 'T'];
            $suffix = '';
            if (isset($suffixes[floor($base)])) {
                $suffix = $suffixes[floor($base)];
            }
            return round(pow(1024, $base - floor($base)), (int) $precision) . $suffix;
        }

        return $variable;
    }
}
