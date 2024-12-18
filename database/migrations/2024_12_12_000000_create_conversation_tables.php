<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('xid')->unique();
            $table->string('name')->nullable();
            $table->string('type')->default('direct'); // direct, group
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Conversation participants table
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->uuid('xid')->unique();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('member'); // admin, member
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('conversations')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['conversation_id', 'user_id']);
        });

        // Messages table
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('xid')->unique();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('content');
            $table->string('type')->default('text'); // text, image, file, system
            $table->json('metadata')->nullable(); // For additional message data (file info, etc.)
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('conversations')
                ->onDelete('cascade');

            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        // Message reactions table
        Schema::create('conversation_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->string('reaction'); // emoji or reaction type
            $table->timestamps();

            $table->foreign('message_id')
                ->references('id')
                ->on('conversation_messages')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Using a shorter name for the unique index
            $table->unique(['message_id', 'user_id', 'reaction'], 'conv_msg_reaction_unique');
        });

        // Message attachments table
        Schema::create('conversation_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('xid')->unique();
            $table->unsignedBigInteger('message_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('message_id')
                ->references('id')
                ->on('conversation_messages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_attachments');
        Schema::dropIfExists('conversation_message_reactions');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
}
