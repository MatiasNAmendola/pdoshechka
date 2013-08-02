<?php

namespace Fountain\Dbal;

class PdoStatement extends \PDOStatement
{
    protected $types;
    protected $placeholders;

    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * @param array|mixed $parameters If args more then one, every argument becomes parameter
     * @param mixed       $parameters ...
     * @return $this
     * @throws \InvalidArgumentException If you miss some params
     */
    public function execute($parameters = array())
    {
        if (func_num_args() !== 1) {
            $parameters = func_get_args();
        }
        if ($this->types === null) {
            parent::execute($parameters);
        } else {
            $parameters = array_intersect_key($parameters, $this->types);
            foreach ($this->placeholders as $i => $name) {
                if (!array_key_exists($name, $parameters)) {
                    throw new \InvalidArgumentException("Missing parameter '{$name}.'");
                }
                $value = & $parameters[$name];
                if ($value === null) {
                    $this->bindValue($i + 1, null, \PDO::PARAM_NULL);
                } else {
                    $this->bindValue($i + 1, $value, $this->types[$name]);
                }
            }
        }

        return $this;
    }

    /**
     * @example
     * $query->fetchCallback(function ($row) {
     *     return new User::fromArray($row);
     * }, null, $results);
     * @param callable $callback
     * @param int|null $mode    Fetch mode, null replaced by \PDO::FETCH_ASSOC
     * @param array    $results Array where method save all callback results
     * @return $this
     */
    public function fetchCallback(\Closure $callback, $mode = Pdo::FETCH_ASSOC, array &$results = array())
    {
        if ($mode === null) {
            $mode = Pdo::FETCH_ASSOC;
        }
        while (false !== $row = $this->fetch($mode)) {
            $results[] = $callback($row);
        }

        return $this;
    }

    /**
     * @see PdoStatement::execute
     */
    public function __invoke()
    {
        return $this->execute(func_get_args());
    }
}