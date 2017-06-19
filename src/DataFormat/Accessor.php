<?php

namespace Caster\DataFormat;

use Caster\Config;
use Caster\Exception\ConfigException;
use Caster\Exception\ExceptionInterface;

class Accessor
{
    private static $instances = [];

    public static function getInstance($schema)
    {
        if (!isset(self::$instances[$schema])) {
            self::$instances[$schema] = self::createInstance($schema);
        }
        return self::$instances[$schema];
    }

    protected static function createInstance($schema)
    {
        $dataFormatDir = Config::get('data_format_dir');
        $dataFormatNamespace = Config::get('data_format_namespace');
        if (empty($dataFormatDir) || !is_dir($dataFormatDir) || empty($dataFormatNamespace)) {
            throw new ConfigException(
                'data_format configuration is invalid',
                ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
            );
        }

        $namespaces = explode("\\", $schema);
        $upperNamespaces = array_map(function ($namespace) {
            return ucfirst($namespace);
        }, $namespaces);
        $fullClassName = sprintf("\\%s\\%s", $dataFormatNamespace, implode("\\", $upperNamespaces));

        return new $fullClassName();
    }
}
