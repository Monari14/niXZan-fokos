<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\News;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Felipe Eduardo Monari',
            'email' => 'felipeemonari@gmail.com',
            'username' => 'monari',
            'password' => Hash::make('felipe'),
        ]);

        for ($i = 1; $i <= 20; $i++) {
            News::create([
                'title' => 'Title n' . $i,
                'content' => 'content n' . $i,
                'id_user' => $user->id,
            ]);
        }
    }
}
