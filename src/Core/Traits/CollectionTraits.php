<?php
declare(strict_types=1);

namespace NazmulIslam\Utility\Core\Traits;
trait CollectionTraits
{
    /**
     * Converts an array of eloquent collections to array of specific attribute
     * @param $collection
     * @param $column
     * @return array
     */
    protected function collectionToArrayColumn($collection, $column) {
        $array = [];
        foreach($collection as $c) {
            $array[] = $c->{$column};
        }
        return $array;
    }
}