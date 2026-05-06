<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class InstallerSeeder extends Seeder
{
    /**
     * Seed only essential roles — no demo data.
     * The admin user is created via the installer wizard form.
     */
    public function run(): void
    {
        // Create roles only if they don't already exist
        if (Role::where('name', 'Admin')->count() === 0) {
            Role::create(['name' => 'Admin']);
        }

        if (Role::where('name', 'Staff')->count() === 0) {
            Role::create(['name' => 'Staff']);
        }
    }
}
