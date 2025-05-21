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
 *  @file ApcCache.php
 *
 *  The APC cache class
 *
 *  @package    Platine\Template\Cache
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Cache;

use Platine\Template\Configuration;
use Platine\Template\Exception\TemplateException;

/**
 * @class ApcCache
 * @package Platine\Template\Cache
 */
class ApcCache extends AbstractCache
{
    /**
     * Create new instance
     * @param Configuration|null $config
     */
    public function __construct(?Configuration $config = null)
    {
        parent::__construct($config);

        if ((!extension_loaded('apcu')) || !((bool) ini_get('apc.enabled'))) {
            throw new TemplateException('The cache for APCu driver is not available.'
                            . ' Check if APCu extension is loaded and enabled.');
        }
    }

    /**
    * {@inheritdoc}
    */
    public function read(string $key, bool $unserialize = true): mixed
    {
        $success = false;
        $data = apcu_fetch($this->prefix . $key, $success);

        return $success ? $data : false;
    }

    /**
    * {@inheritdoc}
    */
    public function exists(string $key): bool
    {
        return apcu_exists($this->prefix . $key) === true;
    }

    /**
    * {@inheritdoc}
    */
    public function write(string $key, mixed $value, bool $serialize = true): bool
    {
        return apcu_store($this->prefix . $key, $value, $this->expire);
    }


    /**
    * {@inheritdoc}
    */
    public function flush(bool $expired = false): bool
    {
        return apcu_clear_cache();
    }
}
