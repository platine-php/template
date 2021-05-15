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
 *  @file Configuration.php
 *
 *  The Template Configuration class
 *
 *  @package    Platine\Template
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template;

use InvalidArgumentException;

/**
 * Class Configuration
 * @package Platine\Template
 */
class Configuration
{

    /**
     * The configuration raw data
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * The cache file directory to use
     * @var string
     */
    protected string $cacheDir = '.';

    /**
     * The cache prefix to use
     * @var string
     */
    protected string $cachePrefix = '__platine_template';

    /**
     * The cache expiration in second
     * @var int
     */
    protected int $cacheExpire = 3600;

    /**
     * The template files directory to use
     * @var string
     */
    protected string $templateDir = '.';

    /**
     * The template file extension to use
     * @var string
     */
    protected string $fileExtension = 'tpl';

    /**
     * Allow template names with extension in include and extends.
     * @var bool
     */
    protected bool $includeWithExtension = false;

    /**
     * Automatically escape any variables
     * unless told otherwise by a "raw" filter
     * @var bool
     */
    protected bool $autoEscape = false;

    /**
     * The custom tags list
     * @var array<string, class-string>
     */
    protected array $tags = [];

    /**
     * The custom filters list
     * @var array<int, class-string>
     */
    protected array $filters = [];

    /**
     * Create new instance
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->load($config);
    }

    /**
     * Return the value of the given configuration
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if (!array_key_exists($name, $this->config)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid configuration [%s]',
                $name
            ));
        }

        return $this->config[$name];
    }

    /**
     * Return the cache directory
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * Return the template file directory
     * @return string
     */
    public function getTemplateDir(): string
    {
        return $this->templateDir;
    }

    /**
     * Wether use extension in include or extends filename
     * @return bool
     */
    public function isIncludeWithExtension(): bool
    {
        return $this->includeWithExtension;
    }

    /**
     * Return the template file extension
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /**
     * Return the cache prefix
     * @return string
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

    /**
     * Return the cache expiration in second
     * @return int
     */
    public function getCacheExpire(): int
    {
        return $this->cacheExpire;
    }

    /**
     * Whether is auto escaped
     * @return bool
     */
    public function isAutoEscape(): bool
    {
        return $this->autoEscape;
    }

    /**
     * Return the tag list
     * @return array<string, class-string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Return the filter list
     * @return array<int, class-string>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }


    /**
     * Load the configuration
     * @param array<string, mixed> $config
     * @return void
     */
    public function load(array $config): void
    {
        $this->config = $config;

        foreach ($config as $name => $value) {
            $key = str_replace('_', '', lcfirst(ucwords($name, '_')));
            if (property_exists($this, $key)) {
                if (in_array($key, ['tags', 'filters']) && is_array($value)) {
                    $method = 'set' . ucfirst($key);
                    $this->{$method}($value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Set the tags configuration
     * @param array<string, class-string> $tags
     * @return void
     */
    protected function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * Set the filters configuration
     * @param array<int, class-string> $filters
     * @return void
     */
    protected function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }
}
