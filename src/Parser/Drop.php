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
 *  @file Drop.php
 *
 *  The Template Drop class
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
 * @class Drop
 * @package Platine\Template\Parser
 *
 */
 /**
 * A drop is a class which allows you to export DOM like things to template.
 * Methods of drops are callable.
 * The main use for drops is the implement lazy loaded objects.
 * If you would like to make data available to the web designers which you don't
 * want loaded unless needed then a drop is a great way to do that.
 */
abstract class Drop implements Stringable
{
    /**
     * The context instance to use
     * @var Context
     */
    protected Context $context;

    /**
     * Set the context instance for future use
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Returns true if the drop supports the given method
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return true;
    }

    /**
     * Invoke a specific method
     * @param string $method
     * @return mixed
     */
    public function invokeDrop(string $method): mixed
    {
        $result = $this->beforeMethod($method);
        if ($result === null && is_callable([$this, $method])) {
            $result = $this->{$method}();
        }

        return $result;
    }

    /**
     * Return the current instance
     * @return $this
     */
    public function toObject(): self
    {
        return $this;
    }

    /**
     * The string representation of this class
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }

    /**
     * Catch all method that is invoked before a specific method
     * @param string $method
     * @return mixed
     */
    protected function beforeMethod(string $method): mixed
    {
        return null;
    }
}
