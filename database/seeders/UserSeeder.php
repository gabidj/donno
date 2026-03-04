<?php

namespace Database\Seeders;

use App\Models\DonationBox;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 3 users with 5 donation boxes each
        User::factory(3)
            ->has(DonationBox::factory(5))
            ->create();

        // 1 user with no donation boxes
        User::factory()->create([
            'name' => 'No Boxes User',
            'email' => 'noboxes@example.com',
        ]);

        // 1 user with exactly 1 donation box
        User::factory()
            ->has(DonationBox::factory(1))
            ->create([
                'name' => 'Single Box User',
                'email' => 'singlebox@example.com',
            ]);
    }
}
