<?php

namespace NazmulIslam\Utility\Components;

/**
 * Class BaseComponent. Base Class that all Components should extend from.
 * @package NazmulIslam\Utility\Core\Components
 */
class BaseComponent
{
    /**
     * This method return the attached, detached and unchaged ids from a many to many pivot
     * table, these can then be used to update the pivot table correctly
     * @param $collections
     * @param $updateArrays
     * @param string $primaryKey
     * @return array
     */
    protected function getPivotTableUpdate($collections, $updateArrays, $primaryKey = 'id') {
        $detachedIds = [];
        $attachableIds = $updateArrays;
        $unchangedIds = [];
            foreach ($collections as $c) {
                $cId = $c->{$primaryKey};
                //if id is not in the array then the role will be detached
                if (!in_array($cId, $updateArrays)) {
                    $detachedIds[] = $cId;
                    $attachableIds = array_filter($attachableIds, function ($value) use ($cId) {
                        if ($value != $cId) {
                            return $value;
                        }
                    });

                }
                //If role is unchanged then id will be removed from attached Ids
                if (in_array($cId, $updateArrays)) {
                    $unchangedIds[] = $cId;
                    $attachableIds = array_filter($attachableIds, function ($value) use ($cId) {
                        if ($value != $cId) {
                            return $value;
                        }
                    });
                }
        }

        return [
            'detached' => $detachedIds,
            'attached' => $attachableIds,
            'unchanged' => $unchangedIds
        ];
    }
}