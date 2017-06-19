<?php

namespace Caster;

use Caster\Exception\QueryException;

class Query
{
    private $query = '';

    public function __construct($baseQuery)
    {
        $this->query = $baseQuery;
    }

    public function setTableName($tableName)
    {
        $this->query = str_replace('__TABLE_NAME__', $tableName, $this->query);
    }

    public function setLimit($limit, $offset = null)
    {
        if (!empty($offset)) {
            $offset = 0;
        }
        $this->query = sprintf('%s LIMIT %d, %d', $this->query, $offset, $limit);
    }

    public function setParameters($params, \Mysqli $conn)
    {
        $this->query = $this->bindListParams($this->query, $params, $conn);
        $this->query = $this->bindParams($this->query, $params, $conn);
        $this->query = $this->replaceEscapedColon($this->query);
    }

    public function getQueryStr()
    {
        $this->query = trim($this->query);
        return $this->query;
    }

    private function bindListParams($query, $params, \Mysqli $conn)
    {
        $pattern = '/:([a-zA-Z0-9_]+)<(.*?)>/s';
        if (!preg_match_all($pattern, $query, $matches)) {
            return $query;
        }

        foreach ($matches[0] as $key => $match) {
            $listKey = $matches[1][$key];
            if (!isset($params[$listKey])) {
                throw new QueryException(
                    sprintf('list key is not found : %s', $listKey)
                );
            }

            $listParams = $params[$listKey];
            $listQuery = $matches[2][$key];
            $listQueries = [];
            foreach ($listParams as $listParam) {
                $q = $this->bindParams($listQuery, $listParam, $conn);
                $listQueries[] = '(' . $q . ')';
            }

            $listQueryStr = implode(',', $listQuery);
            $query = str_replace($match, $listQueryStr, $query);
        }

        return $query;
    }

    private function bindParams($query, $params, \Mysqli $conn)
    {
        if (!preg_match_all('/[\\\]{0,1}:([a-zA-Z0-9_]+)/s', $query, $matches)) {
            return $query;
        }

        foreach ($matches[2] as $key) {
            if ($matches[1][$key] == '\\:') {
                continue;
            }

            if (!isset($params[$key])) {
                throw new QueryException(
                    sprintf('parameter key is not found : %s', $key)
                );
            }
            $value = $params[$key];
            $query = $this->replaceBindParams($query, $key, $value, $conn);
        }

        return $query;
    }

    private function replaceBindParams($query, $key, $value, \Mysqli $conn)
    {
        $val = sprintf("'%s'", $conn->real_escape_string($value));
        return str_replace(':' . $key, $val, $query);
    }

    private function replaceEscapedColon($query)
    {
        return str_replace('\\:', ':', $query);
    }
}
