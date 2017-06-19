<?php

namespace Caster\Tests\UnitTest;

use Caster\Caster;

class SampleTest extends \PHPUnit_Framework_TestCase
{
    public function test001()
    {
        $class = Caster::getAccessor('sample');
        $this->assertNotEmpty($class);
    }
}
