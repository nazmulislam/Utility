<?php

declare(strict_types=1);

namespace App\Domain\[DomainFolder];

use App\Core\Service;
use App\Domain\[DomainFolder]\[ClassName]Repository;
use App\Domain\[DomainFolder]\[ClassName]LogValues;

class [ClassName]Service extends Service
{
    public function create(array $input,[ClassName]Repository $[instanceName]Repository): array
    {
        $return =  $[instanceName]Repository->create(input: $input);
        if(isset($return['[primary_key_id]']))
        {
            $this->createActivityLog(
                
                modelId:intval($return['[primary_key_id]']), 
                userId: $GLOBALS['user']->user_id ?? 0, 
                message:[ClassName]LogValues::MESSAGE_CREATE, 
                action: [ClassName]LogValues::ACTION_CREATE,
                module:'SAMPLE',
                placeholderValues: ['TITLE'=>$return['[title_field]'],'CREATED_BY'=> $GLOBALS['user_fullname'] ?? 'Unknown User','CREATED_AT'=>$return['created_at']], 
                addtionalData:[]);
        }
        
        return ["status" => true, "record" => $return, "message" => "Successfully Created"];

    }
    
    public function delete(int $[parameterId],[ClassName]Repository $[instanceName]Repository): array
    {
            $[instanceName]Repository->delete([parameterId]: $[parameterId]);
            return ['message' => 'Successfully Deleted'];
    }

    public function update(array|null $input, int $[parameterId], [ClassName]Repository $[instanceName]Repository): array
    {
           $record = $[instanceName]Repository->update(input:$input,[parameterId]:$[parameterId]);
           return ['message' => 'Successfully Updated', 'record' => $record];   
    }

    public function getListPaginated(array $input, [ClassName]Repository $[instanceName]Repository): array
    {
        $fields = ['*'];
        $data =  $[instanceName]Repository->get[ModelName]List(input: $input, fields: $fields);
        return isset($data) ? $data : [];
    }
    
    public function get[ModelName]ById(int $[parameterId], [ClassName]Repository $[instanceName]Repository): array
    {
        $fields = ['*'];
        $data =  $[instanceName]Repository->get[ModelName]ById([parameterId]: $[parameterId], fields: $fields);
        return isset($data) ? $data : [];
    }
}
