<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();

        $owner = User::firstOrCreate(
            ['email' => 'dev.cossi001@gmail.com'],
            [
                'name' => 'System Owner',
                'password' => Hash::make('22360010s'),
                'phone' => '0725830546',
                'is_active' => true,
            ]
        );

        if ($ownerRole) {
            $owner->roles()->syncWithoutDetaching([$ownerRole->id]);
        }

        // Create a default branch if none exists
        $branch = \App\Models\Branch::firstOrCreate(
            ['name' => 'Default Branch'],
            [
                'code' => 'DEF-001',
                'address' => 'Nairobi, Kenya',
                'phone' => $owner->phone,
                'is_active' => true,
                'is_main' => true,
                'owner_id' => $owner->id,
                'stock_distribution_percentage' => 100.00
            ]
        );
        
        if (!$owner->branch_id) {
            $owner->update(['branch_id' => $branch->id]);
        }
    }
}
