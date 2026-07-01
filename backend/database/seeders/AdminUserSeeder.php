<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@ispbilling.local',
            'phone' => '+1234567890',
            'password' => Hash::make('password'), // Change this in production!
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id);

        $this->command->info('Super Admin user created:');
        $this->command->info('  Email: admin@ispbilling.local');
        $this->command->warn('  Password: password (CHANGE THIS IN PRODUCTION!)');

        // Create Demo Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'demo@ispbilling.local',
            'phone' => '+1234567891',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign Admin role
        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole->id);

        $this->command->info('Demo Admin user created:');
        $this->command->info('  Email: demo@ispbilling.local');
        $this->command->warn('  Password: password');

        $this->command->info('Users seeded successfully!');
    }
}
