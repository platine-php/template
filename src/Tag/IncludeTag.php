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
 *  @file IncludeTag.php
 *
 *  The "include" Template tag class
 *
 *  @package    Platine\Template\Tag
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Template\Tag;

use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Document;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Class IncludeTag
 * @package Platine\Template\Tag
 */
class IncludeTag extends AbstractTag
{
    /**
     * The name of the template
     * @var string
     */
    protected string $templateName;

    /**
     * The Document that represents the extends template
     * @var Document
     */
    protected Document $document;

    /**
     * The source template Hash
     * @var string
     */
    protected string $hash;

    /**
     * if the variable is a collection
     * @var bool
     */
    protected bool $isCollection = false;

    /**
     * The value to pass to the child
     * template as the template name
     * @var mixed
     */
    protected $variable;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer(
            '/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+('
            . Token::QUOTED_FRAGMENT
            . '+))?/'
        );

        if (!$lexer->match($markup)) {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: include "[template]" (with|for) [object|collection]',
                'include'
            ));
        }

        $unquoted = (strpos($lexer->getStringMatch(1), '"') === false
                     && strpos($lexer->getStringMatch(1), '\'') === false
                    );

        $start = 1;
        $length = strlen($lexer->getStringMatch(1)) - 2;
        if ($unquoted) {
            $start = 0;
            $length = strlen($lexer->getStringMatch(1));
        }

        $this->templateName = substr($lexer->getStringMatch(1), $start, $length);
        if ($lexer->isMatchNotNull(1)) {
            $this->isCollection = $lexer->isMatchNotNull(3)
                    ? $lexer->getStringMatch(3) === 'for'
                    : false;

            $this->variable = $lexer->isMatchNotNull(4)
                    ? $lexer->getStringMatch(4)
                    : '';
        }

        $this->extractAttributes($markup);

        parent::__construct($markup, $tokens, $parser);
    }

    /**
    * {@inheritdoc}
    */
    public function parse(array &$tokens): void
    {
        $source = $this->parser->getLoader()->read($this->templateName);
        $cache = $this->parser->getTemplate()->getCache();

        $mainTokens = [];

        $this->hash = md5($this->templateName);

        /** @var Document|false $document */
        $document = $cache->read($this->hash, true);

        if ($document === false || ($document->hasIncludes())) {
            $mainTokens = $this->parser->tokenize($source);
            $this->document = new Document($mainTokens, $this->parser);
            $cache->write($this->hash, $this->document, true);
        } else {
            $this->document = $document;
        }
    }


    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        $result = '';
        $variable = $context->get($this->variable);

        $context->push();
        foreach ($this->attributes as $key => $value) {
            $context->set($key, $context->get($value));
        }

        if ($this->isCollection && is_array($variable)) {
            foreach ($variable as $item) {
                $context->set($this->templateName, $item);
                $result .= $this->document->render($context);
            }
        } else {
            if (!empty($this->variable)) {
                $context->set($this->templateName, $variable);
            }

            $result .= $this->document->render($context);
        }

        $context->pop();

        return $result;
    }

    /**
     * Check for cached includes;
     * if there are - do not use cache
     * @return bool
     */
    public function hasIncludes(): bool
    {
        if ($this->document->hasIncludes()) {
            return true;
        }

        $cache = $this->parser->getTemplate()->getCache();
        $hash = md5($this->templateName);
        if ($cache->exists($hash) && $this->hash === $hash) {
            return false;
        }

        return true;
    }
}
