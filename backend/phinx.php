<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => $_ENV['RDS_DB_HOST'],
            'name' => $_ENV['RDS_DB_NAME'],
            'user' => $_ENV['RDS_DB_USER'],
            'pass' => $_ENV['RDS_DB_PASS'],
            'port' => '22130',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['RDS_DB_HOST'],
            'name' => $_ENV['RDS_DB_NAME'],
            'user' => $_ENV['RDS_DB_USER'],
            'pass' => $_ENV['RDS_DB_PASS'],
            // 'host' => $_ENV['PROD_DB_HOST'],
            // 'name' => $_ENV['PROD_DB_NAME'],
            // 'user' => $_ENV['PROD_DB_USER'],
            // 'pass' => $_ENV['PROD_DB_PASS'],
            // 'port' => '22130',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => $_ENV['RDS_DB_HOST'],
            'name' => $_ENV['RDS_DB_NAME'],
            'user' => $_ENV['RDS_DB_USER'],
            'pass' => $_ENV['RDS_DB_PASS'],
            'port' => '22130',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
