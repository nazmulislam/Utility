<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\ArrayUtility;

class ArrayUtility
{

    public static array $sort_by_param;
    /**
     * @Description: It takes Eloquent model object and convert it to array.
     * @param \App\Model\Eloquent $eloquent
     * @return array
     */
    public static function getArray($eloquent): array
    {
        return (isset($eloquent) && !empty($eloquent)) ? $eloquent->toArray() : [];
    }

    static function castObjectToArray($obj = [])
    {
        if (!is_null($obj) && !empty($obj))
        {
            return json_decode(json_encode($obj), true);
        } else
        {
            return $obj;
        }
    }

    static function castObjectToSingletonArray($obj = [])
    {
        $data = json_decode(json_encode($obj), true);
        if (isset($data[0]) && !empty($data[0]))
        {
            $data = isset($data[0]) ? $data[0] : [];
        }
        return $data;
    }

    /**
     * 
     * @param type $array
     * @param type $filter_keys
     * @param type $filter_values
     * @return Description : $filter_keys & $filter_values should have same number of objects.
     */
    static public function filterArrayByKeysAndValues($array, $filter_keys, $filter_values)
    {
        $filteredArray = [];
        if (count($filter_keys) == count($filter_values))
        {
            for ($x = 0; $x < count($filter_keys); $x++)
            {
                if (isset($filter_keys[$x]) && isset($filter_values[$x]))
                {
                    self::$sort_by_param = [
                        'key' => isset($filter_keys[$x]) ? $filter_keys[$x] : '',
                        'value' => isset($filter_values[$x]) ? $filter_values[$x] : ''
                    ];
                }
                $filteredArray = array_merge(array_values(array_filter($array, "\NazmulIslam\Utility\ArrayUtility\ArrayUtility::filterByValue")), $filteredArray);
            }
        }
        return $filteredArray;
    }

    static public function filterArrayByValue($array, $filter_key, $filter_value)
    {
        self::$sort_by_param = [
            'key' => isset($filter_key) ? $filter_key : '',
            'value' => isset($filter_value) ? $filter_value : ''
        ];
        return array_values(array_filter($array, "\NazmulIslam\Utility\ArrayUtility\ArrayUtility::filterByValue"));
    }

    static private function filterByValue($arrayValue)
    {
        if (isset($arrayValue[self::$sort_by_param['key']]) && $arrayValue[self::$sort_by_param['key']] == self::$sort_by_param['value'])
        {
            return true;
        }
        return false;
    }

    static public function groupBy($key, $data, $showKeyAsIndex = TRUE)
    {
        $result = [];
        foreach ($data as $val)
        {
            if (array_key_exists($key, $val))
            {
                $result[$val[$key]][] = $val;
            } else
            {
                $result[""][] = $val;
            }
        }
        if ($showKeyAsIndex == TRUE)
        {
            return $result;
        } else
        {
            return self::replaceKeyWithIndex($result);
        }
    }

    static public function replaceKeyWithIndex($result): array
    {
        $formatResponse = [];
        foreach ($result as $section => $sectionData)
        {
            $formatResponse[] = $sectionData;
        }
        return $formatResponse;
    }

    /**
     * Use to filter an array to get rows which contain a specfic key and value,
     * @param array $data
     * @param string $key
     * @param string $value
     * @return array
     */
    static public function getArrayFilteredByKeyAndValue(array $data, string $key, string $value): array
    {
        return array_filter($data, function($row) use($key, $value)
        {

            if ($row[$key] == $value)
            {
                return true;
            } else
            {
                return false;
            }
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * inserts into a specific array index and returns the array reference
     * @param array      $array
     * @param int|string $position
     * @param mixed      $insert
     */
    static function arrayInsert(array &$array, int $position, $insert)
    {
        if (is_int($position))
        {
            array_splice($array, $position, 0, $insert);
        } else
        {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                    array_slice($array, 0, $pos),
                    $insert,
                    array_slice($array, $pos)
            );
        }
    }

    /**
     * inserts into a specific array index and returns the array
     * @param array      $array
     * @param int|string $position
     * @param mixed      $insert
     */
    static function arrayInsertNoReference(array $array, int $position, $insert): array
    {
        if (is_int($position))
        {
            array_splice($array, $position, 0, $insert);
            return $array;
        } else
        {
            $pos = array_search($position, array_keys($array));
            return $newArray = array_merge(
                    array_slice($array, 0, $pos),
                    $insert,
                    array_slice($array, $pos)
            );
        }
    }

    static function arrayFlatten(array $array): array
    {
        if (!is_array($array))
        {
            return FALSE;
        }
        $result = [];
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $result = \array_merge($result, self::arrayFlatten($value));
            } else
            {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Takes a multi dimensional array and returns a single indexed array by specifying the $key to remove
     * @param array $array
     * @param string $key
     * @return array
     */
    static function singleArray(array $array,string $key):array
    {
        return array_column($array, $key);
    }

}
