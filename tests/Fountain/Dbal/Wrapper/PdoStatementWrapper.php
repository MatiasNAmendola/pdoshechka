<?php

namespace Fountain\Dbal\Wrapper;

class PdoStatementWrapper
{
    public $queryString;
    private $_driverOptions;
    private $_query;
    private $_executed;
    private $_values;
    private $_fetchData = array();

    public function __construct($query, array $options)
    {
        $this->_query = $this->queryString = $query;
        $this->_driverOptions = $options;
    }

    public function execute(array $params = array())
    {
        $this->_executed[] = array(
            'params' => $params,
            'query'  => $this->_query
        );
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
}