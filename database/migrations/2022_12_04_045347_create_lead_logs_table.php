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
        Schema::create('lead_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('lead_id')->unsigned()->nullable()->default(null);
            $table->foreign('lead_id')->references('id')->on('leads')->onUpdate('cascade')->onDelete('cascade');
            $table->string('log_type', 20)->default('call_log');  // ['call_log', 'notes', 'lead_follow_up', 'eamil', 'salesman_booking', 'sms']
            $table->bigInteger('user_id')->unsigned()->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('started_on')->nullable()->default(null);
            $table->unsignedInteger('time_taken')->nullable()->default(null);
            $table->longText('notes')->nullable()->default(null);
            $table->dateTime('date_time');
            $table->string('booking_status')->nullable()->default(null);  // ['actioned', 'changed', 'deleted']
            $table->bigInteger('created_by_id')->unsigned()->nullable()->default(null);
            $table->foreign('created_by_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('updated_by_id')->unsigned()->nullable()->default(null);
            $table->foreign('updated_by_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('lead_logs');
    }
};
