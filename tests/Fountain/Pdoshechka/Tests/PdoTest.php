<?php

namespace Fountain\Pdoshechka\Tests;

use Fountain\Pdoshechka\Pdo;

class PdoTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleConstruct()
    {
        $pdo = new Pdo(array(
            'driver' => 'sqlite',
            'memory' => true
        ));
        $this->assertSame('sqlite::memory:', $pdo->getDsn());
    }

    public function testConstructFromReadyDsnString()
    {
        $pdo = new Pdo('sqlite::memory:');

        $this->assertSame('sqlite::memory:', $pdo->getDsn());
    }

    public function testPrepare()
    {
        $pdo = new Pdo('');
        $stmt = $pdo->prepare('SELECT * FROM test');

        $this->assertInstanceOf('Fountain\\Pdoshechka\\PdoStatement', $stmt);
        $this->assertSame('SELECT * FROM test', $stmt->queryString);
    }

    public function testInvoke()
    {
        $pdo = new Pdo('');

        $this->assertInstanceOf('Fountain\\Pdoshechka\\PdoStatement', $pdo(''));
    }
}