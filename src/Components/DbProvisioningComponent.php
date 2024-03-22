<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Components;
use Illuminate\Database\Capsule\Manager as DB;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\Tenant;
/**
 * Description of DbProvisioningComponent
 *
 * @author apple
 */
class DbProvisioningComponent {
    //put your code here
    public $tenant;
    public $defaultSchema = __DIR__.'/../../../../schema/schema.sql';
    public $vendorEduModel;
   
            
    function __construct($tenant) {
        $this->tenant = $tenant;
       
    } 
    
    function createSchema()
    {
        // We will use the `statement` method from the connection class so that
        // we have access to parameter binding.
       DB::connection('master')->statement('CREATE DATABASE '.$this->tenant->tenant_db_name . ' CHARACTER SET utf8 COLLATE utf8_general_ci');
           $this->importTables();

    }
    /**
     * 
     * @return string
     */
    function importTables():void
    {
     
            $templine = '';
            if(!file_exists($this->defaultSchema))
            {
                echo $this->defaultSchema;
                throw new \Exception('schema file doesnt exist');
            }
            
            // Read in entire file
            $lines = file($this->defaultSchema);
            
            foreach ($lines as $line) {
            // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;

            // Add this line to the current segment
                $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
                if (substr(trim($line), -1, 1) == ';') {
                    // Perform the query
    //                $con->query($templine); 
                    //DB::connection($this->tenant->db_name)->select($templine);
                    DB::connection('app')->select($templine);
                    

                    // Reset temp variable to empty
                    $templine = '';
                }
            }

    }
    
 
            
    function migrateDatabase()
    {
        //Illuminate\Support\Facades\Artisan::call('migrate', array('database' => $databaseConnection, 'path' => 'app/database/tenants'));
    }
}
