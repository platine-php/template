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
 *  @file FileCache.php
 *
 *  The File system Template cache class
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
use Platine\Template\Exception\NotFoundException;
use Platine\Template\Util\Helper;

/**
 * Class FileCache
 * @package Platine\Template\Cache
 */
class FileCache extends AbstractCache
{
    /**
     * The cache directory to use
     * @var string
     */
    protected string $path;

    /**
     * Create new instance
     * @param Configuration|null $config
     */
    public function __construct(?Configuration $config = null)
    {
        parent::__construct($config);
        $dir = $this->config->get('cache_dir');
        $path = Helper::normalizePath($dir);
        $realPath = realpath($path);

        if ($realPath === false || !is_writable($realPath)) {
            throw new NotFoundException(sprintf(
                'The cache directory [%s] does not exist or writable',
                $path
            ));
        }

        $this->path = $realPath . DIRECTORY_SEPARATOR;
    }

    /**
    * {@inheritdoc}
    */
    public function read(string $key, bool $unserialize = true)
    {
        if (!$this->exists($key)) {
            return false;
        }

        $file = $this->getFilePath($key);
        $content = (string) file_get_contents($file);
        if ($unserialize) {
            return unserialize($content);
        }

        return $content;
    }

    /**
    * {@inheritdoc}
    */
    public function exists(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file) || (filemtime($file) + $this->expire) < time()) {
            return false;
        }

        return true;
    }

    /**
    * {@inheritdoc}
    */
    public function write(string $key, $value, bool $serialize = true): bool
    {
        $file = $this->getFilePath($key);
        if ($serialize) {
            $value = serialize($value);
        }

        $bytes = file_put_contents($file, $value);

        $this->clean();

        return $bytes !== false;
    }


    /**
    * {@inheritdoc}
    */
    public function flush(bool $expired = false): bool
    {
        $list = glob(sprintf('%s%s*', $this->path, $this->prefix));
        if ($list !== false) {
            foreach ($list as $file) {
                if ($expired) {
                    if ((filemtime($file) + $this->expire) < time()) {
                        unlink($file);
                    }
                } else {
                    unlink($file);
                }
            }
        }

        return true;
    }

    /**
     * Garbage collector of cache file
     * @return void
     */
    protected function clean(): void
    {
        $this->flush(true);
    }

    /**
     * Return the file path for the given key
     * @param string $key
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return sprintf('%s%s%s', $this->path, $this->prefix, $key);
    }
}
