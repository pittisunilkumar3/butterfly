<?php

namespace Database\Seeders;

use App\Classes\PermsSeed;
use App\Models\Company;
use App\Models\Form;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('forms')->delete();

        DB::statement('ALTER TABLE `forms` AUTO_INCREMENT = 1');

        $formNames = [
            0 => [
                'name' => 'Default Form',
                'fields' => [
                    [
                        'name' => 'First Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l01',
                    ],
                    [
                        'name' => 'Last Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l02',
                    ],
                    [
                        'name' => 'Company',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l03',
                    ],
                    [
                        'name' => 'Email',
                        'type' => 'email',
                        'required' => 0,
                        'id' => 'bq2utas4l04',
                    ],
                    [
                        'name' => 'Website',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l05',
                    ],
                    [
                        'name' => 'Phone No.',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l06',
                    ],
                    [
                        'name' => 'Notes',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4l07',
                    ],
                ]
            ],
            1 => [
                'name' => 'Software Development Form',
                'fields' => [
                    [
                        'name' => 'First Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4101',
                    ],
                    [
                        'name' => 'Last Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4102',
                    ],
                    [
                        'name' => 'Company Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4103',
                    ],
                    [
                        'name' => 'Email',
                        'type' => 'email',
                        'required' => 0,
                        'id' => 'bq2utas4104',
                    ],
                    [
                        'name' => 'Contact No.',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4105',
                    ],
                    [
                        'name' => 'Software Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4106',
                    ],
                    [
                        'name' => 'Budget',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4107',
                    ],
                    [
                        'name' => 'Duration',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4108',
                    ],
                    [
                        'name' => 'Notes',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas4109',
                    ],
                ]
            ],
            2 => [
                'name' => 'Insurance Enquiry Form',
                'fields' => [
                    [
                        'name' => 'Name',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1101',
                    ],
                    [
                        'name' => 'Mobile',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1102',
                    ],
                    [
                        'name' => 'Alternative Number',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1103',
                    ],
                    [
                        'name' => 'Email',
                        'type' => 'email',
                        'required' => 0,
                        'id' => 'bq2utas1104',
                    ],
                    [
                        'name' => 'Salary',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1105',
                    ],
                    [
                        'name' => 'Gender',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1106',
                    ],
                    [
                        'name' => 'DOB',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1107',
                    ],
                    [
                        'name' => 'Married',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1108',
                    ],
                    [
                        'name' => 'Type of Insurance',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1109',
                    ],
                    [
                        'name' => 'Notes',
                        'type' => 'text',
                        'required' => 0,
                        'id' => 'bq2utas1110',
                    ],
                ]
            ]
        ];

        $faker = \Faker\Factory::create();
        $company = Company::where('is_global', 0)->first();
        $users = User::where('name', 'Admin')
            ->orWhere('name', 'Manager')
            ->orWhere('name', 'Member')
            ->pluck('id');
        foreach ($formNames as $formName) {
            $randomUser = $faker->randomElement($users);

            $form = new Form();
            $form->company_id = $company->id;
            $form->name = $formName['name'];
            $form->form_fields = $formName['fields'];
            $form->status = 1;
            $form->created_by = $randomUser;
            $form->updated_by = $randomUser;
            $form->save();
        }
    }
}
