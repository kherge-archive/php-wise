<?php

namespace Herrera\Wise\Tests\Util;

use Herrera\PHPUnit\TestCase;
use Herrera\Wise\Util\ArrayUtil;

class ArrayUtilTest extends TestCase
{
    public function testFlatten()
    {
        $this->assertEquals(
            array(
                'one' => 1,
                'sub.two' => 2,
                'sub.sub.three' => 3
            ),
            ArrayUtil::flatten(
                array(
                    'one' => 1,
                    'sub' => array(
                        'two' => 2,
                        'sub' => array(
                            'three' => 3
                        )
                    )
                )
            )
        );
    }

    public function testWalkRecursive()
    {
        $expected = $actual = array(
            'one' => array(
                'two' => array(
                    'three' => array(
                        'four' => 'eight',
                        'twelve' => 'thirteen',
                    ),
                    'five' => 'nine',
                ),
                'six' => 'ten',
            ),
            'seven' => 'eleven',
        );

        ArrayUtil::walkRecursive(
            $actual,
            function (&$value, $key, &$array) {
                if ('four' === $key) {
                    unset($array[$key]);

                    $array['changed'] = $value;
                }
            }
        );

        unset($expected['one']['two']['three']['four']);

        $expected['one']['two']['three']['changed'] = 'eight';

        $this->assertSame($expected, $actual);
    }
}
