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
 *  @file Document.php
 *
 *  The Template Document class
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

use Platine\Template\Tag\BlockTag;
use Platine\Template\Tag\ExtendsTag;
use Platine\Template\Tag\IncludeTag;

/**
 * Class Document
 * @package Platine\Template\Parser
 */
class Document extends AbstractBlock
{
    /**
     * Create new instance
     * @param array<int, string> $tokens
     * @param Parser $parser
     */
    public function __construct(array &$tokens, Parser $parser)
    {
        $this->parser = $parser;

        $this->parse($tokens);
    }

    /**
     * Check for cached includes; if there are - do not use cache
     * @return bool
     */
    public function hasIncludes(): bool
    {
        $seenExtends = false;
        $seenBlock = false;

        foreach ($this->nodeList as $token) {
            if ($token instanceof ExtendsTag) {
                $seenExtends = true;
            } elseif ($token instanceof BlockTag) {
                $seenBlock = true;
            }
        }

        /*
        * We try to keep the base templates in cache (that not extend anything).
        *
        * At the same time if we re-render all other blocks we see, we avoid most
        * if not all related caching quirks. This may be suboptimal.
        */
        if ($seenBlock && !$seenExtends) {
            return true;
        }

        foreach ($this->nodeList as $token) {
            // check any of the tokens for includes
            if ($token instanceof IncludeTag && $token->hasIncludes()) {
                return true;
            }

            if ($token instanceof ExtendsTag && $token->hasIncludes()) {
                return true;
            }
        }

        return false;
    }

    /**
    * {@inheritdoc}
    */
    protected function blockDelimiter(): string
    {
        //There isn't a real delimiter
        return '';
    }

    /**
    * {@inheritdoc}
    */
    protected function assertMissingDelimiter(): void
    {
        // Document blocks don't need to be
        // terminated since they are not actually opened
    }
}
