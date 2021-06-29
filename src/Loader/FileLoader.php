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
 *  @file FileLoader.php
 *
 *  The file system Template loader class
 *
 *  @package    Platine\Template\Loader
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Loader;

use Platine\Template\Configuration;
use Platine\Template\Exception\NotFoundException;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\Lexer;
use Platine\Template\Util\Helper;

/**
 * Class FileLoader
 * @package Platine\Template\Loader
 */
class FileLoader implements LoaderInterface
{

    /**
     * The configuration instance
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * The root path to store file
     * @var string
     */
    protected string $path;

    /**
     * Create new instance
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        $dir = $config->get('template_dir');
        $path = Helper::normalizePath($dir);
        $realPath = realpath($path);

        if ($realPath === false || !is_writable($realPath)) {
            throw new NotFoundException(sprintf(
                'The template directory [%s] does not exist or writable',
                $path
            ));
        }

        $this->path = $realPath . DIRECTORY_SEPARATOR;
    }

    /**
    * {@inheritdoc}
    */
    public function read(string $name): string
    {
        $file = $this->getFilePath($name);

        return (string) file_get_contents($file);
    }


    protected function getFilePath(string $file): string
    {
        $pattern = '/^[^.\/][a-zA-Z0-9_\/-]+$/';

        $lexer = new Lexer($pattern);
        if (!$lexer->match($file)) {
            throw new ParseException(sprintf(
                'Invalid template filename [%s]',
                $file
            ));
        }

        $extension = $this->config->get('file_extension');
        if (!empty($extension)) {
            $file .= '.' . $extension;
        }

        $realPath = $this->path . $file;

        if (!is_file($realPath)) {
            throw new NotFoundException(sprintf(
                'Template file [%s] does not exist',
                $realPath
            ));
        }

        return $realPath;
    }
}
