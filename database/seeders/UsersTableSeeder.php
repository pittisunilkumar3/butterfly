<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\Salesman;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('users')->delete();
        DB::table('role_user')->delete();

        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE role_user AUTO_INCREMENT = 1');

        $count = env('SEED_RECORD_COUNT', 30);
        $faker = \Faker\Factory::create();

        $company = Company::where('is_global', 0)->first();

        // Admin User
        $adminRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'admin')->first();
        $admin = new User();
        $admin->company_id = $company->id;
        $admin->name = 'Admin';
        $admin->email = 'admin@example.com';
        $admin->password = '12345678';
        $admin->role_id = $adminRole->id;
        $admin->user_type = "staff_members";
        $admin->save();
        $admin->attachRole($adminRole->id);

        $company->admin_id = $admin->id;
        $company->save();

        // Manager
        $managerRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'manager')->first();
        $manager = new User();
        $manager->company_id = $company->id;
        $manager->name = 'Manager';
        $manager->email = 'manager@example.com';
        $manager->password = '12345678';
        $manager->role_id = $managerRole->id;
        $manager->user_type = "staff_members";
        $manager->save();
        $manager->attachRole($managerRole->id);

        // Member
        $memberRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'member')->first();
        $member = new User();
        $member->company_id = $company->id;
        $member->name = 'Member';
        $member->email = 'member@example.com';
        $member->password = '12345678';
        $member->role_id = $memberRole->id;
        $member->user_type = "staff_members";
        $member->save();
        $member->attachRole($memberRole->id);

        $allRoles = [
            $adminRole->id,
            $managerRole->id,
            $memberRole->id,

        ];

        // StaffMembers
        User::factory()->count((int)$count)->create([
            'company_id' => $company->id
        ])->each(function ($user) use ($faker, $allRoles) {

            $roleId = $faker->randomElement($allRoles);

            $user->role_id = $roleId;
            $user->save();

            // Assign Role
            $user->attachRole($roleId);
        });

        // Salesmans
        Salesman::factory()->count(33)->create([
            'company_id' => $company->id
        ]);
    }
}
