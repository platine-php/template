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
 *  @file AbstractCondition.php
 *
 *  The Base Template Condition class
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

use Generator;
use Platine\Template\Exception\RenderException;

/**
 * @class AbstractCondition
 * @package Platine\Template\Parser
 */
abstract class AbstractCondition extends AbstractBlock
{
    /**
     * The current left variable to compare
     * @var mixed
     */
    protected mixed $left = null;

    /**
     * The current right variable to compare
     * @var mixed
     */
    protected mixed $right = null;

    /**
     * Returns a string value of an array or object for comparisons
     * @param mixed $value
     * @return mixed
     */
    protected function stringValue(mixed $value): mixed
    {
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            if ($value instanceof Generator) {
                return (string) $value->valid();
            }

            $class = get_class($value);
            throw new RenderException(sprintf(
                'The value of type [%s] has no "toObject" nor "__toString" methods',
                $class
            ));
        }

        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Check to see if to variables are equal in a given context
     * @param string|null $left
     * @param string|null $right
     * @param Context $context
     * @return bool
     */
    protected function variableIsEqual(?string $left, ?string $right, Context $context): bool
    {
        $leftValue = $left;
        $rightValue = $right;
        if (is_string($left) && $context->hasKey($left)) {
            $leftValue = $context->get($left);
        }

        if (is_string($right) && $context->hasKey($right)) {
            $rightValue = $context->get($right);
        }

        $leftStr = $this->stringValue($leftValue);
        $rightStr = $this->stringValue($rightValue);

        return $leftStr == $rightStr;
    }

    /**
     * Evaluate a comparison
     * @param mixed $left
     * @param mixed $right
     * @param string|null $operator
     * @param Context $context
     * @return bool
     */
    protected function evaluateCondition(
        mixed $left,
        mixed $right,
        ?string $operator,
        Context $context
    ): bool {
        if ($operator === null) {
            $value = $this->stringValue($context->get($left));

            return (bool) $value;
        }

        // values of 'empty' have a special meaning in array comparisons
        if ($right == 'empty' && is_array($context->get($left))) {
            $left = $context->get($left);
            $right = 0;
        } elseif ($left == 'empty' && is_array($context->get($right))) {
            $right = $context->get($right);
            $left = 0;
        } else {
            $leftValue = $context->get($left);
            $rightValue = $context->get($right);

            $left = $this->stringValue($leftValue);
            $right = $this->stringValue($rightValue);
        }

        // special rules for null values
        if (is_null($left) || is_null($right)) {
            //null == null => true
            if ($operator === '==' && is_null($left) && is_null($right)) {
                return true;
            }

            //null != anything other than null => true
            if ($operator === '!=' && (is_null($left) || is_null($right))) {
                return true;
            }

            return false;
        }

        //regular rules
        switch ($operator) {
            case '==':
                return ($left == $right);
            case '!=':
                return ($left != $right);
            case '>':
                return ($left > $right);
            case '<':
                return ($left < $right);
            case '>=':
                return ($left >= $right);
            case '<=':
                return ($left <= $right);
            case 'contains':
                return (is_array($left) ? in_array($right, $left) : (strpos($left, $right) !== false));
            default:
                throw new RenderException(sprintf(
                    'Error in tag [%s] - Unknown operator [%s]',
                    $this->getTagName(),
                    $operator
                ));
        }
    }
}
