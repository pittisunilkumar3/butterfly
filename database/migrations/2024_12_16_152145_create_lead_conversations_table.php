<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('message_sid')->unique();
            $table->string('direction')->default('inbound'); // inbound or outbound
            $table->text('message');
            $table->string('status')->default('sent');
            $table->string('from');
            $table->string('to');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['lead_id', 'created_at']);
            $table->index('message_sid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_conversations');
    }
};
