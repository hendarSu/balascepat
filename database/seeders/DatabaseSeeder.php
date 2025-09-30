<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\NotificationChannel;
use Illuminate\Support\Facades\Crypt;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);

        // Seed N8N channel with provided credentials
        $baseUrl = env('N8N_BASE_URL', 'http://localhost:5678');
        NotificationChannel::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'n8n'],
            [
                'name' => 'N8N',
                'base_url' => $baseUrl,
                'auth_type' => 'header',
                'headers_key' => 'X-N8N-API-Key',
                'headers_value' => null,
                'n8n_username' => 'hender.dev@gmail.com',
                'n8n_password' => Crypt::encryptString('-'),
            ]
        );
    }
}
