<?php

namespace Database\Seeders;

use App\Classes\Common;
use App\Models\Campaign;
use App\Models\CampaignUser;
use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\Form;
use App\Models\Lead;
use App\Models\LeadLog;
use App\Models\Salesman;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('campaigns')->delete();
        DB::table('leads')->delete();
        DB::table('lead_logs')->delete();

        DB::statement('ALTER TABLE `campaigns` AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE `leads` AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE `lead_logs` AUTO_INCREMENT = 1');

        $faker = \Faker\Factory::create();
        $company = Company::where('is_global', 0)->first();
        $emailTemplate = EmailTemplate::withoutGlobalScope(CompanyScope::class)->first();

        Campaign::factory()->count(7)->create([
            'company_id' => $company->id,
            'email_template_id' => $emailTemplate->id,
            'detail_fields' => []
        ])->each(function ($campaign) use ($faker, $company) {

            $assignedMembers = $this->assignMembers($campaign);

            if ($campaign->status == 'completed' || $campaign->status == 'started') {
                $completedLeads = $campaign->total_leads - $campaign->remaining_leads;

                if ($completedLeads > 0) {

                    $lastActionerId = null;

                    Lead::factory()->count($completedLeads)->create([
                        'company_id' => $company->id,
                        'campaign_id' => $campaign->id,
                        'started' => 1,
                    ])->each(function ($lead) use ($campaign, $faker, $assignedMembers, $company, $lastActionerId) {
                        // Call actioned by
                        if ($lead->started) {
                            $leadActionUser = CampaignUser::where('campaign_id', $campaign->id)
                                ->inRandomOrder()
                                ->first();
                            $lead->first_action_by = $leadActionUser->user_id;
                            $lead->last_action_by = $leadActionUser->user_id;
                            $lead->created_at = $faker->dateTimeBetween($campaign->started_on, 'now');
                            $lead->updated_at = $faker->dateTimeBetween($lead->created_at, 'now');

                            $callLog = new LeadLog();
                            $callLog->company_id = $company->id;
                            $callLog->lead_id = $lead->id;
                            $callLog->log_type = 'call_log';
                            $callLog->user_id = $leadActionUser->user_id;
                            $callLog->started_on = 0;
                            $callLog->time_taken = $lead->time_taken;
                            $callLog->date_time = $faker->dateTimeBetween($campaign->started_on, 'now');
                            $callLog->created_by_id = $leadActionUser->user_id;
                            $callLog->updated_by_id = $leadActionUser->user_id;
                            $callLog->created_at = $faker->dateTimeBetween($campaign->started_on, 'now');
                            $callLog->updated_at = $faker->dateTimeBetween($lead->created_at, 'now');
                            $callLog->save();

                            $lastActionerId = $leadActionUser->user_id;
                        }

                        $lead->reference_number = $campaign->auto_reference == 1 ? $campaign->auto_reference_prefix . '_' . rand(10000, 9999999) : 'MEP_' . rand(10000, 9999999);

                        // Saving Form Data
                        $currentForm = Form::withoutGlobalScope(CompanyScope::class)->first();
                        $lead->lead_data = $this->generateLeadData($faker, $currentForm);
                        $lead->save();

                        if ($campaign->status == 'started') {
                            $pendingCallBackRandomNumber = rand(1, 4);
                            if ($pendingCallBackRandomNumber == 1) {
                                $callBack = new LeadLog();
                                $callBack->company_id = $company->id;
                                $callBack->lead_id = $lead->id;
                                $callBack->log_type = 'lead_follow_up';
                                $callBack->date_time = $faker->dateTimeBetween($lead->updated_at, '+5 days');
                                $callBack->user_id = $faker->randomElement($assignedMembers);
                                $callBack->created_by_id = $leadActionUser->user_id;
                                $callBack->updated_by_id = $leadActionUser->user_id;
                                $callBack->time_taken = 0;
                                $callBack->save();

                                $lead->lead_follow_up_id = $callBack->id;
                                $lead->save();
                            }

                            // Sales Members
                            $appointmentRandomNumber = rand(1, 4);
                            if ($appointmentRandomNumber == 1) {
                                $randomSalesMember = Salesman::inRandomOrder()->first();

                                $appointment = new LeadLog();
                                $appointment->company_id = $company->id;
                                $appointment->lead_id = $lead->id;
                                $appointment->log_type = 'salesman_booking';
                                $appointment->date_time = $faker->dateTimeBetween('+2 days', '+10 days');
                                $appointment->user_id = $randomSalesMember->id;
                                $appointment->created_by_id = $leadActionUser->user_id;
                                $appointment->updated_by_id = $leadActionUser->user_id;
                                $appointment->time_taken = 0;
                                $appointment->save();

                                $lead->salesman_booking_id = $appointment->id;
                                $lead->save();
                            }
                        }

                        $campaign->last_action_by = $lastActionerId;
                        $campaign->save();
                    });
                }

                if ($campaign->remaining_leads > 0) {

                    Lead::factory()->count($campaign->remaining_leads)->create([
                        'company_id' => $company->id,
                        'campaign_id' => $campaign->id,
                        'started' => 0,
                    ])->each(function ($lead) use ($campaign, $faker, $assignedMembers, $company) {
                        // Saving Form Data
                        $currentForm = Form::withoutGlobalScope(CompanyScope::class)->first();
                        $lead->lead_data = $this->generateLeadData($faker, $currentForm);
                        $lead->save();
                    });
                }
            }

            Common::recalculateCampaignLeads($campaign->id);
        });
    }

    public function generateLeadData($faker, $form)
    {
        $leadData = [];
        $formFields = $form->form_fields;
        foreach ($formFields as $formField) {
            $leadData[] = [
                'id' => Str::random(6),
                'field_name' => $formField['name'],
                'field_value' => $this->getFieldValue($formField['name'], $faker),
            ];
        }

        return $leadData;
    }

    public function assignMembers($campaign)
    {
        $faker = \Faker\Factory::create();
        $mainUsers = [];

        $randomAdminMembers = User::select('users.id')
            ->where('email', 'admin@example.com')
            ->first();
        if ($randomAdminMembers) {
            $mainUsers[] = $randomAdminMembers->id;
        }

        $managerWillBeAssigned = $faker->numberBetween(0, 1);
        if ($managerWillBeAssigned == 1) {
            $randomManagerMembers = User::select('users.id')
                ->where('email', 'manager@example.com')
                ->first();
            if ($randomManagerMembers) {
                $mainUsers[] = $randomManagerMembers->id;
            }
        }


        $memberWillBeAssigned = $faker->numberBetween(0, 1);
        if ($memberWillBeAssigned == 1) {
            $randomMemberMembers = User::select('users.id')
                ->where('email', 'member@example.com')
                ->first();
            if ($randomMemberMembers) {
                $mainUsers[] = $randomMemberMembers->id;
            }
        }

        foreach ($mainUsers as $mainUser) {
            $campaignMember = new CampaignUser();
            $campaignMember->user_id = $mainUser;
            $campaignMember->campaign_id = $campaign->id;
            $campaignMember->save();
        }

        $randomOtherMembers = User::select('users.id')
            ->whereNotIn('id', $mainUsers)
            ->inRandomOrder()->take(rand(1, 2))->get();

        foreach ($randomOtherMembers as $randomMember) {
            $campaignMember = new CampaignUser();
            $campaignMember->user_id = $randomMember->id;
            $campaignMember->campaign_id = $campaign->id;
            $campaignMember->save();

            $mainUsers[] = $randomMember->id;
        }

        return $mainUsers;
    }

    public function getFieldValue($fieldName, $faker)
    {
        $value = '';
        $currencySymbol = [
            '€',
            '£',
            '$',
            '¥',
            '₹',
        ];
        $currency = $faker->randomElement($currencySymbol);

        if ($fieldName == 'First Name') {
            $value = $faker->firstName();
        } else if ($fieldName == 'Last Name') {
            $value = $faker->lastName;
        } else if ($fieldName == 'Name') {
            $value = $faker->firstName() . ' ' . $faker->lastName;
        } else if ($fieldName == 'Company' || $fieldName == 'Company Name') {
            $value = $faker->company;
        } else if ($fieldName == 'Email') {
            $value = $faker->safeEmail;
        } else if ($fieldName == 'Website') {
            $value = $faker->domainName;
        } else if ($fieldName == 'Phone No.' || $fieldName == 'Contact No.' || $fieldName == 'Mobile') {
            $value = $faker->e164PhoneNumber;
        } else if ($fieldName == 'Alternative Number') {
            $value = $faker->tollFreePhoneNumber;
        } else if ($fieldName == 'Notes') {
            $value = $faker->realText($faker->numberBetween(50, 80));
        } else if ($fieldName == 'Software Name') {
            $softwareNames = [
                'ERP Software',
                'NetSuite',
                'CRM Software',
                'Lead Management Software',
                'School Management Software',
                'Online Photo Edit Software',
            ];

            $value = $faker->randomElement($softwareNames);
        } else if ($fieldName == 'Budget') {


            $value = $currency . '' . $faker->numberBetween(1000, 5000);
        } else if ($fieldName == 'Duration') {
            $value = $faker->numberBetween(1, 10) . ' Months';
        } else if ($fieldName == 'Salary') {
            $value = $value = $currency . '' . $faker->numberBetween(10000, 50000);
        } else if ($fieldName == 'Gender') {
            $gender = [
                'Male',
                'Female'
            ];

            $value = $faker->randomElement($gender);
        } else if ($fieldName == 'DOB') {
            $value = $faker->dateTimeBetween('-50 years', '-20 years');
        } else if ($fieldName == 'Married') {
            $married = [
                'Yes',
                'No'
            ];

            $value = $faker->randomElement($married);
        } else if ($fieldName == 'Type of Insurance') {
            $insurance = [
                'Property Insurance',
                'Life Insurance',
                'Home Loan Insurance',
                'Car Insurance',
                'Bike Insurance',
                'Term Plan Insurance'
            ];

            $value = $faker->randomElement($insurance);
        }

        return $value;
    }
}
