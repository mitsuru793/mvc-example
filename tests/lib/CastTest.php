<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

use DateTime;
use Lib\Cast;

class MyCast extends Cast
{
    static protected function toArray($value)
    {
        return explode(',', $value);
    }
}

class CastTest extends TestCase
{
    public function testTo()
    {
        $data = [
            // [arg1, expected, arg2]
            ['int',   1,     1],
            ['int',   1,     1.6],
            ['int',   1,     '1'],
            ['double', 0.0,   0],
            ['double', 1.6,   '1.6'],
            ['bool',  true,  true],
            ['bool',  true,  1],
            ['bool',  true,  '1'],
            ['bool',  false, false],
            ['bool',  false, ''],
            ['bool',  false, 0],
            ['bool',  false, null],
            ['string', '',''],
            ['string', '1','1'],
            ['string', '1',1],
            ['string', '1',true],
            ['string', '',false],
            ['string', '',null]
        ];

        foreach ($data as $d) {
            $expected = $d[1];
            $actual = MyCast::to($d[0], $d[2]);
            $this->assertSame($expected, $actual, implode(',', $d));
        }

        $expected = ['1', '2', '3'];
        $actual = MyCast::to('array', '1,2,3');
        $this->assertSame($expected, $actual);

        $timeStr = '2016-08-04 19:30:21 +09:00';
        $expected = new DateTime($timeStr);
        $actual = MyCast::to('date', $timeStr);
        $this->assertSame('2016-08-04 19:30:21', $actual->format('Y-m-d H:i:s'));
        $this->assertSame('+09:00', $actual->getTimezone()->getName());
    }
}
