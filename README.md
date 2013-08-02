# Database Abstract Layer

## Usage

```php
<?php

use Dbal\Pdo;

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
        "hell0w0rd/dbal": "dev-master"
    }
}
