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
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template;

use Platine\Stdlib\Config\AbstractConfiguration;

/**
 * @class Configuration
 * @package Platine\Template
 */
class Configuration extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): array
    {
        return [
            'cache_expire' => 'integer',
            'cache_dir' => 'string',
            'cache_prefix' => 'string',
            'template_dir' => 'string',
            'file_extension' => 'string',
            'auto_escape' => 'boolean',
            'filters' => 'array',
            'tags' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault(): array
    {
        return [
            'cache_expire' => 3600,
            'cache_dir' => 'cache',
            'cache_prefix' => '__platine_template',
            'template_dir' => 'templates',
            'file_extension' => 'tpl',
            'auto_escape' => true,
            'filters' => [],
            'tags' => [],
        ];
    }
}
