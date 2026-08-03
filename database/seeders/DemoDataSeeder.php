<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        User::factory()
            ->count(10)
            ->has(Task::factory()->count(3))
            ->create();
    }
}
