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
}