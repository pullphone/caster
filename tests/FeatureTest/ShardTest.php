<?php

namespace Caster\Tests\FeatureTest;

use Caster\Caster;

class ShardTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $caster = Caster::get('SampleShard');
        for ($i = 0; $i < 10; $i++) {
            $caster->exec('drop_table', ['id' => $i]);
            $caster->exec('create_table', ['id' => $i]);
        }

        $res = $caster->exec('insert', ['id' => 1, 'key' => 'test_key', 'value' => 'aaaa']);
        $this->assertSame(1, $res);
        $res = $caster->exec('insert_multi', [
            'list' => [
                ['id' => 2, 'key' => 'test_key_2', 'value' => 'bbbb'],
                ['id' => 12, 'key' => 'test_key_3', 'value' => 'cccc'],
                ['id' => 22, 'key' => 'test_key_4', 'value' => 'dddd'],
            ],
        ], [
            'id' => 2,
        ]);
        $this->assertSame(3, $res);

        // wait 0.5 sec for replication delay
        usleep(500000);
    }

    public function testFindFirst()
    {
        $caster = Caster::get('SampleShard');

        $res = $caster->findFirst('find_by_id', ['id' => 1], true);
        $this->assertNotEmpty($res);
        $this->assertArrayHasKey('id', $res);
        $this->assertArrayHasKey('key', $res);
        $this->assertArrayHasKey('value', $res);
        $this->assertArrayHasKey('updated_at', $res);
        $this->assertArrayHasKey('created_at', $res);

        $res = $caster->findFirst('find_by_id', ['id' => 1]);
        $this->assertNotEmpty($res);
    }

    public function testFind()
    {
        $caster = Caster::get('SampleShard');

        $res = $caster->find('find_all', ['id' => 2], null, null, true);
        $this->assertNotEmpty($res);
        $this->assertCount(3, $res);
        foreach ($res as $r) {
            $this->assertArrayHasKey('id', $r);
            $this->assertArrayHasKey('key', $r);
            $this->assertArrayHasKey('value', $r);
            $this->assertArrayHasKey('updated_at', $r);
            $this->assertArrayHasKey('created_at', $r);
        }

        $res = $caster->find('find_all', ['id' => 2]);
        $this->assertNotEmpty($res);
        $this->assertCount(3, $res);

        $res = $caster->find('find_by_ids', ['ids' => [2, 12, 22], 'id' => 2], null, null, true);
        $this->assertNotEmpty($res);
        $this->assertCount(3, $res);
    }

    public function testFindEx()
    {
        $caster = Caster::get('SampleShard');

        $res = $caster->findEx('standby', 'find_all', ['id' => 2], null, null);
        $this->assertNotEmpty($res);
        $this->assertCount(3, $res);
    }
}
