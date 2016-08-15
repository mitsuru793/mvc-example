<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Lib\Util;

class UtilTest extends TestCase
{
    public function testToCamelcase()
    {
        $data = [
            // expected, arg1
            ['hoge',     'hoge'],
            ['hoge',     '_hoge'],
            ['hogeBar',  'hoge_bar'],
            ['hoge',     'Hoge'],
            ['hogeBar',  'hogeBar'],
            ['hoge9bar', 'hoge9bar'],
            ['hoge9Bar', 'hoge9_bar']
        ];

        foreach ($data as $d) {
            $actual = Util::toCamelcase($d[1]);
            $this->assertEquals($d[0], $actual);
        }
    }

    public function testToSnakecase()
    {
        $data = [
            // expected, arg1
            ['hoge',      'hoge'],
            ['_hoge',     '_hoge'],
            ['hoge',      'Hoge'],
            ['hoge_bar',  'hogeBar'],
            ['hoge_bar',  'HogeBar'],
            ['hoge_bar',  'hoge_Bar'],
            ['hoge9bar',  'hoge9bar'],
            ['hoge9_bar', 'hoge9Bar']
        ];

        foreach ($data as $d) {
            $actual = Util::toSnakecase($d[1]);
            $this->assertEquals($d[0], $actual);
        }
    }
}
