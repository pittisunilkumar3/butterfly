<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\FormFieldName;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormFieldNamesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('form_field_names')->delete();

        DB::statement('ALTER TABLE `form_field_names` AUTO_INCREMENT = 1');

        $company = Company::where('is_global', 0)->first();
        $user = User::first();

        $formField = new FormFieldName();
        $formField->company_id = $company->id;
        $formField->field_name = 'Name';
        $formField->similar_field_names = ['First Name', 'Name', 'name', 'first name', 'First name', 'first Name'];
        $formField->visible = 1;
        $formField->created_by = $user->id;
        $formField->updated_by = $user->id;
        $formField->save();

        $formField = new FormFieldName();
        $formField->company_id = $company->id;
        $formField->field_name = 'Email';
        $formField->similar_field_names = ['Email', 'email'];
        $formField->visible = 1;
        $formField->created_by = $user->id;
        $formField->updated_by = $user->id;
        $formField->save();
    }
}
