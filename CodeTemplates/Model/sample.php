<?php
declare(strict_types=1);
namespace App\Models\App;

use Illuminate\Events\Dispatcher;
use App\Domain\[DomainFolder]\[ClassName]Observer;
use App\Domain\[DomainFolder]\[ClassName]Repository;

class [ClassName] extends Model
{
    protected $table = '[table_name]';
    protected $primaryKey = '[primary_key_id]';
    protected $connection = 'app';
    protected $guarded = [];

    const SCHEMA = [
        '[primary_key_id]' => [
            'type' => 'integer',
            'length' => 11,
            'sanitizers' => [
                'FILTER_SANITIZE_NUMBER_INT' => [],
            ],
        ],
        '[title_field]' => [
            'type' => 'string',
            'length' => 255,
            'sanitizers' => [
                'FILTER_SANITIZE_STRING' => [],
            ],
        ]
    ];
    
       public static function boot() {
        parent::boot();
         // create the event dispatcher as there is no laravel app to do this.
        static::setEventDispatcher(new Dispatcher());
        // register an observer to deal with the events occuring on the model
         static::observe(new [ClassName]Observer(new [ClassName]Repository()));

         //alternatively you can deal with events without an observer class
         /*static::saving(function($model) {

         });*/
         //these are the events on a model
        //retrieved, creating, created, updating, updated, saving, saved, deleting,  deleted, restoring, restored
    }
}


