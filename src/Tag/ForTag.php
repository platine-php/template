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
 *  @file ForTag.php
 *
 *  The "for" Template tag class
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

use Generator;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractBlock;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;
use Traversable;

/**
 * Class ForTag
 * @package Platine\Template\Tag
 */
class ForTag extends AbstractBlock
{
    /**
     * Type digit
     */
    protected const TYPE_DIGIT = 1;

    /**
     * Type collection
     */
    protected const TYPE_COLLECTION = 2;

    /**
     * The collection name to loop over
     * @var string
     */
    protected string $collectionName;

    /**
     * The variable name to assign collection elements to
     * @var string
     */
    protected string $variableName;

    /**
     * The name of the loop, which is a
     * compound of the collection and variable names
     * @var string
     */
    protected string $name;

    /**
     * The type of the loop (collection or digit)
     * @var int
     */
    protected int $type = self::TYPE_COLLECTION;

    /**
     * The loop start value
     * @var int|string
     */
    protected $start;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        parent::__construct($markup, $tokens, $parser);

        $lexerCollection = new Lexer('/(\w+)\s+in\s+(' . Token::VARIABLE_NAME . ')/');
        if ($lexerCollection->match($markup)) {
            $this->variableName = $lexerCollection->getStringMatch(1);
            $this->collectionName = $lexerCollection->getStringMatch(2);
            $this->name = $this->variableName . '-' . $this->collectionName;
            $this->extractAttributes($markup);
        } else {
            $lexerDigit = new Lexer(
                '/(\w+)\s+in\s+\((\d+|'
                . Token::VARIABLE_NAME
                . ')\s*\.\.\s*(\d+|'
                . Token::VARIABLE_NAME
                . ')\)/'
            );
            if ($lexerDigit->match($markup)) {
                $this->type = self::TYPE_DIGIT;
                $this->variableName = $lexerDigit->getStringMatch(1);
                $this->start = $lexerDigit->getStringMatch(2);
                $this->collectionName = $lexerDigit->getStringMatch(3);
                $this->name = $this->variableName . '-digit';
                $this->extractAttributes($markup);
            } else {
                throw new ParseException(sprintf(
                    'Syntax Error in "%s" loop - Valid syntax: for [item] in [collection]',
                    'for'
                ));
            }
        }
    }

    /**
    * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        if (!$context->hasRegister('for')) {
            $context->setRegister('for', []);
        }

        if ($this->type === self::TYPE_DIGIT) {
            return $this->renderDigit($context);
        }

        return $this->renderCollection($context);
    }

    /**
     * Render for type "digit"
     * @param Context $context
     * @return string
     */
    protected function renderDigit(Context $context): string
    {
        /** @var int $start */
        $start = $this->start;

        if (!is_int($start)) {
            $start = (int) $context->get($start);
        }

        $end = $this->collectionName;
        if (!is_numeric($end)) {
            $end = (int) $context->get($end);
        } else {
            $end = (int) $this->collectionName;
        }

        $range = [$start, $end];

        $context->push();
        $result = '';
        /** @var int $index */
        $index = 0;
        $length = $range[1] - $range[0] + 1;
        for ($i = $range[0]; $i <= $range[1]; $i++) {
            $context->set($this->variableName, $i);
            $context->set('forloop', [
                'name' => $this->name,
                'length' => $length,
                'index' => $index + 1,
                'index0' => $index,
                'rindex' => $length - $index,
                'rindex0' => $length - $index - 1,
                'first' => ((int)$index === 0),
                'last' => ((int)$index === ($length - 1)),
            ]);

            $result .= $this->renderAll($this->nodeList, $context);

            $index++;

            if ($context->hasRegister('break')) {
                $context->clearRegister('break');
                break;
            }

            if ($context->hasRegister('continue')) {
                $context->clearRegister('continue');
            }
        }

        $context->pop();

        return $result;
    }

    /**
     * Render for type "collection"
     * @param Context $context
     * @return string
     */
    protected function renderCollection(Context $context): string
    {
        $collection = $context->get($this->collectionName);

        if ($collection instanceof Generator && !$collection->valid()) {
            return '';
        }

        if ($collection instanceof Traversable) {
            $collection = iterator_to_array($collection);
        }

        if ($collection === null || !is_array($collection) || count($collection) === 0) {
            return '';
        }

        $range = [0, count($collection)];
        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            /** @var int $offset */
            $offset = 0;
            if (isset($this->attributes['offset'])) {
                $forRegister = $context->getRegister('for');
                $offset =  ($this->attributes['offset'] === 'continue')
                          ? (isset($forRegister[$this->name])
                                ? (int) $forRegister[$this->name]
                                : 0)
                          : (int) $context->get($this->attributes['offset']);
            }

            /** @var int|null $limit */
            $limit = (isset($this->attributes['limit']))
                          ? (int) $context->get($this->attributes['limit'])
                          : null;

            $rangeEnd = ($limit !== null) ? $limit : count($collection) - $offset;
            $range = [$offset, $rangeEnd];

            $context->setRegister('for', [$this->name => $rangeEnd + $offset]);
        }

        $result = '';
        $segment = array_slice($collection, $range[0], $range[1]);
        if (count($segment) <= 0) {
            return '';
        }

        $context->push();
        $length = count($segment);
        $index = 0;
        foreach ($segment as $key => $item) {
            $value = is_numeric($key) ? $item : [$key, $item];
            $context->set($this->variableName, $value);
            $context->set('forloop', [
                'name' => $this->name,
                'length' => $length,
                'index' => $index + 1,
                'index0' => $index,
                'rindex' => $length - $index,
                'rindex0' => $length - $index - 1,
                'first' => ((int)$index === 0),
                'last' => ((int)$index === ($length - 1)),
            ]);

            $result .= $this->renderAll($this->nodeList, $context);

            $index++;

            if ($context->hasRegister('break')) {
                $context->clearRegister('break');
                break;
            }

            if ($context->hasRegister('continue')) {
                $context->clearRegister('continue');
            }
        }

        $context->pop();

        return $result;
    }
}
