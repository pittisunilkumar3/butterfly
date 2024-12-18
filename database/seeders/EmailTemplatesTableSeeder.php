<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('email_templates')->delete();

        DB::statement('ALTER TABLE `email_templates` AUTO_INCREMENT = 1');

        $company = Company::where('is_global', 0)->first();
        $user = User::first();

        $emailTemplate = new EmailTemplate();
        $emailTemplate->company_id = $company->id;
        $emailTemplate->name = 'Welcome mail';
        $emailTemplate->subject = 'Welcome to lead pro';
        $emailTemplate->body = <<<FOD
    <p>Hi&nbsp;##First Name##,</p><p>Thanks for purchasing lead pro.</p><p>Thanks</p><p>##Company Name##<br></p>
FOD;

        $emailTemplate->sharable = 1;
        $emailTemplate->created_by = $user->id;
        $emailTemplate->updated_by = $user->id;
        $emailTemplate->save();
    }
}
