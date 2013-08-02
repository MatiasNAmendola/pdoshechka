<?php

namespace Fountain\Dbal;

class Pdo extends \PDO
{
    const PARAM_REGEX = '/([bnislf])\:(\w+)*/';
    protected static $supportedDrivers = array(
        'mysql'    => 'getMysqlDsn',
        'sqlite'   => 'getSqliteDsn',
        'postgres' => 'getPostgresSql'
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
        $this->setAttribute(\PDO::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . '\\PdoStatement'));
    }

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

    protected function getPostgresSql(array $params)
    {

    }
}