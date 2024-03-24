<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Traits;

use Illuminate\Database\Capsule\Manager as DB;

trait CommonTraits
{
    public function setupTenantDB()
    {
        $db = new DB;

        //=======================================



        $db->addConnection([
            'driver' => 'mysql',
            'host' => DB_HOST_NAME,
            'database' => DB_NAME,
            'username' => DB_USERNAME,
            'password' => DB_PASSWORD,
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'options' => [
                // Turn on persistent connections
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
                // Enable exceptions
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                // Emulate prepared statements
                \PDO::ATTR_EMULATE_PREPARES => true,
                // Set default fetch mode to array
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                // Set character set
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ],
        ], 'app');

        /**
         * The setAsglobal and bootEloquent is required here do not move as the Utility::setSaasDBHostname, will not be able to find connection
         */
        $db->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $db->bootEloquent();
    }
}
