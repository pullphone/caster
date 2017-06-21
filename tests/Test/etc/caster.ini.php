<?php
return [
    'data_format_dir' => __DIR__ . '/../DataFormat',
    'data_format_namespace' => 'Caster\\Tests\\Test\\DataFormat',
    'default' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test',
            'user' => 'root',
            'password' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test',
            'user' => 'root',
            'password' => 'root',
        ],
    ],
    'test_0' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test_1',
            'user' => 'root',
            'password' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test_1',
            'user' => 'root',
            'password' => 'root',
        ],
    ],
    'test_1' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test_2',
            'user' => 'root',
            'password' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test_2',
            'user' => 'root',
            'password' => 'root',
        ],
    ],
];