<?php

namespace Fountain\Dbal;

use Fountain\Dbal\Wrapper\PdoWrapper;

class Pdo extends PdoWrapper
{
    const PARAM_REGEX = '/([bnislf])\:(\w+)*/';
    protected static $supportedDrivers = array(
        'mysql'    => 'getMysqlDsn',
        'sqlite'   => 'getSqliteDsn',
        'postgres' => 'getPostgresDsn'
    );
    protected static $paramTypes = array(
        'b' => \PDO::PARAM_BOOL,
        'n' => \PDO::PARAM_NULL,
        'i' => \PDO::PARAM_INT,
        's' => \PDO::PARAM_STR,
        'l' => \PDO::PARAM_LOB,
        'f' => \PDO::PARAM_STR
    );
    private $lastPlaceholders;
    private $lastTypes;
    private $lastParamsCounter;

    /**
     * @param array|string $params If array, dsn compute by class, else just call parent construct
     * @param string|null  $username
     * @param string|null  $password
     * @param array        $attributes
     * @throws \InvalidArgumentException
     */
    public function __construct($params, $username = null, $password = null, $attributes = array())
    {
        if (is_array($params)) {
            if (isset($params['driver']) && isset(static::$supportedDrivers[$params['driver']])) {
                $dsn = call_user_func(array($this, static::$supportedDrivers[$params['driver']]), $params);
            } else {
                throw new \InvalidArgumentException('Sql driver is required! Supported: ' . implode(', ', array_keys(static::$supportedDrivers)) . '.');
            }
            parent::__construct($dsn, $username, $password, $attributes);
        } else {
            parent::__construct($params, $username, $password, $attributes);
        }
        unset($params);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . '\\PdoStatement'));
    }

    /**
     * @param string $query
     * @param array  $driverOptions
     * @param bool   $parse If false, method just call parent, and not parse any special syntax
     * @return PdoStatement
     */
    public function prepare($query, $driverOptions = array(), $parse = true)
    {
        if ($parse === true) {
            $this->lastPlaceholders = $this->lastTypes = array();
            $this->lastParamsCounter = 0;
            $query = preg_replace_callback(static::PARAM_REGEX, array($this, 'parseQuery'), $query);
        }

        $stmt = parent::prepare($query, $driverOptions);

        if ($parse === true) {
            $stmt->setFountainData($this->lastTypes, $this->lastPlaceholders);
        }

        return $stmt;
    }

    /**
     * @see Pdo::prepare
     */
    public function __invoke($query, $driverOptions = array(), $parse = true)
    {
        return $this->prepare($query, $driverOptions, $parse);
    }

    protected function parseQuery($matches)
    {
        switch (count($matches)) {
            case 2: // unnamed
                $name = $this->lastParamsCounter++;
                break;
            case 3: // named
                $name = & $matches[2];
                break;
            default:
                throw new \InvalidArgumentException("Syntax error! [$matches[0]]");
        }
        $this->lastTypes[$name] = self::$paramTypes[$matches[1]];
        $this->lastPlaceholders[] = $name;

        return '?';
    }

    protected function getMysqlDsn(array $params)
    {
        $dsn = 'mysql:';
        if (isset($params['socket'])) {
            $dsn .= 'unix_socket=' . $params['socket'] . ';';
        } else {
            if (isset($params['host'])) {
                $dsn .= 'host=' . $params['host'] . ';';
            }
            if (isset($params['port'])) {
                $dsn .= 'port=' . $params['port'] . ';';
            }
        }
        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }
        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        return $dsn;
    }

    protected function getSqliteDsn(array $params)
    {
        $dsn = 'sqlite:';
        if (array_key_exists('path', $params)) {
            return $dsn . $params['path'];
        } elseif (isset($params['memory']) && $params['memory']) {
            return $dsn . ':memory:';
        }
    }

    protected function getPostgresDsn(array $params)
    {
        $dsn = 'pgsql:';
        if (isset($params['host'])) {
            $dsn .= 'host=' . $params['host'] . ';';
        }
        if (isset($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }
        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }
        if (isset($params['user'])) {
            $dsn .= 'user=' . $params['user'] . ';';
        }
        if (isset($params['user'])) {
            $dsn .= 'password=' . $params['password'] . ';';
        }

        return $dsn;
    }
}