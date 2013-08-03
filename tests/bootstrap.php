<?php

if (!is_readable(__DIR__ . '/../vendor/autoload.php')) {
    throw new Exception('Run "composer install" at first!');
}
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Fountain\\Dbal', __DIR__, true);