<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') != 'envato') {

            if (app_type() == 'saas') {
                $this->call(SubscriptionPlansTableSeeder::class);
            }
            $this->call([
                LangTableSeeder::class,
                CompanyTableSeeder::class,
                CurrencyTableSeeder::class,
                RolesTableSeeder::class,
                UsersTableSeeder::class,
                SettingTableSeeder::class,
                FormFieldNamesTableSeeder::class,
                EmailTemplatesTableSeeder::class,
                FormsTableSeeder::class,
                CampaignsTableSeeder::class,
                CampaignLeadSeeder::class,
                ConversationSeeder::class,
            ]);

            // Creating SuperAdmin
            if (app_type() == 'saas') {
                \App\SuperAdmin\Classes\SuperAdminCommon::createSuperAdmin(true);
            }
        }
    }
}
