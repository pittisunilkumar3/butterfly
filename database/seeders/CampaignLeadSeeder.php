<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class CampaignLeadSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        // Get the first company or create a default one
        $company = Company::first() ?? Company::create([
            'name' => 'Betterfly',
            'is_global' => 0,
            'status' => 'active'
        ]);

        // Create you as a user
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'AI Assistant',
            'email' => 'ai.assistant@betterfly.com',
            'password' => bcrypt(Str::random(40)), // Secure random password
            'email_verification_code' => Str::random(50),
            'status' => 'enabled',
            'user_type' => 'staff_members',
            'login_enabled' => true,
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'd-m-Y',
            'date_picker_format' => 'dd-mm-yyyy',
            'time_format' => 'h:i a'
        ]);

        // Create a campaign
        $campaign = Campaign::create([
            'name' => 'Customer Acquisition 2024',
            'reference_prefix' => 'CA2024',
            'allow_reference_prefix' => true,
            'status' => 'started',
            'started_on' => now(),
            'total_leads' => 5,
            'remaining_leads' => 5,
            'created_by' => $user->id,
            'last_action_by' => $user->id,
            'detail_fields' => json_encode([
                'description' => 'Targeted outreach for new customer acquisition',
                'target_industry' => 'Technology',
                'target_region' => 'North America'
            ])
        ]);

        // Create leads with phone numbers
        $leads = [
            [
                'name' => 'John Smith',
                'phone' => '+1 (555) 123-4567',
                'email' => 'john.smith@example.com',
                'company' => 'Tech Innovations Inc.',
                'source' => 'LinkedIn',
                'status' => 'new'
            ],
            [
                'name' => 'Emily Johnson',
                'phone' => '+1 (555) 987-6543',
                'email' => 'emily.johnson@example.com',
                'company' => 'Digital Solutions LLC',
                'source' => 'Referral',
                'status' => 'contacted'
            ],
            [
                'name' => 'Michael Chen',
                'phone' => '+1 (555) 246-8135',
                'email' => 'michael.chen@example.com',
                'company' => 'Global Enterprises',
                'source' => 'Trade Show',
                'status' => 'qualified'
            ],
            [
                'name' => 'Sarah Rodriguez',
                'phone' => '+1 (555) 369-2580',
                'email' => 'sarah.rodriguez@example.com',
                'company' => 'Innovative Startups',
                'source' => 'Website',
                'status' => 'new'
            ],
            [
                'name' => 'David Kim',
                'phone' => '+1 (555) 147-2589',
                'email' => 'david.kim@example.com',
                'company' => 'Future Technologies',
                'source' => 'Cold Call',
                'status' => 'in_progress'
            ]
        ];

        // Create leads and associate with campaign
        foreach ($leads as $leadData) {
            $lead = Lead::create([
                'campaign_id' => $campaign->id,
                'lead_data' => json_encode([
                    'name' => $leadData['name'],
                    'phone' => $leadData['phone'],
                    'email' => $leadData['email'],
                    'company' => $leadData['company'],
                    'source' => $leadData['source']
                ]),
                'lead_status' => $leadData['status'],
                'lead_hash' => Str::uuid(),
                'created_by' => $user->id,
                'first_action_by' => $user->id,
                'last_action_by' => $user->id,
                'started' => 0,
                'reference_number' => $campaign->reference_prefix . '-' . Str::random(6)
            ]);
        }
    }
}
