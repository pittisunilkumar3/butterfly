<?php

namespace App\Providers;


use App\Models\Company;
use App\Models\Currency;
use App\Models\Role;
use App\Models\Campaign;
use App\Models\Settings;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\Form;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Lead;
use App\Models\FormFieldName;
use App\Models\LeadLog;
use App\Observers\CurrencyObserver;
use App\Observers\RoleObserver;
use App\Observers\LeadObserver;
use App\Observers\CampaignObserver;
use App\Observers\EmailTemplateObserver;
use App\Observers\FormObserver;
use App\Observers\FormFieldNameObserver;
use App\Observers\ExpenseCategoryObserver;
use App\Observers\ExpenseObserver;
use App\Observers\ProductObserver;
use App\Observers\LeadLogObserver;
use App\Observers\SettingObserver;
use App\Observers\UserObserver;
use App\SuperAdmin\Models\SuperAdmin;
use App\SuperAdmin\Observers\SuperAdminObserver;
use App\SuperAdmin\Observers\CompanyObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Don't run observer when
        // we run command using
        if (!app()->runningInConsole()) {
            $appType = app_type();

            User::observe(UserObserver::class);
            Settings::observe(SettingObserver::class);
            Currency::observe(CurrencyObserver::class);
            Role::observe(RoleObserver::class);
            Campaign::observe(CampaignObserver::class);
            EmailTemplate::observe(EmailTemplateObserver::class);
            Form::observe(FormObserver::class);
            Expense::observe(ExpenseObserver::class);
            ExpenseCategory::observe(ExpenseCategoryObserver::class);
            Product::observe(ProductObserver::class);
            FormFieldName::observe(FormFieldNameObserver::class);
            Lead::observe(LeadObserver::class);
            LeadLog::observe(LeadLogObserver::class);

            if ($appType == 'saas') {
                Company::observe(CompanyObserver::class);
                SuperAdmin::observe(SuperAdminObserver::class);
            }
        }
    }
}
