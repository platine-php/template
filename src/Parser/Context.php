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
 *  @file Context.php
 *
 *  The template Context class
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

use Countable;
use Platine\Template\Exception\TemplateException;
use Platine\Template\Filter\ArrayFilter;
use TypeError;

/**
 * Class Context
 * @package Platine\Template\Parser
 */
class Context
{
    /**
     * The local scopes
     * @var array<int, array<string, mixed>>
     */
    protected array $assigns = [];

    /**
     * Registers for non-variable state data
     * @var array<string, mixed>
     */
    protected array $registers = [];

    /**
     * Global scopes
     * @var array<string, mixed>
     */
    protected array $environments = [];

    /**
     * The filter collection instance
     * @var FilterCollection
     */
    protected FilterCollection $filters;

    /**
     * Called "sometimes" while rendering.
     * For example to abort the execution of a rendering.
     * @var callable|null
     */
    protected $tickCallback = null;

    /**
     * The parser instance
     * @var Parser
     */
    protected Parser $parser;


    /**
     * Create new instance
     * @param array<string, mixed> $assigns
     * @param array<string, mixed> $registers
     */
    public function __construct(array $assigns = [], array $registers = [])
    {
        $this->assigns = [$assigns];
        $this->registers = $registers;
        $this->filters = new FilterCollection();
    }

    /**
     * Set the parser instance
     * @param Parser $parser
     * @return $this
     */
    public function setParser(Parser $parser): self
    {
        $this->parser = $parser;

        return $this;
    }


    /**
     * Set tick callback
     * @param callable|null $tickCallback
     * @return $this
     */
    public function setTickCallback(?callable $tickCallback)
    {
        $this->tickCallback = $tickCallback;

        return $this;
    }

    /**
     * Add filter
     * @param class-string $filter
     * @return $this
     */
    public function addFilter(string $filter): self
    {
        $this->filters->addFilter($filter);

        return $this;
    }

    /**
     * Invokes the filter with the given name
     * @param string $name
     * @param mixed $value
     * @param array<int, mixed> $args
     * @return mixed
     */
    public function invokeFilter(string $name, $value, array $args = [])
    {
        try {
            return $this->filters->invoke($name, $value, $args);
        } catch (TypeError $ex) {
            throw new TemplateException($ex->getMessage(), 0, $ex);
        }
    }

    /**
     * Merges the given assigns into the current assigns
     * @param array<string, mixed> $assigns
     * @return void
     */
    public function merge(array $assigns): void
    {
        $this->assigns[0] = array_merge($this->assigns[0], $assigns);
    }

    /**
     * Push new local scope on the stack.
     * @return bool
     */
    public function push(): bool
    {
        array_unshift($this->assigns, []);

        return true;
    }

    public function pop(): bool
    {
        if (count($this->assigns) === 1) {
            throw new TemplateException('No elements to retrieve (pop) from context');
        }
        array_shift($this->assigns);

        return true;
    }

    /**
     * Return the context value for the given key
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->resolve($key);
    }

    /**
     * Set the value for the given key
     * @param string $key
     * @param mixed $value
     * @param bool $global
     * @return void
     */
    public function set(string $key, $value, bool $global = false): void
    {
        if ($global) {
            $count = count($this->assigns);
            for ($i = 0; $i < $count; $i++) {
                $this->assigns[$i][$key] = $value;
            }
        } else {
            $this->assigns[0][$key] = $value;
        }
    }

    /**
     * Returns true if the given key will properly resolve
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return $this->resolve($key) !== null;
    }

    /**
     * Check whether the given register exists
     * @param string $name
     * @return bool
     */
    public function hasRegister(string $name): bool
    {
        return isset($this->registers[$name]);
    }

    /**
     * Clear the given register
     * @param string $name
     * @return $this
     */
    public function clearRegister(string $name): self
    {
        unset($this->registers[$name]);

        return $this;
    }

    /**
     * Return the value for the given register
     * @param string $name
     * @return mixed|null
     */
    public function getRegister(string $name)
    {
        if ($this->hasRegister($name)) {
            return $this->registers[$name];
        }

        return null;
    }

    /**
     * Set the register value
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setRegister(string $name, $value): self
    {
        $this->registers[$name] = $value;

        return $this;
    }

    /**
     * Check whether the given environment exists
     * @param string $name
     * @return bool
     */
    public function hasEnvironment(string $name): bool
    {
        return isset($this->environments[$name]);
    }

    /**
     * Return the value for the given environment
     * @param string $name
     * @return mixed|null
     */
    public function getEnvironment(string $name)
    {
        if ($this->hasEnvironment($name)) {
            return $this->environments[$name];
        }

        return null;
    }

    /**
     * Set the environment value
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setEnvironment(string $name, $value): self
    {
        $this->environments[$name] = $value;

        return $this;
    }

    /**
     * Call the tick callback
     * @return void
     */
    public function tick(): void
    {
        if ($this->tickCallback === null) {
            return;
        }

        ($this->tickCallback)($this);
    }

