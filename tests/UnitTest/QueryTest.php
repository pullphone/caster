<?php

namespace Caster\Tests\UnitTest;

use Caster\Connection;
use Caster\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    private $conn;

    public function setUp()
    {
        $this->conn = Connection::get('default');
    }

    public function testBuildQuery1()
    {
        $sql = 'SELECT * FROM __TABLE_NAME__ WHERE id = :id';

        $query = new Query($sql, 'sample');
        $query->setTableName('sample');
        $query->setLimit(10);
        $query->setOffset(0);
        $query->setParameters(['id' => 1]);
        $sql = $query->getEmulateQuery($this->conn);

        $assertSql = "SELECT * FROM sample WHERE id = '1' LIMIT 10 OFFSET 0";
        $this->assertEquals($assertSql, $sql);
    }

    public function testBuildQuery2()
    {
        $sql = 'SELECT * FROM __TABLE_NAME__ WHERE id IN (:ids)';

        $query = new Query($sql, 'sample2');
        $query->setTableName('sample2');
        $query->setLimit(10);
        $query->setOffset(0);
        $query->setParameters(['ids' => [1, 2, 3]]);
        $sql = $query->getEmulateQuery($this->conn);

        $assertSql = "SELECT * FROM sample2 WHERE id IN ('1', '2', '3') LIMIT 10 OFFSET 0";
        $this->assertEquals($assertSql, $sql);
    }

    public function testBuildQuery3()
    {
        $baseSql = <<<EOS
CREATE TABLE __TABLE_NAME__ (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  text VARCHAR(255) NOT NULL,
  time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  time2 TIMESTAMP DEFAULT '0000-00-00 00\\:00\\:00',
  PRIMARY KEY (id)
)
EOS;

        $query = new Query($baseSql, 'sample3');
        $query->setTableName('sample3');
        $query->setParameters([]);
        $sql = $query->getEmulateQuery($this->conn);

        $assertSql = preg_replace('/\s+/', ' ', $baseSql);
        $assertSql = preg_replace('/[\x5c]:/', ':', $assertSql);
        $assertSql = str_replace('__TABLE_NAME__', 'sample3', $assertSql);
        $this->assertEquals($assertSql, $sql);
    }

    public function testBuildQuery4()
    {
        $sql = <<<EOS
INSERT INTO __TABLE_NAME__ (
  text,
  time2
) VALUES :list<
  :text,
  :time2
>
EOS;

        $query = new Query($sql, 'sample4');
        $query->setTableName('sample4');
        $values = [
            ['text' => 'aaaa', 'time2' => '2017-01-01 00:00:00'],
            ['text' => 'bbbb', 'time2' => '2017-02-01 00:00:00'],
            ['text' => 'cccc', 'time2' => '2017-03-01 00:00:00'],
            ['text' => 'dddd', 'time2' => '2017-04-01 00:00:00'],
        ];
        $query->setParameters(['list' => $values]);
        $sql = $query->getEmulateQuery($this->conn);

        $valuesStr = implode(', ', array_map(function ($value) {
            return "( '{$value['text']}', '{$value['time2']}' )";
        }, $values));
        $assertSql = "INSERT INTO sample4 ( text, time2 ) VALUES {$valuesStr}";
        $this->assertEquals($assertSql, $sql);
    }
}
