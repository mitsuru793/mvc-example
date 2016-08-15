<?php

namespace Lib;

use Lib\CastBase;

class Cast extends CastBase
{
    static protected function toString($value)
    {
        switch (gettype($value)) {
            case 'array':
                $value = implode(',', $value);
                break;
        }
        return $value;
    }
}
