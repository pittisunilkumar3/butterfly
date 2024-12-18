<?php

use App\Models\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable()->default(NULL);
            $table->string('email')->nullable()->default(NULL);
            $table->string('phone')->nullable()->default(NULL);
            $table->string('website')->nullable()->default(NULL);
            $table->string('light_logo')->nullable()->default(NULL);
            $table->string('dark_logo')->nullable()->default(NULL);
            $table->string('small_dark_logo')->nullable()->default(NULL);
            $table->string('small_light_logo')->nullable()->default(NULL);
            $table->string('address', 1000)->nullable()->default(NULL);
            $table->string('app_layout', 10)->default('sidebar');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->bigInteger('lang_id')->unsigned()->nullable();
            $table->foreign('lang_id')->references('id')->on('langs')->onDelete('set null')->onUpdate('cascade');
            $table->string('left_sidebar_theme', 20)->default("dark");
            $table->string('primary_color', 20)->default("#1890ff");
            $table->string('date_format', 20)->default("DD-MM-YYYY");
            $table->string('time_format', 20)->default("hh:mm a");
            $table->boolean('auto_detect_timezone')->default(true);
            $table->string('timezone')->default("Asia/Kolkata");
            $table->string('session_driver', 20)->default("file");
            $table->boolean('app_debug')->default(false);
            $table->boolean('update_app_notification')->default(true);
            $table->string('login_image')->nullable()->default(NULL);
            $table->boolean('rtl')->default(false);
            $table->string('shortcut_menus', 20)->default('top_bottom');

            $table->bigInteger('subscription_plan_id')->unsigned()->nullable()->default(null);
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('set null')->onUpdate('cascade');
            $table->enum('package_type', ['monthly', 'annual'])->default('monthly');

            $table->boolean('is_global')->default(false);
            $table->bigInteger('admin_id')->unsigned()->nullable()->default(null);
            $table->string('status')->default('active');
            $table->integer('total_users')->default(1);
            $table->timestamps();
        });

        if (app_type() == 'non-saas') {
            $enLang = Lang::where('key', 'en')->first();

            DB::table('companies')->insert([
                'name' => 'Lead Pro',
                'short_name' => 'LeadPro',
                'email' => 'company@example.com',
                'phone' => '+9199999999',
                'address' => '7 street, city, state, 762782',
                'lang_id' => $enLang->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
