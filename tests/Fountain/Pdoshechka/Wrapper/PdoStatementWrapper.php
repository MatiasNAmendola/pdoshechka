<?php

namespace Fountain\Pdoshechka\Wrapper;

class PdoStatementWrapper
{
    public $queryString;
    private $_driverOptions;
    private $_query;
    private $_executed;
    private $_values;
    private $_fetchData = array();

    public function __construct($query, $options)
    {
        $this->_query = $this->queryString = $query;
        $this->_driverOptions = $options;
    }

    public function execute($params = array())
    {
        $this->_executed[] = array(
            'params' => $params,
            'values' => $this->_values
        );
    }

    public function getLastExecuted()
    {
        $last = end($this->_executed);

        return $last;
    }

    public function bindValue($param, $value, $type = \PDO::PARAM_STR)
    {
        $this->_values[$param] = array(
            'value' => $value,
            'type'  => $type
        );
    }

    public function setFetchData(array $data)
    {
        $this->_fetchData = $data;
    }

    public function fetch()
    {
        $data = current($this->_fetchData);
        next($this->_fetchData);

        return $data;
    }
}