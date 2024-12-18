<?php

namespace Database\Seeders;

use App\Classes\PermsSeed;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permission_role')->delete();

        DB::statement('ALTER TABLE `permissions` AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE permission_role AUTO_INCREMENT = 1');

        PermsSeed::seedPermissions();

        $company = Company::where('is_global', 0)->first();

        $adminRole = new Role();
        $adminRole->company_id = $company->id;
        $adminRole->name = 'admin';
        $adminRole->display_name = 'Admin';
        $adminRole->description = 'Admin is allowed to manage everything of the app.';
        $adminRole->save();

        $member = new Role();
        $member->company_id = $company->id;
        $member->name = 'member';
        $member->display_name = 'Team Member';
        $member->description = 'Team Member can participate in campaigns which are assigned to him.';
        $member->save();

        $memberPermissions = Permission::whereIn('name', [
            'email_templates_view', 'email_templates_create', 'email_templates_edit', 'email_templates_delete',
            'forms_view', 'forms_create', 'forms_edit', 'forms_delete',
        ])->pluck('id');
        $member->savePermissions($memberPermissions);

        $manager = new Role();
        $manager->company_id = $company->id;
        $manager->name = 'manager';
        $manager->display_name = 'Manager';
        $manager->description = 'Team Manager can full permissions to manage campaigns.';
        $manager->save();

        $managerPermissions = Permission::whereIn('name', [
            'campaigns_view', 'campaigns_create', 'campaigns_edit', 'campaigns_delete',
            'email_templates_view', 'email_templates_create', 'email_templates_edit', 'email_templates_delete',
            'forms_view', 'forms_create', 'forms_edit', 'forms_delete',
            'salesmans_view', 'salesmans_create', 'salesmans_edit', 'salesmans_delete',
        ])->pluck('id');
        $manager->savePermissions($managerPermissions);
    }
}
