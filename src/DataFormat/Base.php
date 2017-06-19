<?php

namespace Caster\DataFormat;

use Caster\Connection\Handler;
use Caster\Exception\DataFormatException;
use Caster\Exception\ExceptionInterface;
use Caster\Query;

abstract class Base
{
    protected $database = 'default';
    protected $table = 'table_name';
    protected $queries = [];

    public function find(
        string $queryName,
        array $params = null,
        int $limit = null,
        int $offset = null,
        bool $useMaster = false
    ) {
        $baseQuery = $this->getBaseQuery($queryName);
        $query = new Query($baseQuery);
        $query->setTableName($this->getTableName($params));
        if ($limit > 0) {
            $query->setLimit($limit, $offset);
        }

        $database = $this->getDatabaseName($params);
        $dbType = $useMaster ? 'master' : 'slave';
        $conn = Handler::connect($database, $dbType);
        $query->setParameters($params, $conn);

        $result = $conn->query($query->getQueryStr());

        if ($result instanceof \mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return $result;
    }

    public function findFirst(string $queryName, array $params = null, $useMaster = false)
    {
        $res = $this->find($queryName, $params, 1, 0, $useMaster);
        if (is_array($res) && count($res) > 0) {
            return array_shift($res);
        }
        return [];
    }

    public function exec(string $queryName, array $params = null, array $hint = null)
    {
        if (is_array($params) && is_array($hint)) {
            $params += $hint;
        }

        $baseQuery = $this->getBaseQuery($queryName);
        $query = new Query($baseQuery);
        $query->setTableName($this->getTableName($params));

        $database = $this->getDatabaseName($params);
        $conn = Handler::connect($database, 'master');
        $query->setParameters($params, $conn);

        $result = $conn->real_query($query->getQueryStr());

        if (!$result) {
            return 0;
        }
        return (int)$conn->affected_rows;
    }

    protected function getBaseQuery($queryName)
    {
        if (!isset($this->queries[$queryName])) {
            throw new DataFormatException(
                sprintf('%s is not found in %s', $queryName, __CLASS__),
                ExceptionInterface::EXCEPTION_CODE_INVALID_CONFIGURATION
            );
        }
        return $this->queries[$queryName];
    }

    protected function getTableName($params)
    {
        $sharding = $this->getTableSharding($params);
        if (empty($sharding)) {
            return $this->table;
        }

        return sprintf($this->table, $sharding);
    }

    protected function getTableSharding($params)
    {
        return null;
    }

    protected function getDatabaseName($params)
    {
        $sharding = $this->getDatabaseSharding($params);
        if (empty($sharding)) {
            return $this->database;
        }

        return sprintf($this->database, $sharding);
    }

    protected function getDatabaseSharding($params)
    {
        return null;
    }
}