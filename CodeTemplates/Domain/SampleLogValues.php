<?php

declare(strict_types=1);

namespace App\Domain\[DomainFolder];

/**
 * Description of Departments
 *
 * @author nazmulislam
 */
class [ClassName]LogValues
{
    CONST ACTION_CREATE = 'CREATE';
    CONST ACTION_UPDATE = 'UPDATE';
    CONST ACTION_DELETE = 'DELETE';
   
    
    CONST MESSAGE_CREATE = 'New [LowerCaseWithSpace] %%TITLE%% was created by %%CREATED_BY%% at %%CREATED_AT%%';
    CONST MESSAGE_UPDATE = '[UCFirstWithSpace] %%TITLE%% was updated by %%UPDATED_BY%% at %%UPDATED_AT%%';
    CONST MESSAGE_DELETE = '[UCFirstWithSpace] %%TITLE%% was deleted by %%DELETED_BY%% at %%DELETED_AT%%';
    

}
