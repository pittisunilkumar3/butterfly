<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationParticipant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConversationSeeder extends Seeder
{
    public function run()
    {
        // Get or create sample users
        $john = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
            ]
        );

        $jane = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => bcrypt('password'),
            ]
        );

        $mike = User::firstOrCreate(
            ['email' => 'mike@example.com'],
            [
                'name' => 'Mike Johnson',
                'password' => bcrypt('password'),
            ]
        );

        $users = collect([$john, $jane, $mike]);

        // Create a group conversation
        $groupConversation = Conversation::create([
            'xid' => Str::uuid(),
            'name' => 'Project Discussion',
            'type' => 'group',
            'description' => 'Team discussion about the new features',
            'created_by' => $john->id,
        ]);

        // Add participants to group
        foreach ($users as $user) {
            ConversationParticipant::create([
                'xid' => Str::uuid(),
                'conversation_id' => $groupConversation->id,
                'user_id' => $user->id,
                'role' => $user->id === $john->id ? 'admin' : 'member',
            ]);
        }

        // Add some messages to group conversation
        $messages = [
            [
                'content' => 'Hey team, how is everyone doing?',
                'sender_id' => $john->id,
            ],
            [
                'content' => 'Going great! Making progress on the new features.',
                'sender_id' => $jane->id,
            ],
            [
                'content' => 'I have some questions about the design.',
                'sender_id' => $mike->id,
            ],
            [
                'content' => 'Sure, what would you like to know?',
                'sender_id' => $john->id,
            ],
        ];

        foreach ($messages as $message) {
            ConversationMessage::create([
                'xid' => Str::uuid(),
                'conversation_id' => $groupConversation->id,
                'sender_id' => $message['sender_id'],
                'content' => $message['content'],
                'type' => 'text',
                'created_at' => now(),
            ]);
        }

        // Create direct conversations
        $otherUsers = [$jane, $mike];
        foreach ($otherUsers as $otherUser) {
            $directConversation = Conversation::create([
                'xid' => Str::uuid(),
                'type' => 'direct',
                'created_by' => $john->id,
            ]);

            // Add participants
            ConversationParticipant::create([
                'xid' => Str::uuid(),
                'conversation_id' => $directConversation->id,
                'user_id' => $john->id,
            ]);

            ConversationParticipant::create([
                'xid' => Str::uuid(),
                'conversation_id' => $directConversation->id,
                'user_id' => $otherUser->id,
            ]);

            // Add some direct messages
            ConversationMessage::create([
                'xid' => Str::uuid(),
                'conversation_id' => $directConversation->id,
                'sender_id' => $john->id,
                'content' => "Hi {$otherUser->name}, how are you?",
                'type' => 'text',
                'created_at' => now()->subHours(2),
            ]);

            ConversationMessage::create([
                'xid' => Str::uuid(),
                'conversation_id' => $directConversation->id,
                'sender_id' => $otherUser->id,
                'content' => "I'm doing well, thanks! How about you?",
                'type' => 'text',
                'created_at' => now()->subHour(),
            ]);
        }
    }
}
