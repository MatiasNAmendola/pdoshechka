<?php

namespace Fountain\Dbal\Wrapper;

class PdoWrapper
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $options = array(
        \PDO::ATTR_STATEMENT_CLASS => 'Fountain\\Dbal\\Wrapper\\PdoStatementWrapper'
    );

    public function __construct($dsn, $username = null, $password = null, array $options = array())
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;
    }

    public function setAttribute($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function prepare($query, array $driverOptions = array())
    {
        $stmt = new $this->options[\PDO::ATTR_STATEMENT_CLASS];
        $stmt->queryString = $query;
        $stmt->setDriverOptions($driverOptions);

        return $stmt;
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getAttribute($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getUsername()
    {
        return $this->username;
    }

}