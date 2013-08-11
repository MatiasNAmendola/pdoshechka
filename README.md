# Fountain DBAL
[![Build Status](https://travis-ci.org/hell0w0rd/dbal.png?branch=master)](https://travis-ci.org/hell0w0rd/dbal)

Simle, tested wrapper for PDO.

## Usage

```php
<?php

use Fountain\Dbal\Pdo;

$db = new Pdo([
    'username' => 'root',
    'password' => 'secret',
    'dbname'   => 'test_db',
    'driver'   => 'mysql'
]);

$result = $db->prepare('SELECT * FROM table WHERE id < i:id AND price <> f: OR name = s:name')
             ->execute(2.3, ['id' => $id], ['name' => $name])
             ->fetchAll();
```
## Install

Add to your `composer.json`
```json
{
    "require": {
        "fountain/dbal": "1.0"
    }
}
```
