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
    }

    public static function initialize()
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
}
