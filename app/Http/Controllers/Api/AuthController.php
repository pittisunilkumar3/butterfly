<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ProfileRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\Auth\UploadFileRequest;
use App\Models\CampaignUser;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Form;
use App\Models\Lang;
use App\Models\Lead;
use App\Models\Salesman;
use App\Models\Settings;
use App\Models\StaffMember;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends ApiBaseController
{
    public function companySetting()
    {
        $settings = Company::first();

        return ApiResponse::make('Success', [
            'global_setting' => $settings,
        ]);
    }

    public function emailSettingVerified()
    {
        $emailSettingVerified = Settings::where('setting_type', 'email')
            ->where('status', 1)
            ->where('verified', 1)
            ->count();

        return $emailSettingVerified > 0 ? 1 : 0;
    }

    public function app()
    {
        $company = company(true);
        $addMenuSetting = $company ? Settings::where('setting_type', 'shortcut_menus')->first() : null;
        $totalCurrencies = Currency::count();

        return ApiResponse::make('Success', [
            'app' => $company,
            'shortcut_menus' => $addMenuSetting,
            'email_setting_verified' => $this->emailSettingVerified(),
            'total_currencies' => $totalCurrencies,
        ]);
    }

    public function checkSubscriptionModuleVisibility()
    {
        $request = request();
        $itemType = $request->item_type;

        $visible = Common::checkSubscriptionModuleVisibility($itemType);

        return ApiResponse::make('Success', [
            'visible' => $visible,
        ]);
    }

    public function visibleSubscriptionModules()
    {
        $visibleSubscriptionModules = Common::allVisibleSubscriptionModules();

        return ApiResponse::make('Success', $visibleSubscriptionModules);
    }

    public function allEnabledLangs()
    {
        $allLangs = Lang::select('id', 'name', 'key', 'image')->where('enabled', 1)->get();

        return ApiResponse::make('Success', [
            'langs' => $allLangs
        ]);
    }

    public function allForms()
    {
        $allForms = Form::select('id', 'name', 'form_fields')->get();

        return ApiResponse::make('Success', [
            'forms' => $allForms
        ]);
    }

    public function allUsers()
    {
        $request = request();

        if ($request->has('log_type') && $request->log_type == 'salesman_bookings') {
            $users = Salesman::select('id', 'name', 'profile_image')->get();
        } else {
            $users = StaffMember::select('id', 'name', 'profile_image')->get();
        }

        return ApiResponse::make('Success', [
            'users' => $users
        ]);
    }

    public function login(LoginRequest $request)
    {
        // Removing all sessions before login
        session()->flush();

        $phone = "";
        $email = "";

        $credentials = [
            'password' =>  $request->password
        ];

        if (is_numeric($request->get('email'))) {
            $credentials['phone'] = $request->email;
            $phone = $request->email;
        } else {
            $credentials['email'] = $request->email;
            $email = $request->email;
        }

        // For checking user
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        } else if ($phone != '') {
            $user = $user->where('phone', $phone);
        }
        $user = $user->first();

        // Adding user type according to email/phone
        if ($user) {
            $credentials['user_type'] = 'staff_members';
            $credentials['is_superadmin'] = 0;
            $userCompany = Company::where('id', $user->company_id)->first();
        }

        $message = 'Loggged in successfull';
        $status = 'success';

        if (!$token = auth('api')->attempt($credentials)) {
            $status = 'fail';
            $message = 'These credentials do not match our records.';
        } else if ($userCompany->status === 'pending') {
            $status = 'fail';
            $message = 'Your company not verified.';
        } else if ($userCompany->status === 'inactive') {
            $status = 'fail';
            $message = 'Company account deactivated.';
        } else if (auth('api')->user()->status === 'waiting') {
            $status = 'fail';
            $message = 'User not verified.';
        } else if (auth('api')->user()->status === 'disabled') {
            $status = 'fail';
            $message = 'Account deactivated.';
        }

        if ($status == 'success') {
            $company = company();
            $response = $this->respondWithToken($token);
            $addMenuSetting = Settings::where('setting_type', 'shortcut_menus')->first();
            $response['app'] = $company;
            $response['shortcut_menus'] = $addMenuSetting;
            $response['email_setting_verified'] = $this->emailSettingVerified();
            $response['visible_subscription_modules'] = Common::allVisibleSubscriptionModules();
        }
        $response['status'] = $status;
        $response['message'] = $message;

        return ApiResponse::make($message, $response);
    }

    protected function respondWithToken($token)
    {
        $user = user();

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()->addDays(180),
            'user' => $user
        ];
    }

    public function logout()
    {
        $request = request();

        if (auth('api')->user() && $request->bearerToken() != '') {
            auth('api')->logout();
        }

        session()->flush();

        return ApiResponse::make(__('Session closed successfully'));
    }

    public function user()
    {
        $user = auth('api')->user();
        $user = $user->load('role', 'role.perms');

        session(['user' => $user]);

        return ApiResponse::make('Data successfull', [
            'user' => $user
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $newToken = auth('api')->refresh();

        $response = $this->respondWithToken($newToken);

        return ApiResponse::make('Token fetched successfully', $response);
    }

    public function uploadFile(UploadFileRequest $request)
    {
        $result = Common::uploadFile($request);

        return ApiResponse::make('File Uploaded', $result);
    }

    public function profile(ProfileRequest $request)
    {
        $user = auth('api')->user();

        // In Demo Mode
        if (env('APP_ENV') == 'production') {
            $request = request();

            if ($request->email == 'admin@example.com' && $request->has('password') && $request->password != '') {
                throw new ApiException('Not Allowed In Demo Mode');
            }
        }

        $user->name = $request->name;
        if ($request->has('profile_image')) {
            $user->profile_image = $request->profile_image;
        }
        if ($request->password != '') {
            $user->password = $request->password;
        }
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->save();

        return ApiResponse::make('Profile updated successfull', [
            'user' => $user->load('role', 'role.perms')
        ]);
    }

    public function langTrans()
    {
        $langs = Lang::with('translations')->get();

        return ApiResponse::make('Langs fetched', [
            'data' => $langs
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = user();

        $yourCampaignCount = CampaignUser::join('campaigns', 'campaigns.id', '=', 'campaign_users.campaign_id')
            ->where('campaign_users.user_id', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            })
            ->count();

        $yourLeadCount = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->where('leads.last_action_by', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            });

        $totalTimes = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->where('leads.last_action_by', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            });

        $totalFollowUps = Lead::join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->join('lead_logs', 'lead_logs.id', '=', 'leads.lead_follow_up_id')
            ->where('lead_logs.user_id', '=', $user->id)
            ->where('campaigns.status', '!=', 'completed')
            ->whereNotNull('leads.lead_follow_up_id');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $yourLeadCount = $yourLeadCount->whereRaw('DATE(leads.updated_at) >= ?', [$startDate])
                ->whereRaw('DATE(leads.updated_at) <= ?', [$endDate]);
            $totalTimes = $totalTimes->whereRaw('DATE(leads.updated_at) >= ?', [$startDate])
                ->whereRaw('DATE(leads.updated_at) <= ?', [$endDate]);
            $totalFollowUps = $totalFollowUps->whereRaw('DATE(lead_logs.date_time) >= ?', [$startDate])
                ->whereRaw('DATE(lead_logs.date_time) <= ?', [$endDate]);
        }

        $yourLeadCount = $yourLeadCount->count();
        $totalTimes = $totalTimes->sum('time_taken');
        $totalFollowUps = $totalFollowUps->count();


        return ApiResponse::make('Data fetched', [
            'actionedCampaigns' => $this->getActionedCampaigns(),
            'callMade' => $this->getCallMade(),
            'allAppointments' => $this->getBookedAppointments(),
            'allFollowUps' => $this->getFollowUps(),
            'stateData' => [
                'campaign_count' => $yourCampaignCount,
                'lead_count' => $yourLeadCount,
                'total_times' => $totalTimes,
                'total_follow_ups' => $totalFollowUps,
            ]
        ]);
    }

    public function getActionedCampaigns()
    {
        $request = request();
        $user = user();

        $allActionedCampaigns = CampaignUser::select('campaigns.id', 'campaigns.name')
            ->join('campaigns', 'campaigns.id', '=', 'campaign_users.campaign_id')
            ->where('campaign_users.user_id', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            })
            ->get();

        $actionedCampaignName = [];
        $actionedCampaignLeads = [];
        $actionedCampaignColors = [];
        foreach ($allActionedCampaigns as $allActionedCampaign) {
            $totalLeads = Lead::where('leads.last_action_by', '=', $user->id)
                ->where('leads.campaign_id', '=', $allActionedCampaign->id);

            if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
                $dates = $request->dates;
                $startDate = $dates[0];
                $endDate = $dates[1];

                $totalLeads = $totalLeads->whereRaw('DATE(leads.updated_at) >= ?', [$startDate])
                    ->whereRaw('DATE(leads.updated_at) <= ?', [$endDate]);
            }

            $totalLeads = $totalLeads->count();

            $actionedCampaignName[] = $allActionedCampaign->name;
            $actionedCampaignLeads[] = $totalLeads;
            $actionedCampaignColors[] = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return [
            'labels' => $actionedCampaignName,
            'values' => $actionedCampaignLeads,
            'colors' => $actionedCampaignColors,
        ];
    }

    public function getCallMade()
    {
        $user = user();
        $request = request();

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        } else {
            $startDate =  Carbon::now()->subDays(30)->format("Y-m-d");
            $endDate =  Carbon::now()->format("Y-m-d");
        }

        $allLeads = Lead::select(DB::raw('date(leads.updated_at) as date, count(leads.id) as total_leads'))
            ->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->where('leads.last_action_by', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            })
            ->whereRaw('DATE(leads.updated_at) >= ?', [$startDate])
            ->whereRaw('DATE(leads.updated_at) <= ?', [$endDate])
            ->groupByRaw('date(leads.updated_at)')
            ->orderByRaw("date(leads.updated_at) asc")
            ->pluck('total_leads', 'date');

        $periodDates = CarbonPeriod::create($startDate, $endDate);
        $datesArray = [];
        $leadsCount = [];

        // Iterate over the period
        foreach ($periodDates as $periodDate) {
            $currentDate =  $periodDate->format('Y-m-d');

            if (isset($allLeads[$currentDate])) {
                $datesArray[] = $currentDate;
                $leadsCount[] = isset($allLeads[$currentDate]) ? $allLeads[$currentDate] : 0;
            }
        }

        return [
            'dates' => $datesArray,
            'calls' => $leadsCount,
        ];
    }

    public function getBookedAppointments()
    {
        $request = request();
        $user = user();

        $allAppointments = Lead::select('leads.id', 'leads.reference_number', 'leads.lead_data', 'leads.started', 'leads.campaign_id', 'leads.time_taken', 'leads.first_action_by', 'leads.last_action_by', 'leads.salesman_booking_id')
            ->with([
                'campaign' => function ($query) {
                    $query->select('id', 'name', 'status');
                },
                'firstActioner' => function ($query) {
                    $query->select('id', 'name');
                },
                'lastActioner' => function ($query) {
                    $query->select('id', 'name');
                },
                'salesmanBooking' => function ($query) {
                    $query->select('id', 'lead_id', 'user_id', 'date_time');
                },
                'salesmanBooking.user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->join('lead_logs', 'lead_logs.id', '=', 'leads.salesman_booking_id')
            ->where('leads.last_action_by', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            })
            ->whereNotNull('salesman_booking_id');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $allAppointments = $allAppointments->whereRaw('DATE(lead_logs.date_time) >= ?', [$startDate])
                ->whereRaw('DATE(lead_logs.date_time) <= ?', [$endDate]);
        }

        $allAppointments = $allAppointments->take(5)->get();


        return $allAppointments;
    }

    public function getFollowUps()
    {
        $request = request();
        $user = user();

        $allAppointments = Lead::select('leads.id', 'leads.reference_number', 'leads.lead_data', 'leads.started', 'leads.campaign_id', 'leads.time_taken', 'leads.first_action_by', 'leads.last_action_by', 'leads.lead_follow_up_id')
            ->with([
                'campaign' => function ($query) {
                    $query->select('id', 'name', 'status');
                },
                'firstActioner' => function ($query) {
                    $query->select('id', 'name');
                },
                'lastActioner' => function ($query) {
                    $query->select('id', 'name');
                },
                'leadFollowUp' => function ($query) {
                    $query->select('id', 'lead_id', 'user_id', 'date_time');
                },
                'leadFollowUp.user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->join('campaigns', 'campaigns.id', '=', 'leads.campaign_id')
            ->join('lead_logs', 'lead_logs.id', '=', 'leads.lead_follow_up_id')
            ->where('leads.last_action_by', '=', $user->id)
            ->where(function ($query) {
                return $query->where('campaigns.status', 'started')
                    ->orWhereNull('campaigns.status');
            })
            ->whereNotNull('lead_follow_up_id');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $allAppointments = $allAppointments->whereRaw('DATE(lead_logs.date_time) >= ?', [$startDate])
                ->whereRaw('DATE(lead_logs.date_time) <= ?', [$endDate]);
        }

        $allAppointments = $allAppointments->take(5)->get();


        return $allAppointments;
    }

    public function changeThemeMode(Request $request)
    {
        $mode = $request->has('theme_mode') ? $request->theme_mode : 'light';

        session(['theme_mode' => $mode]);

        if ($mode == 'dark') {
            $company = company();
            $company->left_sidebar_theme = 'dark';
            $company->save();

            $updatedCompany = company(true);
        }

        return ApiResponse::make('Success', [
            'status' => "success",
            'themeMode' => $mode,
            'themeModee' => session()->all(),
        ]);
    }

    public function getAllTimezones()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return ApiResponse::make('Success', [
            'timezones' => $timezones,
            'date_formates' => [
                'd-m-Y' => 'DD-MM-YYYY',
                'm-d-Y' => 'MM-DD-YYYY',
                'Y-m-d' => 'YYYY-MM-DD',
                'd.m.Y' => 'DD.MM.YYYY',
                'm.d.Y' => 'MM.DD.YYYY',
                'Y.m.d' => 'YYYY.MM.DD',
                'd/m/Y' => 'DD/MM/YYYY',
                'm/d/Y' => 'MM/DD/YYYY',
                'Y/m/d' => 'YYYY/MM/DD',
                'd/M/Y' => 'DD/MMM/YYYY',
                'd.M.Y' => 'DD.MMM.YYYY',
                'd-M-Y' => 'DD-MMM-YYYY',
                'd M Y' => 'DD MMM YYYY',
                'd F, Y' => 'DD MMMM, YYYY',
                'D/M/Y' => 'ddd/MMM/YYYY',
                'D.M.Y' => 'ddd.MMM.YYYY',
                'D-M-Y' => 'ddd-MMM-YYYY',
                'D M Y' => 'ddd MMM YYYY',
                'd D M Y' => 'DD ddd MMM YYYY',
                'D d M Y' => 'ddd DD MMM YYYY',
                'dS M Y' => 'Do MMM YYYY',
            ],
            'time_formates' => [
                "hh:mm A" => '12 Hours hh:mm A',
                'hh:mm a' => '12 Hours hh:mm a',
                'hh:mm:ss A' => '12 Hours hh:mm:ss A',
                'hh:mm:ss a' => '12 Hours hh:mm:ss a',
                'HH:mm ' => '24 Hours HH:mm:ss',
                'HH:mm:ss' => '24 Hours hh:mm:ss',
            ]
        ]);
    }
}
