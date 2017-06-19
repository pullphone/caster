<?php

namespace Caster\Tests\DataFormat;

class Sample extends \Caster\DataFormat\Base
{
    protected $database = 'default';
    protected $table = 'sample';

    protected $queries = [
        'create_table' => "
            CREATE TABLE IF NOT EXISTS __TABLE_NAME__ (
                `id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
                `key` VARCHAR(255) NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `created_at` TIMESTAMP DEFAULT '0000-00-00 00\\:00\\:00',
                PRIMARY KEY (id),
                UNIQUE KEY `key` (`key`)
            )
        ",
        'drop_table' => "DROP TABLE IF EXISTS __TABLE_NAME__",
        'insert' => "
            INSERT INTO __TABLE_NAME__ (
                `name`,
                `key`,
                `value`
            ) VALUES (
                :name,
                :key,
                :value
            )
        ",
        'insert_multi' => "
            INSERT INTO __TABLE_NAME__ (
                `name`,
                `key`,
                `value`
            ) VALUES :list<
                :name,
                :key,
                :value
            >
        ",
        'find_by_key' => "
            SELECT * FROM __TABLE_NAME__ WHERE key = :key
        ",
    ];
}