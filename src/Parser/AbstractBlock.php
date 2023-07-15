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
 *  @file AbstractBlock.php
 *
 *  The base Template block class
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

use InvalidArgumentException;
use Platine\Template\Exception\ParseException;
use Platine\Template\Exception\RenderException;

/**
 * Class AbstractBlock
 * @package Platine\Template\Parser
 */
class AbstractBlock extends AbstractTag
{
    /**
     * The node list
     * @var AbstractTag[]|Variable[]|string[]
     */
    protected array $nodeList = [];

    /**
     * Whenever next token should be ltrim'med.
     * @var bool
     */
    protected bool $trimWhitespace = false;

    /**
     * Return the node list
     * @return AbstractTag[]|Variable[]|string[]
     */
    public function getNodeList(): array
    {
        return $this->nodeList;
    }

    /**
     * {@inheritdoc}
    */
    public function parse(array &$tokens): void
    {
        $startRegex = '/^' . Token::BLOCK_OPEN . '/';
        $variableStartRegex = '/^' . Token::VARIABLE_OPEN . '/';
        $tagRegex = '/^'
                        . Token::BLOCK_OPEN
                        . Token::WHITESPACE_CONTROL
                        . '?\s*(\w+)\s*(.*?)'
                        . Token::WHITESPACE_CONTROL
                        . '?'
                        . Token::BLOCK_CLOSE
                        . '$/';

        $lexerStart = new Lexer($startRegex);
        $lexerVariableStart = new Lexer($variableStartRegex);
        $lexerTag = new Lexer($tagRegex);

        $this->nodeList = [];
        //Custom tags
        $tags = $this->parser->getTemplate()->getTags();
        while (count($tokens) > 0) {
            $token = (string) array_shift($tokens);
            if ($lexerStart->match($token)) {
                $this->whitespaceHandler($token);
                if ($lexerTag->match($token)) {
                    // If we found the proper block
                    // delimitor just end parsing here
                    // and let the outer block proceed
                    if ($lexerTag->getStringMatch(1) === $this->blockDelimiter()) {
                        $this->endTag();
                        return;
                    }

                    $node = null;
                    if (array_key_exists($lexerTag->getStringMatch(1), $tags)) {
                        $tagNameClass = $tags[$lexerTag->getStringMatch(1)];
                        if (is_string($tagNameClass)) {
                            $node = new $tagNameClass(
                                $lexerTag->getStringMatch(2),
                                $tokens,
                                $this->parser
                            );
                        } else {
                            $node = $tagNameClass;
                        }
                    } else {
                        //check for core tags
                        $coreTagName = $lexerTag->getStringMatch(1);
                        $coreTagNameClass = 'Platine\\Template\\Tag\\' . ucwords($coreTagName) . 'Tag';
                        if (class_exists($coreTagNameClass)) {
                            $node = new $coreTagNameClass(
                                $lexerTag->getStringMatch(2),
                                $tokens,
                                $this->parser
                            );
                        }
                    }

                    if ($node !== null) {
                        if (!$node instanceof AbstractTag) {
                            throw new InvalidArgumentException(sprintf(
                                'Tag class [%s] must extends base classes [%s] or [%s]',
                                get_class($node),
                                AbstractTag::class,
                                AbstractBlock::class
                            ));
                        }

                        $this->nodeList[] = $node;

                        if ($lexerTag->getStringMatch(1) === 'extends') {
                            return;
                        }
                    } else {
                        $this->unknownTag($lexerTag->getStringMatch(1), $lexerTag->getStringMatch(2), $tokens);
                    }
                } else {
                    throw new ParseException(sprintf(
                        'Tag [%s] was not properly terminated (won\'t match [%s])',
                        $token,
                        $lexerTag
                    ));
                }
            } elseif ($lexerVariableStart->match($token)) {
                $this->whitespaceHandler($token);
                $this->nodeList[] = $this->createVariable($token);
            } else {
                // This is neither a tag or a variable, proceed with an ltrim
                if ($this->trimWhitespace) {
                    $token = ltrim($token);
                }

                $this->trimWhitespace = false;
                $this->nodeList[] = $token;
            }
        }

        $this->assertMissingDelimiter();
    }

    /**
     * {@inheritdoc}
    */
    public function render(Context $context): string
    {
        return $this->renderAll($this->nodeList, $context);
    }

    /**
     * Renders all the given node list's nodes
     * @param AbstractTag[]|Variable[]|string[] $list
     * @param Context $context
     * @return string
     */
    protected function renderAll(array $list, Context $context): string
    {
        $result = '';
        foreach ($list as $token) {
            $value = $token;
            if (is_object($token)) {
                //Variable or AbstractTag
                $value = $token->render($context);
            }

            if (is_array($value)) {
                throw new RenderException('Implicit rendering of arrays not supported.'
                        . ' Use index operator.');
            }

            $result .= $value;

            if ($context->hasRegister('break') || $context->hasRegister('continue')) {
                break;
            }

            $context->tick();
        }

        return $result;
    }

    /**
     * An action to execute when the end tag is reached
     * @return void
     */
    protected function endTag(): void
    {
        //Do nothing now
    }

    /**
     * Handler for unknown tags
     * @param string $tag
     * @param string $param
     * @param array<int, string> $tokens
     * @return void
     * @throws ParseException
     */
    protected function unknownTag(string $tag, string $param, array $tokens): void
    {
        switch ($tag) {
            case 'else':
                throw new ParseException(sprintf(
                    '[%s] does not expect "else" tag',
                    $this->getTagName()
                ));
            case 'end':
                throw new ParseException(sprintf(
                    '"end" is not a valid delimiter for [%s] tags.Use [%s]',
                    $this->getName(),
                    $this->blockDelimiter()
                ));
            default:
                throw new ParseException(sprintf(
                    'Unknown template tag [%s]',
                    $tag
                ));
        }
    }

    /**
     * This method is called at the end
     * of parsing, and will throw an error unless this method is sub classed.
     * @return void
     * @throws ParseException
     */
    protected function assertMissingDelimiter(): void
    {
        throw new ParseException(sprintf(
            '[%s] tag was never closed',
            $this->getTagName()
        ));
    }

    /**
     * Returns the string that delimits the end of the block
     * @return string
     */
    protected function blockDelimiter(): string
    {
        return 'end' . $this->getTagName();
    }

    /**
     * Handle the white space.
     * @param string $token
     * @return void
     */
    protected function whitespaceHandler(string $token): void
    {
        $char = Token::WHITESPACE_CONTROL;
        if (substr($token, 2, 1) === $char) {
            $previousToken = end($this->nodeList);
            if (is_string($previousToken)) {
                // this can also be a tag or a variable
                $this->nodeList[key($this->nodeList)] = ltrim($previousToken);
            }
        }

        $this->trimWhitespace = substr($token, -3, 1) === $char;
    }

    /**
     * Create a variable for the given token
     * @param string $token
     * @return Variable
     * @throws ParseException
     */
    private function createVariable(string $token): Variable
    {
        $variableRegex = '/^'
                            . Token::VARIABLE_OPEN
                            . Token::WHITESPACE_CONTROL
                            . '?(.*?)'
                            . Token::WHITESPACE_CONTROL
                            . '?'
                            . Token::VARIABLE_CLOSE
                            . '$/';
        $lexer = new Lexer($variableRegex);

        if ($lexer->match($token)) {
            return new Variable($lexer->getStringMatch(1), $this->parser);
        }

        throw new ParseException(sprintf(
            'Variable [%s] was not properly terminated',
            $token
        ));
    }
}
