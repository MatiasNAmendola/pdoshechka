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
        'b' => self::PARAM_BOOL,
        'n' => self::PARAM_NULL,
        'i' => self::PARAM_INT,
        's' => self::PARAM_STR,
        'l' => self::PARAM_LOB,
        'f' => self::PARAM_STR
    );
    private $lastPlaceholders;
    private $lastTypes;
    private $lastParamsCounter;

    /**
     * @param array|string $params If array, dsn compute by class, else just call parent construct
     * @param string|null  $username
     * @param string|null  $password
     * @param array        $options
     * @throws \InvalidArgumentException
     */
    public function __construct($params = array(), $username = null, $password = null, $options = array())
    {
        if (is_array($params)) {
            if (isset($params['driver']) && isset(self::$supportedDrivers[$params['driver']])) {
                $dsn = call_user_func(array($this, self::$supportedDrivers[$params['driver']]), $params);
            } else {
                throw new \InvalidArgumentException('Sql driver is required! Supported: ' . implode(', ', array_keys(self::$supportedDrivers)) . '.');
            }
            parent::__construct($dsn, $username, $password, $options);
        } else {
            parent::__construct($params, $username, $password, $options);
        }
        unset($params);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . '\\PdoStatement'));
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
            $query = preg_replace_callback(self::PARAM_REGEX, array($this, 'parseQuery'), $query);
        }

        $stmt = parent::prepare($query, $driverOptions);

        if ($parse === true) {
            $stmt->setPlaceholders($this->lastPlaceholders);
            $stmt->setTypes($this->lastTypes);
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
                throw new \InvalidArgumentException("Syntax error!$matches[0]");
        }
        $this->lastTypes[$name] = self::$paramTypes[$matches[1]];
        $this->lastPlaceholders[] = $name;

        return '?';
    }

    protected function getMysqlDsn(array $params)
    {
        $dsn = 'mysql:';
        if (array_key_exists('socket', $params)) {
            $dsn .= 'unix_socket=' . $params['socket'] . ';';
        } else {
            if (array_key_exists('host', $params)) {
                $dsn .= 'host=' . $params['host'] . ';';
            }
            if (array_key_exists('port', $params)) {
                $dsn .= 'port=' . $params['port'] . ';';
            }
        }
        if (array_key_exists('dbname', $params)) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }
        if (array_key_exists('charset', $params)) {
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
        if (array_key_exists('host', $params)) {
            $dsn .= 'host=' . $params['host'] . ';';
        }
        if (array_key_exists('port', $params)) {
            $dsn .= 'port=' . $params['port'] . ';';
        }
        if (array_key_exists('dbname', $params)) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }
        if (array_key_exists('user', $params)) {
            $dsn .= 'user=' . $params['user'] . ';';
        }
        if (array_key_exists('password', $params)) {
            $dsn .= 'password=' . $params['password'] . ';';
        }

        return $dsn;
    }
}