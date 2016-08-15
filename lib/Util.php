<?php

namespace Lib;

class Util
{
    static function toCamelcase($str)
    {
        $str = ucwords($str, '_');
        $str = str_replace('_', '', $str);
        return lcfirst($str);
    }

    static function toSnakecase($str) {
        $pattern = '/[a-z]+(?=[A-Z])|[A-Z]+(?=[A-Z][a-z])|[0-9]+(?=[A-Z])/';
        $str = preg_replace($pattern, '$0_', $str);
        return strtolower($str);
    }
}
