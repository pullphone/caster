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
        $this->assertEquals('SELECT * FROM __TABLE_NAME__ WHERE key = :key', trim($query));

        $accessor2 = Accessor::getInstance('sample');
        $this->assertSame($accessor, $accessor2);
    }

    public function testGetInstance2()
    {
        $accessor = Accessor::getInstance('SampleShard');

        $db = $accessor->getDatabaseName(['id' => 1]);
        $table = $accessor->getTableName(['id' => 1, 'value' => 5]);
        $this->assertEquals('test_1', $db);
        $this->assertEquals('sample_shard_01_05', $table);

        $db = $accessor->getDatabaseName(['id' => 14]);
        $table = $accessor->getTableName(['id' => 24, 'value' => 32]);
        $this->assertEquals('test_0', $db);
        $this->assertEquals('sample_shard_04_02', $table);
    }
}
