<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->longText('detail_fields')->nullable()->default(null);
            $table->boolean('allow_reference_prefix')->default(false);
            $table->string('reference_prefix', 20)->nullable()->default(null);
            $table->integer('total_leads')->default(0);
            $table->integer('remaining_leads')->default(0);

            $table->string('status', 20)->default('not_started'); // started,not_started,completed
            $table->dateTime('started_on')->nullable()->default(null);
            $table->dateTime('completed_on')->nullable()->default(null);
            $table->bigInteger('completed_by')->unsigned()->nullable()->default(null);
            $table->foreign('completed_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');

            $table->bigInteger('form_id')->unsigned()->nullable()->default(null);
            $table->foreign('form_id')->references('id')->on('forms')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('email_template_id')->unsigned()->nullable()->default(null);
            $table->foreign('email_template_id')->references('id')->on('email_templates')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('created_by')->unsigned()->nullable()->default(null);
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('updated_by')->unsigned()->nullable()->default(null);
            $table->foreign('updated_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('last_action_by')->unsigned()->nullable()->default(null);
            $table->foreign('last_action_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('campaign_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('campaign_id')->unsigned()->nullable()->default(null);
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_users');
        Schema::dropIfExists('campaigns');
    }
};
