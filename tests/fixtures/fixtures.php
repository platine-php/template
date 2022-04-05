<?php

declare(strict_types=1);

namespace Platine\Test\Fixture;

use Countable;
use Platine\Template\Parser\AbstractBlock;
use Platine\Template\Parser\AbstractFilter;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Drop;
use stdClass;

class CustomTag extends AbstractTag
{
    protected string $name = 'tnh';

    public function render(Context $context): string
    {
        return __CLASS__;
    }
}

class CustomBlock extends AbstractBlock
{
    protected string $name = 'tnh';
}

class ContextObjectToObject
{
    public function toObject()
    {
        $o = new stdClass();
        $o->toObject = __CLASS__;

        return $o;
    }
}

class IfTagObjectToString
{
    public function __toString()
    {
        return __CLASS__;
    }
}

class IfTagObjectWithoutToString
{
}

class ContextObjectToArray
{
    public function toArray()
    {
        return [
            'bar' => new ContextDrop(),
            'myarr' => new ContextObjectToObject(),
        ];
    }
}

class ContextObjectToArrayNull
{
    public function toArray()
    {
        return null;
    }
}

class ContextObjectToArrayInteger
{
    public function toArray()
    {
        return 12;
    }
}

class ContextDrop extends Drop
{
    public function foo(): string
    {
        return get_class($this);
    }

    public function hasKey(string $key): bool
    {
        return $key === 'baz' ? false : true;
    }
}

class ContextCountable implements Countable
{
    public function count(): int
    {
        return 100;
    }
}

class ContextMethodCallGetMagicGet
{
    public function mymethod()
    {
        return __CLASS__;
    }

    public function __get($name)
    {
        if ($name === 'bar') {
            return $name . '_foo';
        }

        return null;
    }
}

class DropTestClass extends Drop
{
    public function myMethod(): string
    {
        return get_class($this);
    }
}

class CustomFilter extends AbstractFilter
{
    public static function char($param)
    {
        if (is_string($param)) {
            return $param[0];
        }

        return $param;
    }

    public static function strictType(int $param): int
    {
        return $param * 2;
    }

    private static function notCallable($param)
    {
        if (is_string($param)) {
            return $param[0];
        }

        return $param;
    }

    public static function charAt($param, $index = 0)
    {
        if (is_string($param) && isset($param[$index])) {
            return $param[$index];
        }

        return $param;
    }
}

class StringFilterLengthTestClass
{
    public function size(): int
    {
        return 234;
    }
}

function func_return_generator()
{
    for ($i = 0; $i <= 2; $i++) {
        yield $i;
    }
}
