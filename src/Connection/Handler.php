<?php

namespace Caster\Connection;

use Caster\Config;
use Caster\Exception\ConfigException;
use Caster\Exception\HandlerException;

class Handler
{
    private static $connections = [];

    public static function connect($database, $dbType = null)
    {
        $config = Config::get($database);
        if (empty($config)) {
            throw new ConfigException(
                sprintf('cannot find database configuration : %s', $database)
            );
        }

        $dbType = $dbType ?? 'master';
        list(
            $db,
            $host,
            $port,
            $user,
            $pass,
            $charset
        ) = self::determineConnection($config, $dbType);

        $key = self::getConnectionKey($database, $dbType);
        if (isset(self::$connections[$key])) {
            return self::$connections[$key];
        }

        // persistent connection
        $pHost = 'p:' . $host;
        $connection = new \Mysqli($pHost, $user, $pass, $db, $port);
        if ($connection->connect_error) {
            throw new HandlerException(
                sprintf('cannot connect database : %s(%s)', $connection->connect_error, $connection->connect_errno)
            );
        }

        $connection->set_charset($charset);
        self::$connections[$key] = $connection;
        return $connection;
    }

    private static function determineConnection($config, $dbType)
    {
        $selectConfig = $config['master'];
        $db = $selectConfig['database'];
        $host = $selectConfig['host'];
        $user = $selectConfig['user'];
        $pass = $selectConfig['pass'] ?? null;
        $charset = $selectConfig['charset'] ?? 'utf8';
        if (!empty($dbType) && $dbType != 'master' && isset($config[$dbType])) {
            $selectConfig = $config[$dbType];
            $db = $selectConfig['database'] ?? $db;
            $host = $selectConfig['host'] ?? $host;
            $user = $selectConfig['user'] ?? $user;
            $pass = $selectConfig['pass'] ?? $pass;
            if (is_array($host)) {
                shuffle($host);
                $host = array_shift($host);
            }
        }

        list($host, $port) = explode(':', $host);

        return [
            $db,
            $host,
            $port,
            $user,
            $pass,
            $charset,
        ];
    }

    private static function getConnectionKey($db, $type)
    {
        return sprintf('%s-%s', $db, $type);
    }
}
