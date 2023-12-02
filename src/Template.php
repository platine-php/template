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
 *  @file Template.php
 *
 *  The Template class
 *
 *  @package    Platine\Template
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template;

use Platine\Template\Cache\AbstractCache;
use Platine\Template\Cache\NullCache;
use Platine\Template\Loader\LoaderInterface;
use Platine\Template\Loader\StringLoader;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\AbstractTag;

/**
 * Class Template
 * @package Platine\Template
 */
class Template
{
    /**
     * The configuration instance
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * The loader instance
     * @var LoaderInterface
     */
    protected LoaderInterface $loader;

    /**
     * The Parser instance
     * @var Parser
     */
    protected Parser $parser;

    /**
     * Tick callback
     * @var callable|null
     */
    protected $tickCallback = null;

    /**
     * The cache instance
     * @var AbstractCache
     */
    protected AbstractCache $cache;

    /**
     * List of filters
     * @var array<int, class-string>
     */
    protected array $filters = [];

    /**
     * List of tags
     * @var array<string, string|AbstractTag>
     */
    protected array $tags = [];

    /**
     * Create new instance
     * @param Configuration|null $config
     * @param LoaderInterface|null $loader
     * @param AbstractCache|null $cache
     */
    public function __construct(
        ?Configuration $config = null,
        ?LoaderInterface $loader = null,
        ?AbstractCache $cache = null
    ) {
        $this->config = $config ?? new Configuration([]);
        $this->loader = $loader ?? new StringLoader([]);
        $this->cache = $cache ?? new NullCache($config);
        $this->parser = new Parser($this);

        //Add custom tags
        $this->tags = $this->config->get('tags');

        //Add custom filters
        $this->filters = $this->config->get('filters');
    }

    /**
     * Return the configuration instance
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Return the loader instance
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * Return the cache instance
     * @return AbstractCache
     */
    public function getCache(): AbstractCache
    {
        return $this->cache;
    }

    /**
     * Return the list of tags
     * @return array<string, string|AbstractTag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Return the list of filters
     * @return array<int, class-string>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Register Tag
     * @param string $name
     * @param string|AbstractTag $class
     * @return $this
     */
    public function addTag(string $name, $class): self
    {
        $this->tags[$name] = $class;

        return $this;
    }

    /**
     * Register the filter
     * @param class-string $filter
     * @return $this
     */
    public function addFilter(string $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Set tick callback
     * @param callable|null $tickCallback
     * @return $this
     */
    public function setTickCallback(?callable $tickCallback): self
    {
        $this->tickCallback = $tickCallback;

        return $this;
    }

    /**
     * Render the template
     * @param string $name the name of template (filename, etc)
     * @param array<string, mixed> $assigns
     * @param array<string, mixed> $registers
     * @return string the final output
     */
    public function render(string $name, array $assigns = [], array $registers = []): string
    {
        $context = new Context($assigns, $registers);

        if ($this->tickCallback !== null) {
            $context->setTickCallback($this->tickCallback);
        }

        foreach ($this->filters as $filter) {
            $context->addFilter($filter);
        }

        return $this->parser->render($name, $context);
    }

    /**
     * Render the template using the string
     * @param string $content the template content
     * @param array<string, mixed> $assigns
     * @param array<string, mixed> $registers
     * @return string the final output
     */
    public function renderString(string $content, array $assigns = [], array $registers = []): string
    {
        $context = new Context($assigns, $registers);

        if ($this->tickCallback !== null) {
            $context->setTickCallback($this->tickCallback);
        }

        foreach ($this->filters as $filter) {
            $context->addFilter($filter);
        }

        return $this->parser->renderString($content, $context);
    }
}
