<?php

namespace Caster\Tests\Test\DataFormat;

class SampleShard extends \Caster\DataFormat\DataFormat
{
    protected $database = 'test_%d';
    protected $table = 'sample_shard_%02d_%02d';

    protected $queries = [
        'create_table' => "CREATE TABLE IF NOT EXISTS __TABLE_NAME__ (
          `id` BIGINT(20) UNSIGNED,
          `key` VARCHAR(255) NOT NULL,
          `value` BIGINT(20) NOT NULL,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (id),
          UNIQUE KEY `key` (`key`)
        )",
        'drop_table' => "DROP TABLE IF EXISTS __TABLE_NAME__",
        'insert' => "INSERT INTO __TABLE_NAME__ (
          `id`,
          `name`,
          `key`,
          `value`
        ) VALUES (
          :id,
          :name,
          :key,
          :value
        )",
        'insert_multi' => "
            INSERT INTO __TABLE_NAME__ (
              `id`,
              `name`,
              `key`,
              `value`
            ) VALUES :list<
              :id,
              :name,
              :key,
              :value
            >
        ",
        'find_by_key' => "
            SELECT * FROM __TABLE_NAME__ WHERE key = :key
        ",
        'find_by_id' => "
            SELECT * FROM __TABLE_NAME__ WHERE id = :id
        ",
        'find_by_ids' => "
            SELECT * FROM __TABLE_NAME__ WHERE id IN (:ids)
        ",
    ];

    protected function getDatabaseSharding($params)
    {
        return $params['id'] % 2;
    }

    protected function getTableSharding($params)
    {
        return [$params['id'] % 10, $params['value'] % 10];
    }
}