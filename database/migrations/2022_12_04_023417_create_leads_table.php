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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->string('reference_number')->nullable()->default(null);
            $table->bigInteger('campaign_id')->unsigned()->nullable()->default(null);
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onUpdate('cascade')->onDelete('cascade');
            $table->longText('lead_data')->nullable()->default(null);
            $table->boolean('started')->default(0);
            $table->string('lead_status')->nullable()->default(null); // ['interested', 'not_interested', 'unreachable']
            $table->unsignedInteger('time_taken')->default(0);
            $table->bigInteger('lead_follow_up_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('salesman_booking_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('first_action_by')->unsigned()->nullable();
            $table->foreign('first_action_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('last_action_by')->unsigned()->nullable();
            $table->foreign('last_action_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->string('lead_hash')->nullable()->default(null);
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
        Schema::dropIfExists('leads');
    }
};
