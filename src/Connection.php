<?php

namespace Caster;

use Caster\Exception\ConnectionException;

class Connection
{
    private static $connections = [];

    public static function get($database, $dbType = null)
    {
        $key = self::getConnectionKey($database, $dbType);
        if (isset(self::$connections[$key])) {
            return self::$connections[$key];
        }

        $dbType = $dbType ?? 'master';
        $config = Config::getConnectionConfig($database, $dbType);
        $db = $config['db'];
        $host = $config['host'];
        $port = $config['port'];
        $user = $config['user'];
        $pass = $config['password'];
        $charset = $config['charset'];

        // persistent connection
        $pHost = 'p:' . $host;
        $connection = new \Mysqli($pHost, $user, $pass, $db, $port);
        if ($connection->connect_error) {
            throw new ConnectionException(
                sprintf('cannot connect database : %s(%s)', $connection->connect_error, $connection->connect_errno)
            );
        }

        $connection->set_charset($charset);
        self::$connections[$key] = $connection;
        return $connection;
    }

    private static function getConnectionKey($db, $type)
    {
        return sprintf('%s-%s', $db, $type);
    }
}
