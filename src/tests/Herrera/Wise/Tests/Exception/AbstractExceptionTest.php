<?php

namespace Herrera\Wise\Tests\Exception;

use Herrera\Wise\Exception\AbstractException;
use Herrera\PHPUnit\TestCase;

class AbstractExceptionTest extends TestCase
{
    public function testFormat()
    {
        $this->assertEquals(
            'Test message.',
            Exception::format('%s message.', 'Test')->getMessage()
        );
    }
}

class Exception extends AbstractException
{
}