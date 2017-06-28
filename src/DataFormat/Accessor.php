<?php

namespace Caster\DataFormat;

use Caster\Config;
use Caster\Exception\ConfigException;
use Caster\Exception\ExceptionInterface;

class Accessor
{
    private static $instances = [];

    /**
     * @param $schema
     * @return DataFormat
     */
    public static function getInstance($schema)
    {
        $key = strtolower($schema);
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = self::createInstance($schema);
        }
        return self::$instances[$key];
    }

    private static function createInstance($schema)
    {
        $dataFormatDir = Config::get('data_format_dir');
        $dataFormatNamespace = Config::get('data_format_namespace');
        if (empty($dataFormatDir) || !is_dir($dataFormatDir) || empty($dataFormatNamespace)) {
            throw new ConfigException(
                'data_format configuration is invalid',
                ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
            );
        }

        $fullClassName = self::getFullClassName($dataFormatNamespace, $schema);
        if (!class_exists($fullClassName)) {
            throw new ConfigException(
                'data_format class is not found',
                ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
            );
        }
        return new $fullClassName();
    }

    private static function getFullClassName($dataFormatNamespace, $schema)
    {
        $namespaces = explode("\\", $schema);
        $upperNamespaces = array_map(function ($namespace) {
            return ucfirst($namespace);
        }, $namespaces);
        return sprintf("\\%s\\%s", $dataFormatNamespace, implode("\\", $upperNamespaces));
    }
}
