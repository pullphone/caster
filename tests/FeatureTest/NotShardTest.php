<?php

namespace Caster\Tests\FeatureTest;

use Caster\Caster;

class NotShardTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $caster = Caster::get('Sample');
        $caster->exec('drop_table');
        $caster->exec('create_table');

        $res = $caster->exec('insert', ['key' => 'test_key', 'value' => 'aaaa']);
        $this->assertSame(1, $res);
        $res = $caster->exec('insert_multi', [
            'list' => [
                ['key' => 'test_key_2', 'value' => 'bbbb'],
                ['key' => 'test_key_3', 'value' => 'cccc'],
                ['key' => 'test_key_4', 'value' => 'dddd'],
            ],
        ]);
        $this->assertSame(3, $res);

        // wait 0.5 sec for replication delay
        usleep(500000);
    }

    public function testFindFirst()
    {
        $caster = Caster::get('Sample');

        $res = $caster->findFirst('find_by_id', ['id' => 1], true);
        $this->assertNotEmpty($res);
        $this->assertArrayHasKey('id', $res);
        $this->assertArrayHasKey('key', $res);
        $this->assertArrayHasKey('value', $res);
        $this->assertArrayHasKey('updated_at', $res);
        $this->assertArrayHasKey('created_at', $res);

        $res = $caster->findFirst('find_by_id', ['id' => 1]);
        $this->assertNotEmpty($res);
        $this->assertArrayHasKey('id', $res);
        $this->assertArrayHasKey('key', $res);
        $this->assertArrayHasKey('value', $res);
        $this->assertArrayHasKey('updated_at', $res);
        $this->assertArrayHasKey('created_at', $res);
    }

    public function testFind()
    {
        $caster = Caster::get('Sample');

        $res = $caster->find('find_by_key', ['key' => 'test_key_2'], null, null, true);
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);
        $this->assertArrayHasKey('id', $res[0]);
        $this->assertArrayHasKey('key', $res[0]);
        $this->assertArrayHasKey('value', $res[0]);
        $this->assertArrayHasKey('updated_at', $res[0]);
        $this->assertArrayHasKey('created_at', $res[0]);
        $res = $caster->find('find_by_key', ['key' => 'test_key_2']);
        $this->assertNotEmpty($res);
        $this->assertCount(1, $res);

        $res = $caster->find('find_by_ids', ['ids' => [1, 2, 3]], null, null, true);
        $this->assertnotempty($res);
        $this->assertcount(3, $res);

        $res = $caster->find('find_all', [], null, null, true);
        $this->assertNotEmpty($res);
        $this->assertCount(4, $res);
        foreach ($res as $r) {
            $this->assertArrayHasKey('id', $r);
            $this->assertArrayHasKey('key', $r);
            $this->assertArrayHasKey('value', $r);
            $this->assertArrayHasKey('updated_at', $r);
            $this->assertArrayHasKey('created_at', $r);
        }

        $res = $caster->find('find_all', []);
        $this->assertNotEmpty($res);
        $this->assertCount(4, $res);
    }

    public function testFindEx()
    {
        $caster = Caster::get('Sample');

        $res = $caster->findEx('standby', 'find_by_ids', ['ids' => [1, 2, 3]], null, null);
        $this->assertnotempty($res);
        $this->assertcount(3, $res);
    }
}
