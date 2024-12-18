<?php

namespace App\Classes;

use App\Models\Permission;
use Nwidart\Modules\Facades\Module;

class PermsSeed
{
    public static $mainPermissionsArray = [

        // Users
        'users_view' => [
            'name' => 'users_view',
            'display_name' => 'Staff Member View'
        ],
        'users_create' => [
            'name' => 'users_create',
            'display_name' => 'Staff Member Create'
        ],
        'users_edit' => [
            'name' => 'users_edit',
            'display_name' => 'Staff Member Edit'
        ],
        'users_delete' => [
            'name' => 'users_delete',
            'display_name' => 'Staff Member Delete'
        ],

        // Salesmans
        'salesmans_view' => [
            'name' => 'salesmans_view',
            'display_name' => 'Salesman View'
        ],
        'salesmans_create' => [
            'name' => 'salesmans_create',
            'display_name' => 'Salesman Create'
        ],
        'salesmans_edit' => [
            'name' => 'salesmans_edit',
            'display_name' => 'Salesman Edit'
        ],
        'salesmans_delete' => [
            'name' => 'salesmans_delete',
            'display_name' => 'Salesman Delete'
        ],

        // Campaigns
        'campaigns_view' => [
            'name' => 'campaigns_view',
            'display_name' => 'Campaigns View'
        ],
        'campaigns_view_all' => [
            'name' => 'campaigns_view_all',
            'display_name' => 'View All Campaigns'
        ],
        'view_completed_campaigns' => [
            'name' => 'view_completed_campaigns',
            'display_name' => 'View All Completed Campaigns'
        ],
        'campaigns_create' => [
            'name' => 'campaigns_create',
            'display_name' => 'Campaigns Create'
        ],
        'campaigns_edit' => [
            'name' => 'campaigns_edit',
            'display_name' => 'Campaigns Edit'
        ],
        'campaigns_delete' => [
            'name' => 'campaigns_delete',
            'display_name' => 'Campaigns Delete'
        ],

        // Leads
        'leads_view_all' => [
            'name' => 'leads_view_all',
            'display_name' => 'View All Leads'
        ],
        'leads_create' => [
            'name' => 'leads_create',
            'display_name' => 'Leads Create'
        ],
        'leads_delete' => [
            'name' => 'leads_delete',
            'display_name' => 'Leads Delete'
        ],

        // email_templates
        'email_templates_view' => [
            'name' => 'email_templates_view',
            'display_name' => 'Email Templates View'
        ],
        'email_templates_view_all' => [
            'name' => 'email_templates_view_all',
            'display_name' => 'View All Email Templates'
        ],
        'email_templates_create' => [
            'name' => 'email_templates_create',
            'display_name' => 'Email Templates Create'
        ],
        'email_templates_edit' => [
            'name' => 'email_templates_edit',
            'display_name' => 'Email Templates Edit'
        ],
        'email_templates_delete' => [
            'name' => 'email_templates_delete',
            'display_name' => 'Email Templates Delete'
        ],

        // Forms
        'forms_view' => [
            'name' => 'forms_view',
            'display_name' => 'form View'
        ],
        'forms_view_all' => [
            'name' => 'forms_view_all',
            'display_name' => 'View All Forms'
        ],
        'forms_create' => [
            'name' => 'forms_create',
            'display_name' => 'Forms Create'
        ],
        'forms_edit' => [
            'name' => 'forms_edit',
            'display_name' => 'Forms Edit'
        ],
        'forms_delete' => [
            'name' => 'forms_delete',
            'display_name' => 'Forms Delete'
        ],

        // Lead Table Fields
        'form_field_names_view' => [
            'name' => 'form_field_names_view',
            'display_name' => 'Lead Table Fields View'
        ],
        'form_field_names_create' => [
            'name' => 'form_field_names_create',
            'display_name' => 'Lead Table Fields Create'
        ],
        'form_field_names_edit' => [
            'name' => 'form_field_names_edit',
            'display_name' => 'Lead Table Fields Edit'
        ],
        'form_field_names_delete' => [
            'name' => 'form_field_names_delete',
            'display_name' => 'Lead Table Fields Delete'
        ],
         // Expense Category
         'expense_categories_view' => [
            'name' => 'expense_categories_view',
            'display_name' => 'Expense Category View'
        ],
        'expense_categories_create' => [
            'name' => 'expense_categories_create',
            'display_name' => 'Expense Category Create'
        ],
        'expense_categories_edit' => [
            'name' => 'expense_categories_edit',
            'display_name' => 'Expense Category Edit'
        ],
        'expense_categories_delete' => [
            'name' => 'expense_categories_delete',
            'display_name' => 'Expense Category Delete'
        ],

        // Expense
        'expenses_view' => [
            'name' => 'expenses_view',
            'display_name' => 'Expense View'
        ],
        'expenses_create' => [
            'name' => 'expenses_create',
            'display_name' => 'Expense Create'
        ],
        'expenses_edit' => [
            'name' => 'expenses_edit',
            'display_name' => 'Expense Edit'
        ],
        'expenses_delete' => [
            'name' => 'expenses_delete',
            'display_name' => 'Expense Delete'
        ],

        // Product
        'products_view' => [
            'name' => 'products_view',
            'display_name' => 'Product View'
        ],
        'products_create' => [
            'name' => 'products_create',
            'display_name' => 'Product Create'
        ],
        'products_edit' => [
            'name' => 'products_edit',
            'display_name' => 'Product Edit'
        ],
        'products_delete' => [
            'name' => 'products_delete',
            'display_name' => 'Product Delete'
        ],

        // Currency
        'currencies_view' => [
            'name' => 'currencies_view',
            'display_name' => 'Currency View'
        ],
        'currencies_create' => [
            'name' => 'currencies_create',
            'display_name' => 'Currency Create'
        ],
        'currencies_edit' => [
            'name' => 'currencies_edit',
            'display_name' => 'Currency Edit'
        ],
        'currencies_delete' => [
            'name' => 'currencies_delete',
            'display_name' => 'Currency Delete'
        ],

        // Modules
        'modules_view' => [
            'name' => 'modules_view',
            'display_name' => 'Modules View'
        ],

        // Role
        'roles_view' => [
            'name' => 'roles_view',
            'display_name' => 'Role View'
        ],
        'roles_create' => [
            'name' => 'roles_create',
            'display_name' => 'Role Create'
        ],
        'roles_edit' => [
            'name' => 'roles_edit',
            'display_name' => 'Role Edit'
        ],
        'roles_delete' => [
            'name' => 'roles_delete',
            'display_name' => 'Role Delete'
        ],

        // Company
        'companies_edit' => [
            'name' => 'companies_edit',
            'display_name' => 'Company Edit'
        ],

        // Translation
        'translations_view' => [
            'name' => 'translations_view',
            'display_name' => 'Translation View'
        ],
        'translations_create' => [
            'name' => 'translations_create',
            'display_name' => 'Translation Create'
        ],
        'translations_edit' => [
            'name' => 'translations_edit',
            'display_name' => 'Translation Edit'
        ],
        'translations_delete' => [
            'name' => 'translations_delete',
            'display_name' => 'Translation Delete'
        ],

        // Storage Settings
        'storage_edit' => [
            'name' => 'storage_edit',
            'display_name' => 'Storage Settings Edit'
        ],

        // Email Settings
        'email_edit' => [
            'name' => 'email_edit',
            'display_name' => 'Email Settings Edit'
        ],

    ];

    public static $eStorePermissions = [];

    public static function getPermissionArray($moduleName)
    {
        return self::$mainPermissionsArray;
    }

    public static function seedPermissions($moduleName = '')
    {
        $permissions = self::getPermissionArray($moduleName);

        foreach ($permissions as $group => $permission) {
            $permissionCount = Permission::where('name', $permission['name'])->count();

            if ($permissionCount == 0) {
                $newPermission = new Permission();
                $newPermission->name = $permission['name'];
                $newPermission->display_name = $permission['display_name'];
                $newPermission->save();
            }
        }
    }

    public static function seedMainPermissions()
    {
        // Main Module
        self::seedPermissions();
    }

    public static function seedAllModulesPermissions()
    {
        // Main Module
        self::seedMainPermissions();

        $allModules = Module::all();
        foreach ($allModules as $allModule) {
            self::seedPermissions($allModule);
        }
    }
}
