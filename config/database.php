<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| MySQL SSL Constants Compatibility
|--------------------------------------------------------------------------
|
| PHP 8.5 mulai mengganti beberapa konstanta PDO MySQL lama.
| Kode ini dibuat supaya tetap jalan di PHP lama dan PHP 8.5 Vercel.
|
*/

$mysqlSslCa = class_exists(\Pdo\Mysql::class) && defined(\Pdo\Mysql::class . '::ATTR_SSL_CA')
    ? constant(\Pdo\Mysql::class . '::ATTR_SSL_CA')
    : constant('PDO::MYSQL_ATTR_SSL_CA');

$mysqlSslVerifyServerCert = class_exists(\Pdo\Mysql::class) && defined(\Pdo\Mysql::class . '::ATTR_SSL_VERIFY_SERVER_CERT')
    ? constant(\Pdo\Mysql::class . '::ATTR_SSL_VERIFY_SERVER_CERT')
    : constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT');

/*
|--------------------------------------------------------------------------
| MySQL SSL CA Path
|--------------------------------------------------------------------------
|
| Untuk Aiven, simpan CA certificate di:
| config/certs/aiven-ca.pem
|
| Lalu di Vercel Environment Variables isi:
| MYSQL_ATTR_SSL_CA=config/certs/aiven-ca.pem
|
*/

$defaultMysqlSslCaPath = base_path('config/certs/aiven-ca.pem');

$mysqlSslCaPath = env('MYSQL_ATTR_SSL_CA')
    ? base_path(env('MYSQL_ATTR_SSL_CA'))
    : $defaultMysqlSslCaPath;

$mysqlSslCaPath = file_exists($mysqlSslCaPath)
    ? $mysqlSslCaPath
    : null;

$mysqlSslVerifyServerCertValue = filter_var(
    env('MYSQL_ATTR_SSL_VERIFY_SERVER_CERT', false),
    FILTER_VALIDATE_BOOLEAN,
    FILTER_NULL_ON_FAILURE
);

$mysqlSslVerifyServerCertValue = $mysqlSslVerifyServerCertValue ?? false;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                $mysqlSslCa => $mysqlSslCaPath,
                $mysqlSslVerifyServerCert => $mysqlSslVerifyServerCertValue,
            ], fn ($value) => ! is_null($value)) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                $mysqlSslCa => $mysqlSslCaPath,
                $mysqlSslVerifyServerCert => $mysqlSslVerifyServerCertValue,
            ], fn ($value) => ! is_null($value)) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];