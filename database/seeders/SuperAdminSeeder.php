<?php

namespace Database\Seeders;

use App\Models\Scopes\ActiveUserScope;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('quicktask.super_admin.name');
        $email = config('quicktask.super_admin.email');
        $password = config('quicktask.super_admin.password');

        if (empty($email) || empty($password)) {
            throw new \InvalidArgumentException('SuperAdminSeeder: missing email or password in config.');
        }

        if (strlen($password) < 12) {
            throw new \InvalidArgumentException('Super admin password must be at least 12 characters.');
        }

        $admin = User::withoutGlobalScope(ActiveUserScope::class)
            ->firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $admin->password = $password;
        }

        $admin->name = $name;
        $admin->role = 'admin';
        $admin->is_active = true;

        $admin->save();
    }
}
