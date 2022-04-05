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
 *  @file FilterCollection.php
 *
 *  The Template Filter collection class
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

use Platine\Template\Filter\ArrayFilter;
use Platine\Template\Filter\DatetimeFilter;
use Platine\Template\Filter\HtmlFilter;
use Platine\Template\Filter\NumberFilter;
use Platine\Template\Filter\StringFilter;
use Platine\Template\Util\Helper;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class FilterCollection
 * @package Platine\Template\Parser
 */
class FilterCollection
{
    /**
     * The list of filter with their name and class
     * @var array<string, class-string>
     */
    protected array $filters = [];

    /**
     * Create new instance
     */
    public function __construct()
    {
        $this->addFilter(ArrayFilter::class);
        $this->addFilter(DatetimeFilter::class);
        $this->addFilter(HtmlFilter::class);
        $this->addFilter(NumberFilter::class);
        $this->addFilter(StringFilter::class);
    }

    /**
     * Add filter
     * @param class-string $filter a class the filters will be
     *  called statically
     *
     * @return bool
     */
    public function addFilter(string $filter): bool
    {
        // register all its static methods
        $reflection = new ReflectionClass($filter);
        $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC);
        if (!empty($methods)) {
            foreach ($methods as $method) {
                /** @var class-string $className */
                $className = $method->class;
                $this->filters[$method->name] = $className;
            }

            return true;
        }

        return false;
    }

    /**
     * Invokes the filter with the given name
     * @param string $name
     * @param mixed $value
     * @param array<int, mixed> $args
     * @return mixed
     */
    public function invoke(string $name, $value, array $args = [])
    {
        // workaround for a single standard filter being a reserved
        // keyword - we can't use overloading for static calls
        if ($name === 'default') {
            $name = 'defaultValue';
        }

        //convert underscore value to camelcase
        // like sort_key => sortKey
        $methodName = Helper::dashesToCamelCase($name);

        array_unshift($args, $value);

        // Consult the mapping
        if (!isset($this->filters[$methodName])) {
            return $value;
        }

        $className = $this->filters[$methodName];

        $callback = $className . '::' . $methodName;
        if (is_callable($callback)) {
            // Call a class method statically
            return call_user_func_array($callback, $args);
        }

        return $value;
    }
}
