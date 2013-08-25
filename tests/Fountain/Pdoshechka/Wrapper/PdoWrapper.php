<?php

namespace Fountain\Pdoshechka\Wrapper;

class PdoWrapper
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $attributes;

    public function __construct($dsn, $username = null, $password = null, array $attributes = array())
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->attributes = $attributes;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function prepare($query, $driverOptions = array())
    {
        $stmt = new $this->attributes[\PDO::ATTR_STATEMENT_CLASS][0]($query, $driverOptions);

        return $stmt;
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getAttribute($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
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