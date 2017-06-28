<?php

namespace Caster\DataFormat;

use Caster\Exception\DataFormatException;
use Caster\Exception\ExceptionInterface;

abstract class DataFormat
{
    protected $database = 'default';
    protected $table = 'table_name';
    protected $queries = [];
    protected $databaseShardKeys = [];
    protected $tableShardKeys = [];

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
        $sharding = [];
        foreach ($this->tableShardKeys as $key => $shard) {
            if (!isset($params[$key])) {
                throw new DataFormatException(
                    sprintf('parameter : %s is not found', $key),
                    ExceptionInterface::EXCEPTION_CODE_INVALID_PARAMETER
                );
            }
            if (!is_int($shard)) {
                throw new DataFormatException(
                    sprintf('table sharding key : %s is not interger', $key),
                    ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
                );
            }
            $sharding[] = $params[$key] % $shard;
        }

        return $sharding;
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
        $sharding = [];
        foreach ($this->databaseShardKeys as $key => $shard) {
            if (!isset($params[$key])) {
                throw new DataFormatException(
                    sprintf('parameter : %s is not found', $key),
                    ExceptionInterface::EXCEPTION_CODE_INVALID_PARAMETER
                );
            }
            if (!is_int($shard)) {
                throw new DataFormatException(
                    sprintf('database sharding key : %s is not interger', $key),
                    ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
                );
            }
            $sharding[] = $params[$key] % $shard;
        }

        return $sharding;
    }
}