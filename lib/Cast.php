<?php

namespace Lib;

use DateTime;
use InvalidArgumentException;

class Cast
{
    static public function to($type, $value)
    {
        $casted = null;
        switch ($type) {
            case 'int':
                $casted = static::toInt($value);
                break;
            case 'double':
                $casted = static::toDouble($value);
                break;
            case 'bool':
                $casted = static::toBool($value);
                break;
            case 'string':
                $casted = static::toString($value);
                break;
            case 'array':
                $casted = static::toArray($value);
                break;
            case 'date':
                $casted = static::toDate($value);
                break;
            default:
                throw new InvalidArgumentException("Arg1: {$type} is not a defined case.");
                break;
        }
        return $casted;
    }

    static protected function toInt($value)
    {
        return (int)$value;
    }

    static protected function toBool($value)
    {
        return (bool)$value;
    }

    static protected function toDouble($value)
    {
        return (double)$value;
    }

    static protected function toString($value)
    {
        return (string)$value;
    }

    static protected function toArray($value)
    {
        return (array)$value;
    }

    static protected function toDate($value)
    {
        return new DateTime($value);
    }
}
