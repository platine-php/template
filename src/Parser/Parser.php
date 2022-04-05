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
 *  @file Parser.php
 *
 *  The Template parser class
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

use Platine\Template\Configuration;
use Platine\Template\Loader\LoaderInterface;
use Platine\Template\Template;

/**
 * Class Parser
 * @package Platine\Template\Parser
 */
class Parser
{
    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The root of the node tree
     * @var Document
     */
    protected Document $root;

    /**
     * Create new instance
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Return the template instance
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * Return the Loader instance
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->template->getLoader();
    }

    /**
     * Return the configuration instance
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->template->getConfig();
    }

    /**
     * Return the document instance
     * @return Document
     */
    public function getRoot(): Document
    {
        return $this->root;
    }

    /**
    * Parse the template source and use the cached
    * content if is available
    * @param string $name
    * @return $this
    */
    public function parse(string $name): self
    {
        $hash = md5($name);

        /** @var Document|false $root */
        $root = $this->template->getCache()->read($hash, true);

        if ($root === false || ($root->hasIncludes())) {
            $content = $this->getLoader()->read($name);
            $tokens = $this->tokenize($content);

            $this->root = new Document($tokens, $this);
            $this->template->getCache()->write($hash, $this->root, true);
        } else {
            $this->root = $root;
        }

        return $this;
    }

    /**
     * Render the template
     * @param string $name the template name
     * @param Context $context
     * @return string
     */
    public function render(string $name, Context $context): string
    {
        return $this->parse($name)
                    ->getRoot()
                    ->render($context);
    }

    /**
     * Render the template using string content
     * @param string $content the template name
     * @param Context $context
     * @return string
     */
    public function renderString(string $content, Context $context): string
    {
        $tokens = $this->tokenize($content);
        $this->root = new Document($tokens, $this);

        return $this->getRoot()
                    ->render($context);
    }

    /**
     * Parser the given source string to tokens
     * @param string $source
     * @return array<int, mixed>
     */
    public function tokenize(string $source): array
    {
        if (empty($source)) {
            return [];
        }

        $tokens = preg_split(
            Token::TOKENIZATION_REGEXP,
            $source,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        return $tokens !== false ? $tokens : [];
    }
}
