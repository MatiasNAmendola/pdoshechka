<?php

namespace Fountain\Pdoshechka\Tests;

use Fountain\Pdoshechka\Pdo;
use PDO as Param;

class PdoStatementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pdo
     */
    private $pdo;

    /**
     * @dataProvider getQueries
     */
    public function testExecute($query, $type)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array('param' => 1));
        $lastExecuted = $stmt->getLastExecuted();
        $this->assertSame($type, $lastExecuted['values'][1]['type']);
    }

    /**
     * @dataProvider getQueries
     */
    public function testExecuteThrowExceptionIfMissParams($query)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->pdo->prepare($query)->execute();
    }

    public function testExecuteWithManyParams()
    {
        $stmt = $this->pdo->prepare('i:id foo s:string');
        $stmt->execute(array('id' => 1, 'string' => 2));

        $this->assertSame('? foo ?', $stmt->queryString);

        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(1, $lastExecuted['values'][1]['value']);
        $this->assertSame(2, $lastExecuted['values'][2]['value']);

        $stmt->execute(array('string' => 1, 'id' => 2));
        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(2, $lastExecuted['values'][1]['value']);
        $this->assertSame(1, $lastExecuted['values'][2]['value']);
    }

    public function testExecuteWithUnnamedParams()
    {
        $stmt = $this->pdo->prepare('i: foo s: bar');
        $stmt->execute(array(1, 2));

        $this->assertSame('? foo ? bar', $stmt->queryString);

        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(1, $lastExecuted['values'][1]['value']);
        $this->assertSame(2, $lastExecuted['values'][2]['value']);
    }

    public function testExecuteWithMixedParams()
    {
        $stmt = $this->pdo->prepare('bar i:id foo s:');
        $stmt->execute(array('id' => 1, 2));

        $this->assertSame('bar ? foo ?', $stmt->queryString);

        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(1, $lastExecuted['values'][1]['value']);
        $this->assertSame(2, $lastExecuted['values'][2]['value']);
    }

    public function testExecuteWithNullParam()
    {
        $stmt = $this->pdo->prepare('i:id');
        $stmt->execute(array('id' => null));

        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(null, $lastExecuted['values'][1]['value']);
        $this->assertSame(Param::PARAM_NULL, $lastExecuted['values'][1]['type']);
    }

    public function testExecuteWithManyArgs()
    {
        $stmt = $this->pdo->prepare('i:id s:str');
        $stmt->execute(array('id' => 1), array('str' => 2));

        $lastExecuted = $stmt->getLastExecuted();

        $this->assertSame(1, $lastExecuted['values'][1]['value']);
        $this->assertSame(2, $lastExecuted['values'][2]['value']);
    }

    public function testFetchCallback()
    {
        $data = array(
            array(
                'id'   => 1,
                'data' => 'foo'
            ),
            array(
                'id'   => 2,
                'data' => 'bar'
            )
        );

        $stmt = $this->pdo->prepare('');
        $stmt->setFetchData($data);
        $results = array();
        $stmt->fetchCallback(function ($row) {
            return $row;
        }, null, $results);

        $this->assertSame($data, $results);
    }

    public function getQueries()
    {
        return array(
            array('i:param', Param::PARAM_INT),
            array('b:param', Param::PARAM_BOOL),
            array('n:param', Param::PARAM_NULL),
            array('s:param', Param::PARAM_STR),
            array('l:param', Param::PARAM_LOB),
            array('f:param', Param::PARAM_STR)
        );
    }

    protected function setUp()
    {
        $this->pdo = new Pdo('');
    }
}