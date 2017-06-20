<?php

namespace Caster;

use Caster\Exception\ConfigException;
use Caster\Exception\ExceptionInterface;

class Config
{
    private static $configDir = __DIR__;
    private static $data = [];

    const CONFIG_FILE_NAME = 'caster.ini.php';

    public static function setConfigDir($dirName)
    {
        self::$configDir = $dirName;
        self::load();
    }

    private static function load()
    {
        $configFilePath = sprintf('%s/%s', self::$configDir, self::CONFIG_FILE_NAME);
        if (!file_exists($configFilePath)) {
            throw new ConfigException(
                sprintf('%s is not found', $configFilePath),
                ExceptionInterface::EXCEPTION_CODE_FILE_NOT_FOUND
            );
        }

        self::$data = require $configFilePath;
    }

    public static function get($key)
    {
        if (empty(self::$data[$key])) {
            return null;
        }
        return self::$data[$key];
    }

    public static function getConnectionConfig($database, $dbType)
    {
        $config = self::get($database);
        if (empty($config)) {
            throw new ConfigException(
                sprintf('cannot find database configuration : %s', $database)
            );
        }

        if (empty($config['master'])) {
            throw new ConfigException(
                sprintf('cannot find database typed configuration : %s[master]', $database)
            );
        }

        if (empty($config[$dbType])) {
            throw new ConfigException(
                sprintf('cannot find database typed configuration : %s[%s]', $database, $dbType)
            );
        }

        $masterConfig = $config['master'];
        $typedConfig = $config[$dbType];
        $pass = $masterConfig['password'] ?? null;
        $charset = $masterConfig['charset'] ?? 'utf8';
        $db = $typedConfig['database'] ?? $masterConfig['database'];
        $host = $typedConfig['host'] ?? $masterConfig['host'];
        $user = $typedConfig['user'] ?? $masterConfig['user'];
        $pass = $typedConfig['password'] ?? $pass;
        $charset = $typedConfig['charset'] ?? $charset;

        if (is_array($host)) {
            shuffle($host);
            $host = array_shift($host);
        }

        list($host, $port) = explode(':', $host);

        return [
            'db' => $db,
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $pass,
            'charset' => $charset
        ];
    }
}
