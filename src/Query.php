<?php

namespace Caster;

use Caster\Exception\QueryException;

/**
 * Class Query
 * @package Caster
 */
class Query
{
    const P_HOLDER_STMT      = 'stmt';
    const P_HOLDER_BIND      = 'bind';
    const P_HOLDER_NAME      = 'name';
    const BIND_TYPE          = 'type';
    const BIND_VAL           = 'value';
    const BIND_ORIG_TYPE     = 'orig-type';
    const BIND_ORIG_VAL      = 'orig-value';
    const VAR_TYPE_IS_NULL   = 'NULL';
    const VAR_TYPE_IS_BOOL   = 'boolean';
    const VAR_TYPE_IS_LONG   = 'integer';
    const VAR_TYPE_IS_DOUBLE = 'double';
    const VAR_TYPE_IS_STRING = 'string';

    private $query = '';
    private $schema = '';
    private $bindValues = [];
    private $bindValueDetails = [];
    private $limit = null;
    private $offset = null;

    public function __construct($baseQuery, $schema)
    {
        $this->query = $baseQuery;
        $this->schema = $schema;
    }

    public function setTableName($tableName)
    {
        $this->query = str_replace('__TABLE_NAME__', $tableName, $this->query);
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * setParameters
     *
     * @param $params
     *
     * from:
     * gree/cascade:Cascade_DB_SQL_Statement
     * https://github.com/gree/cascade/blob/develop/class/Cascade/DB/SQL/Statement.php
     */
    public function setParameters($params)
    {
        $pHolder = [];
        $query   = preg_replace('/\s+/', ' ', $this->query);
        $pHolder[self::P_HOLDER_STMT] = $query;
        $pHolder[self::P_HOLDER_NAME] = NULL;
        $pHolder[self::P_HOLDER_BIND] = [];
        preg_match_all('/[^\x5c](:([a-zA-Z0-9_]+)(?:<.+>)?)/', $query, $tokens);
        for ($pos = 0; $pos < count($tokens[0]); $pos++) {
            $ptr =& $pHolder[self::P_HOLDER_BIND][$pos];
            $ptr[self::P_HOLDER_STMT] = $tokens[1][$pos];
            $ptr[self::P_HOLDER_NAME] = $tokens[2][$pos];
            $ptr[self::P_HOLDER_BIND] = [];
        }
        // 行値構成子
        foreach ($pHolder[self::P_HOLDER_BIND] as $pos => &$bind) {
            $tmp = preg_replace('/:[a-zA-Z0-9_]+(?:<(.+)>)?/', '$1', $bind[self::P_HOLDER_STMT]);
            preg_match_all('/(?:^|[^\x5c])(:([a-zA-Z0-9_]+))/', $tmp, $tokens);
            for ($pos = 0; $pos < count($tokens[0]); $pos++) {
                $ptr =& $bind[self::P_HOLDER_BIND][$pos];
                $ptr[self::P_HOLDER_STMT] = $tokens[1][$pos];
                $ptr[self::P_HOLDER_NAME] = $tokens[2][$pos];
                $ptr[self::P_HOLDER_BIND] = [];
            }
        }
        unset($bind);
        // クエリー/バインド変数の設定
        $this->setupBindValue($pHolder, $params);
        $this->setupQuery($pHolder, $params);
    }

    /**
     * setupBindValue
     *
     * @param $pHolder
     * @param $params
     *
     * from:
     * gree/cascade:Cascade_DB_SQL_Statement
     * https://github.com/gree/cascade/blob/develop/class/Cascade/DB/SQL/Statement.php
     */
    private function setupBindValue($pHolder, $params)
    {
        // バインド値の設定
        foreach ($pHolder[self::P_HOLDER_BIND] as $bind) {
            $bindStmt  = $bind[self::P_HOLDER_STMT];
            $bindName  = $bind[self::P_HOLDER_NAME];
            if (array_key_exists($bindName, $params) === FALSE) {
                $msg  = 'Bind value was not specified {df, key} %s %s';
                throw new QueryException(
                    sprintf($msg, $this->schema, $bindName)
                );
            }
            $bindValue = is_array($params[$bindName])
                ? $params[$bindName]
                : array($params[$bindName]);
            if (preg_match('/^:[a-zA-Z0-9_]+<(.+)>$/', $bindStmt, $maches)) {
                // 行値構成子
                foreach ($bindValue as $_value) {
                    foreach ($bind[self::P_HOLDER_BIND] as $matchBind) {
                        $matchName  = $matchBind[self::P_HOLDER_NAME];
                        if (array_key_exists($matchName, $_value) === FALSE) {
                            $msg  = 'Bind value was not specified {df, key} %s %s';
                            throw new QueryException(
                                sprintf($msg, $this->schema, $matchName)
                            );
                        }
                        $matchValue = is_array($_value[$matchName])
                            ? $_value[$matchName]
                            : array($_value[$matchName]);
                        foreach ($matchValue as $__value) {
                            if ($__value === null) {
                                $this->addBindValue($__value, self::VAR_TYPE_IS_NULL);
                            } else {
                                $this->addBindValue($__value);
                            }
                        }
                    }
                }
            } else {
                foreach ($bindValue as $_value) {
                    if ($_value === null) {
                        // nullはそのまま
                        $this->addBindValue($_value, self::VAR_TYPE_IS_NULL);
                    } else {
                        // null以外は文字列に変換
                        $this->addBindValue($_value);
                    }
                }
            }
        }
        // クエリ条件設定 (最大取得件数)
        if ($this->limit !== NULL) {
            $this->addBindValue($this->limit, self::VAR_TYPE_IS_LONG);
        }
        // クエリ条件設定 (オフセット)
        if ($this->offset !== NULL) {
            $this->addBindValue($this->offset, self::VAR_TYPE_IS_LONG);
        }
    }

    /**
     * addBindValue
     *
     * @param $value
     * @param $type
     *
     * from:
     * gree/cascade:Cascade_DB_SQL_Statement
     * https://github.com/gree/cascade/blob/develop/class/Cascade/DB/SQL/Statement.php
     */
    private function addBindValue(
        $value,
        $type = self::VAR_TYPE_IS_STRING
    ) {
        // Z_TYPEの取得
        $orig      = $value;
        $origType = gettype($value);
        // 指定の型に変換
        switch ($type) {
            case self::VAR_TYPE_IS_NULL:
            case self::VAR_TYPE_IS_BOOL:
            case self::VAR_TYPE_IS_LONG:
            case self::VAR_TYPE_IS_DOUBLE:
                settype($value, $type);
                break;
            case self::VAR_TYPE_IS_STRING:
            {
                switch($origType) {
                    case self::VAR_TYPE_IS_NULL:
                        $value = 'NULL';
                        break;
                    case self::VAR_TYPE_IS_BOOL:
                        $value = $value ? '1' : '0';
                        break;
                    case self::VAR_TYPE_IS_LONG:
                    case self::VAR_TYPE_IS_DOUBLE:
                    case self::VAR_TYPE_IS_STRING:
                        settype($value, $type);
                        break;
                    default:
                        $msg = 'Invalid Z_TYPE of bind-value {z_type, value} %s %s';
                        throw new QueryException(
                            sprintf($msg, $type, $value)
                        );
                }
                break;
            }
            default:
                $msg = 'Unexpected the type of bind-value {type, value} %d %d';
                throw new QueryException(
                    sprintf($msg, $type, $value)
                );
        }
        // 内部変数に保存
        $this->bindValues[]        = $value;
        $this->bindValueDetails[] = [
            self::BIND_TYPE      => $type,
            self::BIND_VAL       => $value,
            self::BIND_ORIG_TYPE => $origType,
            self::BIND_ORIG_VAL  => $orig,
        ];
    }

    /**
     * setupQuery
     *
     * @param $pHolder
     * @param $params
     *
     * from:
     * gree/cascade:Cascade_DB_SQL_Statement
     * https://github.com/gree/cascade/blob/develop/class/Cascade/DB/SQL/Statement.php
     */
    private function setupQuery($pHolder, $params)
    {
        // クエリの設定
        $query = $pHolder[self::P_HOLDER_STMT];
        foreach ($pHolder[self::P_HOLDER_BIND] as $bind) {
            $bindStmt  = $bind[self::P_HOLDER_STMT];
            $bindName  = $bind[self::P_HOLDER_NAME];
            $bindValue = $params[$bindName];
            // 疑問符プレスフォルダに置き換え
            if (preg_match('/^:[a-zA-Z0-9_]+<(.+)>$/', $bindStmt, $matches)) {
                // 行値構成子
                $token = '('.$matches[1].')';
                foreach ($bind[self::P_HOLDER_BIND] as $matchBind) {
                    $matchStmt  = $matchBind[self::P_HOLDER_STMT];
                    $matchName  = $matchBind[self::P_HOLDER_NAME];
                    $_token = (is_array($bindValue[0][$matchName]))
                        ? '${1}?' . str_repeat(', ?', count($bindValue[0][$matchName]) - 1)
                        : '${1}?';
                    $pattern = sprintf('/(^|[^\x5c])%s/', preg_quote($matchStmt));
                    $token   = preg_replace($pattern, $_token, $token, 1);
                }
                $token  .= str_repeat(', ' . $token, count($bindValue) - 1);
                $fields  = '${1}' . $token;
            } else {
                $fields = '${1}?';
                if (is_array($bindValue)) {
                    $fields .= str_repeat(', ?', count($bindValue) - 1);
                }
            }
            $pattern = sprintf('/(^|[^\x5c])%s/', preg_quote($bindStmt));
            $query   = preg_replace($pattern, $fields, $query, 1);
        }
        // クエリ条件設定 (最大取得件数)
        if ($this->limit !== NULL) {
            $query .= ' LIMIT ?';
        }
        // クエリ条件設定 (オフセット)
        if ($this->offset !== NULL) {
            $query .= ' OFFSET ?';
        }

        $query = preg_replace('/\x5c:/', ':', $query);
        // クエリ登録
        $this->query = $query;
    }

    /**
     * getEmulateQuery
     *
     * @param \Mysqli $conn
     * @return string
     *
     * from:
     * gree/cascade:Cascade_Driver_SQL_MySQLi
     * https://github.com/gree/cascade/blob/develop/class/Cascade/Driver/SQL/MySQLi.php
     */
    public function getEmulateQuery(\Mysqli $conn)
    {
        $query = $this->query;
        $params = $this->bindValues;

        $pos           = 0;
        $emulateQuery = '';
        $tmp = preg_split('/((?<!\\\)[&?!])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($tmp as $token) {
            switch ($token) {
                case '?':
                    // バインド値の有無確認
                    if (array_key_exists($pos, $params) === FALSE) {
                        $error = "Doesn't match count of bind-value {query, pos} %s %d";
                        $error = sprintf($error, $query, $pos);
                        throw new QueryException($error);
                    }
                    // プレースホルダを値に置き換える
                    if ($params[$pos] === null) {
                        $emulateQuery .= 'NULL';
                    } else {
                        $emulateQuery .= is_string($params[$pos])
                            ? sprintf("'%s'", $conn->real_escape_string($params[$pos]))
                            : $params[$pos];
                    }
                    $pos ++;
                    break;
                default:
                    $emulateQuery .= preg_replace('/\\\([&?!])/', "\\1", $token);
                    break;
            }
        }

        return $emulateQuery;
    }
}
