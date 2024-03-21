<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\Traits;

use NazmulIslam\Utility\Models\NazmulIslam\Utility\ProductPolicy;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\SystemModule;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\UserRole;
use DateTime;


trait UserTraits
{

    public array $scopes = [];
    public array $filteredModules = [];

    public function getUserRoles(int $userId): array
    {

        $userRoles = UserRole::select('role.role_meta_data')
            ->JoinRole()
            ->JoinUser()
            ->where('user_role.user_id', $userId)
            ->get();
        if (isset($userRoles)) {
            foreach ($userRoles as $userRole) {



                $roleScopes = isset($userRole['role_meta_data']) ? json_decode($userRole['role_meta_data'], true) : [];

                $this->setFilteredScope($roleScopes);
                //                 Logger::info('role',[$roleScopes]);
                if (is_array($roleScopes) && count($roleScopes) > 0) {
                    $this->setScopes($this->filteredModules);
                }
            }
        }

        return $this->scopes ?? [];
    }

    private function setFilteredScope(array $roleScopes): void
    {
        foreach ($roleScopes as $scope) {
            if (in_array($scope['module_identifier'], $this->getSubscribedModules())) {
                $this->filteredModules[] = $scope;
            }
        }
    }

    private function setScopes(array $roleScopes)
    {



        foreach ($roleScopes as $scope) {

            if (isset($scope['module_identifier']) && isset($scope['selectedPermission']) && in_array('can_read', $scope['selectedPermission'])) {
                $this->scopes['scopes'][] = $scope['module_identifier'];
                $this->scopes['permissions'][$scope['module_identifier']] = $scope['selectedPermission'];
                $this->scopes['modules'][] = $scope;

                if (isset($scope['app_route']) && $scope['app_route'] !== '') {
                    $this->scopes['appRoutes'][] = $scope['app_route'];
                }
            }
            if (isset($scope['child']) && is_array($scope['child']) && count($scope['child']) > 0) {
                $this->setScopes($scope['child']);
            }
        }
    }

    public function getSubscribedModules(): array
    {
    
        $subscribedModules = [
            'hr_user_dashboard',
            'social_media_screening',
            'cyber_security',
            'hr',
            'sales',
            'lms',
            'tasks',
            'service_desk',
            'workflow',
            'workflow_builder',
            'settings'
        ];
        return $subscribedModules;
    }

    public function getMenuAndPermissions($user)
    {
      

        $modulePermissions = $this->getModulesWithAccess($user);
        $moduleIdentifiers = [];

        foreach ($modulePermissions ?? [] as $permission) {
            if (isset($permission['moduleIdentifier'])) {
                $moduleIdentifiers[] = $permission['moduleIdentifier'];
            }
        }

        return [
      
            //'subscribedProductPrefixes' => $this->subscribedProducts(),
            'policyIdentifiers'  => $this->policyIdentifiers($user),
            'menuGroup'          => $user->Role?->RoleMenu?->role_menu_identifier ?? "",
            'moduleIdentifiers'  => $moduleIdentifiers,
            'modulePermissions'  => $modulePermissions,
        ];
    }




    public function moduleIdentifiers($user): array
    {


        $moduleIdentifiers = [];

        if ($user->Role !== null && $user->Role->RolePolicies->count()) {
            foreach ($user->Role->RolePolicies as $rolePolicy) {
                if ($rolePolicy->ProductPolicy->module_json != null) {
                    $decodedIdentifiers = json_decode($rolePolicy->ProductPolicy->module_json, true);
                    $moduleIdentifiers[] = array_keys($decodedIdentifiers);
                }
            }
        }



        $userAdditionalAccess = $user->additional_access == null ? null : json_decode($user->additional_access, true);

        if ($userAdditionalAccess != null && $userAdditionalAccess['has_additional_access']) {
            if ($userAdditionalAccess['access_type'] == 1) {
                $policies = $this->getPoliciesForUser($userAdditionalAccess);

                if ($policies->count()) {
                    $this->getModuleIdentifiers($policies, $moduleIdentifiers);
                }
            } else if ($userAdditionalAccess['access_type'] == 2) {
                if (count($userAdditionalAccess['date_range']) > 0) {

                    $startDate  = $userAdditionalAccess['date_range'][0];
                    $endDate    = $userAdditionalAccess['date_range'][1];

                    $currentDate    = new DateTime();
                    $startDateObj   = new DateTime($startDate);
                    $endDateObj     = new DateTime($endDate);

                    if ($currentDate >= $startDateObj && $currentDate <= $endDateObj) {
                        $policies = $this->getPoliciesForUser($userAdditionalAccess);

                        if ($policies->count()) {
                            $this->getModuleIdentifiers($policies, $moduleIdentifiers);
                        }
                    }
                }
            }
        }

        if (count($moduleIdentifiers)) {
            $moduleIdentifiers = array_merge(...$moduleIdentifiers);
        }
        return $moduleIdentifiers;
    }