    /**
     * Resolve a key by either returning the appropriate literal
     * or by looking up the appropriate variable
     * @param string $key
     * @return mixed|null
     */
    protected function resolve(string $key)
    {
        if ($key === 'null') {
            return null;
        }

        if ($key === 'true') {
            return true;
        }

        if ($key === 'false') {
            return false;
        }

        $matches = [];
        if (preg_match('/^\'(.*)\'$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^"(.*)"$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(-?\d+)$/', $key, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(-?\d[\d\.]+)$/', $key, $matches)) {
            return $matches[1];
        }

        return $this->variable($key);
    }

    /**
     * Fetches the current key in all the scopes
     * @param string $key
     * @return mixed|null
     */
    protected function fetch(string $key)
    {
        if (array_key_exists($key, $this->environments)) {
            return $this->environments[$key];
        }

        foreach ($this->assigns as $scope) {
            if (array_key_exists($key, $scope)) {
                $obj = $scope[$key];

                if ($obj instanceof Drop) {
                    $obj->setContext($this);
                }

                return $obj;
            }
        }

        return null;
    }

    /**
     * Resolved the name spaced queries gracefully.
     * @param string $key
     * @return mixed|null
     */
    protected function variable(string $key)
    {
        // Support numeric and variable array indicies
        $matches = [];
        if (preg_match('|\[[0-9]+\]|', $key)) {
            $key = (string) preg_replace('|\[([0-9]+)\]|', ".$1", $key);
        } elseif (preg_match('|\[[0-9a-z._]+\]|', $key, $matches)) {
            $index = $this->get(str_replace(['[', ']'], '', $matches[0]));
            if (strlen((string) $index) > 0) {
                $key = (string) preg_replace('|\[([0-9a-z._]+)\]|', ".$index", $key);
            }
        }

        $parts = explode(Token::VARIABLE_ATTR_SEPARATOR, $key);

        if ($parts !== false) {
            $object = $this->fetch((string) array_shift($parts));
            while (count($parts) > 0) {
                // since we still have a part to consider
                // and since we can't dig deeper into plain values
                // it can be thought as if it has a property with a null value
                if (
                    !is_object($object)
                    && !is_array($object)
                    && !is_string($object)
                ) {
                    return null;
                }

                // first try to cast an object to an array or value
                if (is_object($object)) {
                    if (method_exists($object, 'toObject')) {
                        $object = $object->toObject();
                    } elseif (method_exists($object, 'toArray')) {
                        $object = $object->toArray();
                    }
                }

                if (is_null($object)) {
                    return null;
                }

                if ($object instanceof Drop) {
                    $object->setContext($this);
                }

                $nextPartName = (string) array_shift($parts);
                if (is_string($object)) {
                    if ($nextPartName === 'size') {
                        return strlen($object);
                    }

                    return null;
                }


                if (is_array($object)) {
                    if (
                        $nextPartName === 'first'
                        && count($parts) === 0
                        && !array_key_exists('first', $object)
                    ) {
                        return ArrayFilter::first($object);
                    }

                    if (
                        $nextPartName === 'last'
                        && count($parts) === 0
                        && !array_key_exists('last', $object)
                    ) {
                        return ArrayFilter::last($object);
                    }

                    if (
                        $nextPartName === 'size'
                        && count($parts) === 0
                        && !array_key_exists('size', $object)
                    ) {
                        return count($object);
                    }

                    // no key - no value
                    if (!array_key_exists($nextPartName, $object)) {
                        return null;
                    }

                    $object = $object[$nextPartName];
                    continue;
                }

                if (!is_object($object)) {
                    // we got plain value, yet asked to resolve a part
                    // think plain values have a null part with any name
                    return null;
                }

                if ($object instanceof Countable) {
                    if (
                        $nextPartName === 'size'
                        && count($parts) === 0
                    ) {
                        return count($object);
                    }
                }

                if ($object instanceof Drop) {
                    if (!$object->hasKey($nextPartName)) {
                        return null;
                    }

                    $object = $object->invokeDrop($nextPartName);
                    continue;
                }

                // if it's just a regular object, attempt to access a public method
                $objectCallable = [$object, $nextPartName];
                if (is_callable($objectCallable)) {
                    $object = call_user_func($objectCallable);
                    continue;
                }

                // if a magic accessor method present...
                if (is_object($object) && method_exists($object, '__get')) {
                    $object = $object->{$nextPartName};
                    continue;
                }

                // Inexistent property is a null, PHP-speak
                if (!property_exists($object, $nextPartName)) {
                    return null;
                }

                // then try a property (independent of accessibility)
                if (property_exists($object, $nextPartName)) {
                    $object = $object->{$nextPartName};
                    continue;
                }
                // we'll try casting this object in the next iteration
            }

            // lastly, try to get an embedded value of an object
            // value could be of any type, not just string, so we have to do this
            // conversion here, not later in AbstractBlock::renderAll
            if (is_object($object) && method_exists($object, 'toObject')) {
                $object = $object->toObject();
            }

            /*
            * Before here were checks for object types and object to string conversion.
            *
            * Now we just return what we have:
            * - Traversable objects are taken care of inside filters
            * - Object-to-string conversion is handled at the last moment
             * in AbstractCondition::stringValue, and in AbstractBlock::renderAll
            *
            * This way complex objects could be passed between templates and to filters
            */
            return $object;
        }
    }
}
