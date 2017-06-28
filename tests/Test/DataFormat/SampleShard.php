<?php

namespace Caster\Tests\Test\DataFormat;

class SampleShard extends \Caster\DataFormat\DataFormat
{
    protected $database = 'test_%d';
    protected $table = 'sample_shard_%02d';
    protected $databaseShardKeys = ['id' => 2];
    protected $tableShardKeys = ['id' => 10];

    protected $queries = [
        'create_table' => "CREATE TABLE IF NOT EXISTS __TABLE_NAME__ (
          `id` BIGINT(20) UNSIGNED,
          `key` VARCHAR(255) NOT NULL,
          `value` VARCHAR(255) NOT NULL,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `created_at` TIMESTAMP DEFAULT '2000-01-01 00\\:00\\:00',
          PRIMARY KEY (id),
          UNIQUE KEY `key` (`key`)
        )",
        'drop_table' => "DROP TABLE IF EXISTS __TABLE_NAME__",
        'insert' => "INSERT INTO __TABLE_NAME__ (
          `id`,
          `key`,
          `value`,
          created_at
        ) VALUES (
          :id,
          :key,
          :value,
          CURRENT_TIMESTAMP
        )",
        'insert_multi' => "
            INSERT INTO __TABLE_NAME__ (
              `id`,
              `key`,
              `value`,
              created_at
            ) VALUES :list<
              :id,
              :key,
              :value,
              CURRENT_TIMESTAMP
            >
        ",
        'find_by_id' => "
            SELECT * FROM __TABLE_NAME__ WHERE id = :id
        ",
        'find_by_ids' => "
            SELECT * FROM __TABLE_NAME__ WHERE id IN (:ids)
        ",
        'find_by_key' => "
            SELECT * FROM __TABLE_NAME__ WHERE `key` = :key
        ",
        'find_all' => "
            SELECT * FROM __TABLE_NAME__
        ",
    ];
}