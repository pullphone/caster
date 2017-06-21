<?php

namespace Caster\Tests\UnitTest;

use Caster\Connection;
use Caster\Exception\ExceptionInterface;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $conn = Connection::get('default');
        $this->assertInstanceOf('Mysqli', $conn);
        $conn2 = Connection::get('default');
        $this->assertSame($conn, $conn2);
    }

    public function testGetFailed1()
    {
        $msg = null;
        $code = null;
        try {
            Connection::get('aaaa');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $code = $e->getCode();
        }

        $this->assertNotEmpty($msg);
        $this->assertNotEmpty($code);
        $this->assertEquals('cannot find database configuration : aaaa', $msg);
        $this->assertEquals(ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION, $code);
    }

    public function testGetFailed2()
    {
        $msg = null;
        $code = null;
        try {
            Connection::get('default', 'master1');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $code = $e->getCode();
        }

        $this->assertNotEmpty($msg);
        $this->assertNotEmpty($code);
        $this->assertEquals('cannot find database typed configuration : default[master1]', $msg);
        $this->assertEquals(ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION, $code);
    }
}
