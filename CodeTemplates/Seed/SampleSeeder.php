<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;
use NazmulIslam\Utility\Utility\Utility;
use Faker\Factory;

class [ClassName]Seeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run():void
    {
        if($this->hasTable('[table_name]'))
        {
            $table = $this->table('[table_name]');
            $table->truncate();

            $faker = Factory::create();
            $data = [];

            $table->insert($data)->saveData();
        }
        
    }
}
