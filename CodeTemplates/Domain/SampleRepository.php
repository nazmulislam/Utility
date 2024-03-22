<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Domain\[DomainFolder];

use NazmulIslam\Utility\Core\Interfaces\RepositoryInterface;
use NazmulIslam\Utility\Core\Entity;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\[ModelName];
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]CacheKeys;
use NazmulIslam\Utility\Core\Traits\PaginateTrait;
use NazmulIslam\Utility\Utility;

class [ClassName]Repository extends Entity implements RepositoryInterface
{

    use PaginateTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function get[ModelName]List(array $input, array $fields = ['*']): array
    {
        $this->setDefaultPaginationValues(input: $input);
        
        if((bool)$this->cacheIsEnabled === false)
        {
            return $this->getData(input: $input, fields: $fields);
        }

        $key = [ClassName]CacheKeys::GET_[CONSTANTS]_ALL_TABLE['key'];

        $cacheKey = $key . '_tenant_' . $GLOBALS['TENANT'] ?? ''.'_'. $this->perPage . '_' . $this->page. (isset($this->searchFilter)?('_filter_'.$this->searchFilter):'');
        $cachedString = $this->cache->getItem($cacheKey);
        $cachedData = $cachedString->get();
        if (is_null($cachedData)) {

            $data = $this->getData(input: $input, fields: $fields);
            if (count($data['data']) == 0) {
                return $data;
            }
            $cachedString->set($data)
                 ->setTags([[ClassName]CacheKeys::[CONSTANTS]_TAG])
                ->expiresAfter([ClassName]CacheKeys::GET_[CONSTANTS]_ALL_TABLE['expiresAfter']); //in seconds, also accepts Datetime
            $this->cache->save($cachedString); // Save the cache item just like you do with doctrine and entities

            return $cachedString->get();
        } else {

            return $cachedData;
        }
    }

    public function getData(array $input, array $fields): array
    {

        $query = [ModelName]::select($fields);

        if (isset($this->searchFilter) && !empty($this->searchFilter)) {
            $query->where('[title_field]','like' ,'%'.$this->searchFilter.'%');
        }

        if (isset($this->sortingColumns) && is_array($this->sortingColumns) && count($this->sortingColumns) > 1 && (!empty($this->sortingColumns[0]) && !empty($this->sortingColumns[1]))) {
            $query->orderBy($this->sortingColumns[0], $this->sortingColumns[1]);
        }

        $data = $query->paginate($this->perPage, NULL, NULL, $this->page);

        return isset($data) ? $data->toArray() : [];
    }
    
    public function get[ModelName]ById(int $[parameterId], array $fields = ['*']): array
    {
            $row = [ModelName]::select($fields)->where('[primary_key_id]',$[parameterId])->first();
            
            return isset($row) ? $row->toArray() : [];
    }
    
    
    public function create(array $input): array
    {
            $row = [ModelName]::create([
               '[title_field]' => $input['[title_field]']
            ]);
            
            return $row->toArray();
    }
    
    public function delete(int $[parameterId])
    {
        [ModelName]::where('[primary_key_id]', $[parameterId])->first()->delete();
    }

    public function update(array|null $input, int $[parameterId]): array
    {
            $row = [ModelName]::where('[primary_key_id]', $[parameterId])->first();
            if (isset($row) || !empty($row)) {

               foreach ([ModelName]::SCHEMA as $field => $value) 
               {
                    if (isset($input[$field])) 
                    {
                        $row->$field = $input[$field];
                    }
                }
                $row->save();
               return isset($row) ? $row->toArray() :[];
                
            }
        return [];
    }
    
}
