<?php
return [
    'data_format_dir' => __DIR__ . '/../DataFormat',
    'data_format_namespace' => 'Caster\\Tests\\DataFormat',
    'default' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test',
            'user' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test',
            'user' => 'root',
        ],
    ],
    'test_1' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test_1',
            'user' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test_1',
            'user' => 'root',
        ],
    ],
    'test_2' => [
        'master' => [
            'host' => '127.0.0.1:3306',
            'database' => 'caster_test_2',
            'user' => 'root',
        ],
        'slave' => [
            'host' => ['127.0.0.1:3307', '127.0.0.1:3308'],
            'database' => 'caster_test_2',
            'user' => 'root',
        ],
    ],
];