    public function policyIdentifiers($user): array
    {


        $policyIdentifiers = [];

        if ($user->Role !== null && $user->Role->RolePolicies->count()) {
            foreach ($user->Role->RolePolicies as $rolePolicy) {
                if ($rolePolicy->ProductPolicy != null) {
                    if ($rolePolicy->ProductPolicy->master_product_policy_identifier != null) {
                        $policyIdentifiers[] = $rolePolicy->ProductPolicy->master_product_policy_identifier;
                    }
                }
            }
        }



        $userAdditionalAccess = $user->additional_access == null ? null : json_decode($user->additional_access, true);

        if ($userAdditionalAccess != null && $userAdditionalAccess['has_additional_access']) {
            if ($userAdditionalAccess['access_type'] == 1) {
                $policies = $this->getPoliciesForUser($userAdditionalAccess);

                if ($policies->count()) {
                    $this->getPolicyIdentifiers($policies, $policyIdentifiers);
                }
            } else if ($userAdditionalAccess['access_type'] == 2) {
                if (count($userAdditionalAccess['date_range']) > 0) {

                    $startDate  = $userAdditionalAccess['date_range'][0];
                    $endDate    = $userAdditionalAccess['date_range'][1];

                    $currentDate    = new DateTime();
                    $startDateObj   = new DateTime($startDate);
                    $endDateObj     = new DateTime($endDate);

                    if ($currentDate >= $startDateObj && $currentDate <= $endDateObj) {
                        $policies = $this->getPoliciesForUser($userAdditionalAccess);

                        if ($policies->count()) {
                            $this->getPolicyIdentifiers($policies, $policyIdentifiers);
                        }
                    }
                }
            }
        }

        return $policyIdentifiers;
    }

    public function appMenuItems($user): array
    {


        $modulesIdsArray = [];

        if ($user->Role !== null && $user->Role->RolePolicies->count()) {
            foreach ($user->Role->RolePolicies as $rolePolicy) {
                if ($rolePolicy->ProductPolicy->ProductPolicyDetail->count()) {
                    foreach ($rolePolicy->ProductPolicy->ProductPolicyDetail as $policyDetail) {
                        $checkPermissions = $this->getCorrectPermissionsArray($policyDetail->permission);
                        if (count($checkPermissions)) {

                            $modulesIdsArray[] = $policyDetail->SystemModule->system_module_id;
                        }
                    }
                }
            }
        }



        $userAdditionalAccess = $user->additional_access == null ? null : json_decode($user->additional_access, true);

        if ($userAdditionalAccess != null && $userAdditionalAccess['has_additional_access']) {
            if ($userAdditionalAccess['access_type'] == 1) {
                $policies = $this->getPoliciesForUser($userAdditionalAccess);

                if ($policies->count()) {
                    $this->getModulesIds($policies, $modulesIdsArray);
                }
            } else if ($userAdditionalAccess['access_type'] == 2) {
                if (count($userAdditionalAccess['date_range']) > 0) {

                    $startDate  = $userAdditionalAccess['date_range'][0];
                    $endDate    = $userAdditionalAccess['date_range'][1];

                    $currentDate    = new DateTime();
                    $startDateObj   = new DateTime($startDate);
                    $endDateObj     = new DateTime($endDate);

                    if ($currentDate >= $startDateObj && $currentDate <= $endDateObj) {
                        $policies = $this->getPoliciesForUser($userAdditionalAccess);

                        if ($policies->count()) {
                            $this->getModulesIds($policies, $modulesIdsArray);
                        }
                    }
                }
            }
        }

        if (count($modulesIdsArray) > 0) {
            SystemModule::fixTree();

            $tree = SystemModule::whereIn('system_module_id', $modulesIdsArray)->get()->toTree();
            $appMenuItems = $this->convertTreeDataToAppMenuItems($tree);

            return $appMenuItems;
        }

        return [];
    }


    public function getModulesWithAccess($user): array
    {

        $modulesAccess = [];


        if ($user->Role !== null && $user->Role->RolePolicies->count()) {
            foreach ($user->Role->RolePolicies as $rolePolicy) {
                if(isset($rolePolicy->ProductPolicy->module_json)){
                    if ($rolePolicy->ProductPolicy->module_json != null) {
                        $modulesJson = json_decode($rolePolicy->ProductPolicy->module_json,true);
    
                        foreach ($modulesJson as $module) {
                            $this->extractPermissionsRecursive($module, $modulesAccess);
                        }
                    }
                }
              
            }
        }

        $userAdditionalAccess = $user->additional_access == null ? null : json_decode($user->additional_access, true);

        if ($userAdditionalAccess != null && $userAdditionalAccess['has_additional_access']) {
            if ($userAdditionalAccess['access_type'] == 1) {
                $policies = $this->getPoliciesForUser($userAdditionalAccess);

                if ($policies->count()) {
                    $this->getModulesWithPermissions($policies, $modulesAccess);
                }
            } else if ($userAdditionalAccess['access_type'] == 2) {
                if (count($userAdditionalAccess['date_range']) > 0) {

                    $startDate  = $userAdditionalAccess['date_range'][0];
                    $endDate    = $userAdditionalAccess['date_range'][1];

                    $currentDate    = new DateTime();
                    $startDateObj   = new DateTime($startDate);
                    $endDateObj     = new DateTime($endDate);

                    if ($currentDate >= $startDateObj && $currentDate <= $endDateObj) {
                        $tempPolicies = $this->getPoliciesForUser($userAdditionalAccess);

                        if ($tempPolicies->count()) {
                            $this->getModulesWithPermissions($tempPolicies, $modulesAccess);
                        }
                    }
                }
            }
        }

        return $modulesAccess;
    }

