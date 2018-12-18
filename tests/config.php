<?php

use yii\helpers\ArrayHelper;
use yii\db\Connection;
use yii\caching;

$localConfig = __DIR__ . DIRECTORY_SEPARATOR . 'config-local.php';

$host = getenv('DB_HOST');
$name = getenv("DB_NAME");
$port = getenv("DB_PORT");
$dsn = "pgsql:host={$host};dbname={$name};port={$port}";

$config = [
    'id' => 'bobra-documents',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => Connection::class,
            'dsn' => $dsn,
            'username' => getenv("DB_USERNAME"),
            'password' => getenv("DB_PASSWORD") ?: null,
        ],
        'cache' => [
            'class' => caching\ArrayCache::class,
        ],
    ],
];

return ArrayHelper::merge(
    $config,
    is_file($localConfig) ? require $localConfig : []
);
