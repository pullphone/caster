<?php

namespace Caster\DataFormat;

use Caster\Exception\DataFormatException;
use Caster\Exception\ExceptionInterface;

abstract class DataFormat
{
    protected $database = 'default';
    protected $table = 'table_name';
    protected $queries = [];

    public function getBaseQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            throw new DataFormatException(
                sprintf('%s is not found in %s', $queryName, __CLASS__),
                ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
            );
        }
        return $this->queries[$queryName];
    }

    public function getTableName($params)
    {
        $sharding = $this->getTableSharding($params);
        if (is_null($sharding)) {
            return $this->table;
        }

        if (!is_array($sharding)) {
            $sharding = [$sharding];
        }

        return vsprintf($this->table, $sharding);
    }

    protected function getTableSharding($params)
    {
        return null;
    }

    public function getDatabaseName($params)
    {
        $sharding = $this->getDatabaseSharding($params);
        if (is_null($sharding)) {
            return $this->database;
        }

        if (!is_array($sharding)) {
            $sharding = [$sharding];
        }

        return vsprintf($this->database, $sharding);
    }

    protected function getDatabaseSharding($params)
    {
        return null;
    }
}