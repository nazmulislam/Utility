<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

use Illuminate\Database\Capsule\Manager as DB;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use NazmulIslam\Utility\Core\Entity;

trait TestSetupTrait
{
    public Client $endPointClient;
    public Client $endPointClientWithoutHeader;
    protected Client $login;
    protected array $loginResponse;

    public function clientAuthorization() {

        $this->login = new Client([
            'base_uri' => $_ENV['API_UNIT_TEST_URI'],
            'verify' => false,
            'headers' => [
                
                // 'Content-Type' => 'application/json'
            ],
        ]);

        $response = $this->login->request('POST', '/authenticate', [
            RequestOptions::JSON => [
                'username' => $_ENV['API_UNIT_TEST_USERNAME'], 
                'password' => $_ENV['API_UNIT_TEST_PASSWORD']
            ]
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }

    public function setUpClient() {
        
        $this->loginResponse = $this->clientAuthorization();

        $this->endPointClient = new Client([
            'base_uri' => $_ENV['API_UNIT_TEST_URI'],
            'headers' => [
                'authorization' => 'Bearer '.$this->loginResponse['accessToken'],
                'refresh-token' => $this->loginResponse['refreshToken'],
                
                // 'Content-Type' => 'application/json'
            ],
            'verify' => false,
            // need to add this, as guzzle was handling the 401 errors
            'http_errors' => false
        ]);

        return $this->endPointClient;
    }

    public function setUpClientWithoutHeader() {
        
        $this->loginResponse = $this->clientAuthorization();
        
        $this->endPointClientWithoutHeader = new Client([
            'base_uri' => $_ENV['API_UNIT_TEST_URI'],
            'headers' => [
                
                // 'Content-Type' => 'application/json'
            ],
            'verify' => false,
            // need to add this, as guzzle was handling the 401 errors
            'http_errors' => false
        ]);

        return $this->endPointClientWithoutHeader;
    }

    public function dbSetup(array $tagList)
    {
        /**
         * Should be set after after middleware, to prevent object cacche folders being created
         */
        CacheManager::setDefaultConfig(new ConfigurationOption(['path' => __DIR__ . '/../../../cache']));
        
        $objFilesCache = CacheManager::getInstance(CACHE_DRIVER);
        global $objFilesCache;

        // $entityProphey = $this->prophesize(Entity::class);
        $entityProphey = $this->prophesize(Entity::class);
        $entityProphey->cache = $objFilesCache;
        $entityProphey->clearCacheByTag($tagList);

        $saasDb = new DB;

        //=======================================
        $saasDb->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['TEST_DB_HOST_NAME'],
            'database'  => $_ENV['TEST_DB_NAME'],
            'username'  => $_ENV['TEST_DB_USERNAME'],
            'password'  => $_ENV['TEST_DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict' => false,// disable error SELECT list is not in GROUP BY clause and contains nonaggregated column
            'options'   => [
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
                    ]
        ],'app');

        $saasDb->setAsGlobal();
        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $saasDb->bootEloquent();
    }
}
