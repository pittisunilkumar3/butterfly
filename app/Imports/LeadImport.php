<?php

namespace App\Imports;

use App\Classes\Common;
use App\Models\Campaign;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Str;

class LeadImport implements ToArray, WithHeadingRow
{
    public $importLeadFields = "";
    public $allFormFields = "";
    public $campaignId = "";

    public function __construct($importLeadFields, $allFormFields, $campaignId)
    {
        $this->importLeadFields = $importLeadFields;
        $this->allFormFields = $allFormFields;
        $this->campaignId = $campaignId;
    }

    public function array(array $leads)
    {
        $campaign = Campaign::withoutGlobalScope(CompanyScope::class)->find($this->campaignId);

        DB::transaction(function () use ($leads, $campaign) {

            $counter = 1;
            foreach ($leads as $lead) {

                // Calculating Lead Data Hash
                $leadHashString = "";
                $newLeadData = [];
                foreach ($this->allFormFields as $allFormField) {
                    $fieldName = $allFormField['name'];
                    $headerColumnName = array_search($fieldName, $this->importLeadFields);

                    if ($headerColumnName === false) {
                        $fieldValue = "";
                    } else {
                        $headerColumnSlug = Str::slug($headerColumnName, '_');
                        $fieldValue = $lead[$headerColumnSlug] ? $lead[$headerColumnSlug] : '';
                    }

                    $newLeadData[] = [
                        'id' => strtolower(Str::random(12)),
                        'field_name' => $fieldName,
                        'field_value' => $fieldValue,
                    ];

                    $leadHashString .= strtolower($fieldValue);
                }

                $leadHash = md5($leadHashString . $campaign->id);

                // Check if lead hash exists or not
                $leadHashCount = Lead::withoutGlobalScope(CompanyScope::class)
                    ->where('campaign_id', $campaign->id)
                    ->where('lead_hash', $leadHash)
                    ->count();

                if ($leadHashCount == 0) {

                    // Saving Lead
                    $lead = new Lead();
                    $lead->campaign_id = $campaign->id;

                    // Reference Prefix
                    if ($campaign->allow_reference_prefix) {
                        $timerCounter = Carbon::now()->timestamp + $counter;
                        $lead->reference_number = $campaign->reference_prefix . $timerCounter;
                    }

                    $lead->lead_data = $newLeadData;
                    $lead->created_by = user() ?  user()->id : null;
                    $lead->lead_hash = $leadHash;
                    $lead->save();

                    $counter = $counter + 1;
                }
            }

            // Calculating Lead Counts
            Common::recalculateCampaignLeads($campaign->id);
        });
    }
}
