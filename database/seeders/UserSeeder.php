<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'teste@desafio.com',
        ], [
            'name' => 'Gabriel',
            'password' => 'password',
        ]);
    }
}
