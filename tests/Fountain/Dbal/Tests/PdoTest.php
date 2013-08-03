<?php

namespace Fountain\Dbal\Tests;

use Fountain\Dbal\Pdo;

class PdoTest extends \PHPUnit_Framework_TestCase
{
    protected static $instanse;

    public static function setUpBeforeClass()
    {
        $pdo = new Pdo(array(
            'driver' => 'sqlite',
            'memory' => true
        ));
        $pdo->exec('CREATE TABLE IF NOT EXISTS test (id int(11) NOT NULL);');
        self::$instanse = $pdo;
    }

    public function testConstructErrorDriverNotExist()
    {
        $this->setExpectedException('InvalidArgumentException');
        $pdo = new Pdo;
    }

    public function testPrepare()
    {
        $pdo = self::$instanse;
        $stmt = $pdo->prepare('SELECT * FROM test where id = i:id');

        $this->assertInstanceOf('Fountain\\Dbal\\PdoStatement', $stmt);
        $this->assertSame('SELECT * FROM test where id = ?', $stmt->queryString);

        $stmt = $pdo->prepare('SELECT * FROM test where id = :id', array(), false);

        $this->assertSame('SELECT * FROM test where id = :id', $stmt->queryString);
    }
}