    private function extractPermissionsRecursive($module, &$modulesAccess) {
        $permissions = $module['permissions'];
        $anyPermissionTrue = false;
    
        foreach ($permissions as $permission) {
            if ($permission === true) {
                $anyPermissionTrue = true;
                break; // Exit the loop as soon as a true permission is found
            }
        }
    
        if ($anyPermissionTrue === true) {
            $modulesAccess[] = [
                'moduleIdentifier' => $module['data'],
                'modulePermission' => $module['permissions'],
            ];
        }
    
        if (isset($module['children']) && is_array($module['children'])) {
            foreach ($module['children'] as $childModule) {
                $this->extractPermissionsRecursive($childModule, $modulesAccess);
            }
        }
    }

    public function getPoliciesForUser($userAdditionalAccess)
    {
        if (count($userAdditionalAccess['master_product_policy_ids']) > 0) {
            return ProductPolicy::with('ProductPolicyDetail.SystemModule')
                ->select('master_product_policy_id', 'master_product_policy_identifier','module_json')
                ->whereIn('master_product_policy_id', $userAdditionalAccess['master_product_policy_ids'])
                ->get();
        }

        return collect([]);
    }

    public function getModulesWithPermissions($policies, &$modulesAccess)
    {
        foreach($policies as $policy) {
            $modulesJson = json_decode($policy->module_json,true);

            foreach ($modulesJson as $module) {
                $this->extractPermissionsRecursive($module, $modulesAccess);
            }
        }


        // foreach ($policies as $policy) {
        //     foreach ($policy->ProductPolicyDetail as $policyDetail) {
        //         $checkPermissions = $this->getCorrectPermissionsArray($policyDetail->permission);

        //         if (count($checkPermissions)) {
        //             $modulesAccess[] = [
        //                 'pathName'    => $policyDetail->SystemModule->app_route,
        //                 'permissions' => $checkPermissions,
        //             ];
        //         }
        //     }
        // }
    }

    public function getModulesIds($policies, &$modulesIdsArray)
    {

        foreach ($policies as $policy) {
            foreach ($policy->ProductPolicyDetail as $policyDetail) {
                $checkPermissions = $this->getCorrectPermissionsArray($policyDetail->permission);

                if (count($checkPermissions)) {
                    $modulesIdsArray[] = $policyDetail->SystemModule->system_module_id;
                }
            }
        }
    }

    public function getPolicyIdentifiers($policies, &$policyIdentifiers)
    {
        foreach ($policies as $policy) {
            if ($policy->master_product_policy_identifier) {
                $policyIdentifiers[] = $policy->master_product_policy_identifier;
            }
        }
    }

    public function getModuleIdentifiers($policies, &$moduleIdentifiers)
    {
        foreach ($policies as $policy) {
            if ($policy->module_json != null) {

                $decodedIdentifiers = json_decode($policy->module_json, true);
                $moduleIdentifiers[] = array_keys($decodedIdentifiers);
            }
        }
    }

    private function getCorrectPermissionsArray($encodedPermission): array
    {
        if ($encodedPermission !== null) {

            $permission = json_decode($encodedPermission);
            $truePermissions = [];
            switch (true) {
                case $permission->create:
                    $truePermissions[] = 'create';
                case $permission->read:
                    $truePermissions[] = 'read';
                case $permission->update:
                    $truePermissions[] = 'update';
                case $permission->delete:
                    $truePermissions[] = 'delete';
            }
            return $truePermissions;
        }
        return [];
    }

    private function convertTreeDataToAppMenuItems($treeData, $parentKey = '0')
    {
        $convertedData = [];

        foreach ($treeData as $node) {
            $key = $parentKey . '-' . $node['system_module_id'];

            $convertedNode = [
                'disabled'  => false,
                'icon'      => $node['icon'],
                'isActive'  => 1,
                'isPrivate' => true,
                'label'     => $node['name'],
                'path'      => $node['app_route'],
            ];

            if (isset($node['children']) && count($node['children'])) {
                $convertedNode['items'] = $this->convertTreeDataToAppMenuItems($node['children'], $key);
            }

            $convertedData[] = $convertedNode;
        }

        return $convertedData;
    }
}
