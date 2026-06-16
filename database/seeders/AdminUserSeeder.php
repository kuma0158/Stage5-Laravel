<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->warn('ADMIN_PASSWORD が .env に設定されていません。スキップします。');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => '管理者',
                'password' => Hash::make($password),
            ],
        );

        $this->command->info("管理者ユーザーを準備しました: {$email}");
    }
}
