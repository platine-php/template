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
 *  @file ExtendsTag.php
 *
 *  The "extends" Template tag class
 *
 *  @package    Platine\Template\Tag
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
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
 * Class ExtendsTag
 * @package Platine\Template\Tag
 */
class ExtendsTag extends AbstractTag
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
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/("[^"]+"|\'[^\']+\')?/');
        if ($lexer->match($markup) && $lexer->isMatchNotNull(1)) {
            $this->templateName = substr(
                $lexer->getStringMatch(1),
                1,
                strlen($lexer->getStringMatch(1)) - 2
            );
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: extends "template name"',
                'extends'
            ));
        }

        parent::__construct($markup, $tokens, $parser);
    }

    /**
    * {@inheritdoc}
    */
    public function parse(&$tokens): void
    {
        $source = $this->parser->getLoader()->read($this->templateName);
        $mainTokens = $this->parser->tokenize($source);

        $lexerExtends = new Lexer(
            '/^' . Token::BLOCK_OPEN
            . '\s*extends (.*)?'
            . Token::BLOCK_CLOSE . '$/'
        );

        $match = null;
        foreach ($mainTokens as $mainToken) {
            if ($lexerExtends->match($mainToken)) {
                $match = $lexerExtends->getStringMatch(1);
                break;
            }
        }

        $result = [];
        if ($match !== null) {
            $result = array_merge($mainTokens, $tokens);
        } else {
            $childrenTokens = $this->findBlocks($tokens);

            $lexerBlockStart = new Lexer(
                '/^' . Token::BLOCK_OPEN
                . '\s*block (\w+)\s*(.*)?'
                . Token::BLOCK_CLOSE . '$/'
            );

            $lexerBlockEnd = new Lexer(
                '/^' . Token::BLOCK_OPEN
                . '\s*endblock\s*?'
                . Token::BLOCK_CLOSE . '$/'
            );

            $name = null;
            $keep = false;
            $count = count($mainTokens);
            for ($i = 0; $i < $count; $i++) {
                if ($lexerBlockStart->match($mainTokens[$i])) {
                    $name = $lexerBlockStart->getStringMatch(1);
                    if (isset($childrenTokens[$name])) {
                        $keep = true;
                        array_push($result, $mainTokens[$i]);
                        foreach ($childrenTokens[$name] as $item) {
                            array_push($result, $item);
                        }
                    }
                }

                if (!$keep) {
                    array_push($result, $mainTokens[$i]);
                }

                if ($lexerBlockEnd->match($mainTokens[$i]) && $keep) {
                    $keep = false;
                    array_push($result, $mainTokens[$i]);
                }
            }
        }

        $cache = $this->parser->getTemplate()->getCache();

        $this->hash = md5($this->templateName);

        /** @var Document|false $document */
        $document = $cache->read($this->hash, true);

        if ($document === false || ($document->hasIncludes())) {
            $this->document = new Document($result, $this->parser);
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
        $context->push();
        $result = $this->document->render($context);
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

    /**
     * Find all defined blocks
     * @param array<int, mixed> $tokens
     * @return array<string, array<string>>
     */
    protected function findBlocks(array $tokens): array
    {
        $lexerBlockStart = new Lexer(
            '/^' . Token::BLOCK_OPEN
            . '\s*block (\w+)\s*(.*)?'
            . Token::BLOCK_CLOSE . '$/'
        );

        $lexerBlockEnd = new Lexer(
            '/^' . Token::BLOCK_OPEN
            . '\s*endblock\s*?'
            . Token::BLOCK_CLOSE . '$/'
        );

        $result = [];
        $name = null;
        foreach ($tokens as $token) {
            if ($lexerBlockStart->match($token)) {
                $name = $lexerBlockStart->getStringMatch(1);
                $result[$name] = [];
            } elseif ($lexerBlockEnd->match($token)) {
                $name = null;
            } else {
                if ($name !== null) {
                    array_push($result[$name], $token);
                }
            }
        }

        return $result;
    }
}
