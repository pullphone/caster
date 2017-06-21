<?php

namespace Caster\Tests\UnitTest;

use Caster\DataFormat\Accessor;

class DataFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance1()
    {
        $accessor = Accessor::getInstance('Sample');
        $db = $accessor->getDatabaseName([]);
        $table = $accessor->getTableName([]);

        $this->assertEquals('default', $db);
        $this->assertEquals('sample', $table);

        $query = $accessor->getBaseQuery('find_by_key');
        $this->assertEquals('SELECT * FROM __TABLE_NAME__ WHERE `key` = :key', trim($query));

        $accessor2 = Accessor::getInstance('sample');
        $this->assertSame($accessor, $accessor2);
    }

    public function testGetInstance2()
    {
        $accessor = Accessor::getInstance('SampleShard');

        $id = 1;
        $db = $accessor->getDatabaseName(['id' => $id]);
        $table = $accessor->getTableName(['id' => $id]);
        $this->assertEquals('test_1', $db);
        $this->assertEquals('sample_shard_01', $table);

        $id = 16;
        $db = $accessor->getDatabaseName(['id' => $id]);
        $table = $accessor->getTableName(['id' => $id]);
        $this->assertEquals('test_0', $db);
        $this->assertEquals('sample_shard_06', $table);
    }
}
