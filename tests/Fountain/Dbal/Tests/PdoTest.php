<?php

namespace Fountain\Dbal\Tests;

use Fountain\Dbal\Pdo;

class PdoTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructFromReadyDsnString()
    {
        $pdo = new Pdo('sqlite::memory:');

        $this->assertSame('sqlite::memory:', $pdo->getDsn());
    }
}