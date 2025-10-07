<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleRoleGroupSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing mappings first
        DB::table('module_role_groups')->truncate();

        $mappings = [
            // ============================================
            // SUPPLY CHAIN MANAGEMENT (SCM) - Module ID: 2
            // ============================================
            [
                'module_id' => 2,
                'role_id' => 1, // Manager
                'azure_group_id' => '0c5a368f-3c3a-46dd-ab6d-5ca6d9edfabd',
                'azure_group_name' => 'SCM_PowerUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 2, // Officer
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 3, // Admin
                'azure_group_id' => '0c5a368f-3c3a-46dd-ab6d-5ca6d9edfabd',
                'azure_group_name' => 'SCM_PowerUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 4, // User
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 5, // Analyst
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 6, // Coordinator
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 7, // Consultant
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 2,
                'role_id' => 8, // Reviewer
                'azure_group_id' => '7c9ca1a8-fa16-47c6-a889-38faf5f1d829',
                'azure_group_name' => 'SCM_BasicUser',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // OFFICE MANAGEMENT (Odoo) - Module ID: 1
            // ============================================
            [
                'module_id' => 1,
                'role_id' => 1, // Manager
                'azure_group_id' => '7f70ba1b-2bb6-4c70-83c9-d836623b5317',
                'azure_group_name' => 'Odoo_Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 2, // Officer
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 3, // Admin
                'azure_group_id' => '7f70ba1b-2bb6-4c70-83c9-d836623b5317',
                'azure_group_name' => 'Odoo_Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 4, // User
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 5, // Analyst
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 6, // Coordinator
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 7, // Consultant
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 1,
                'role_id' => 8, // Reviewer
                'azure_group_id' => 'b4acd816-78c8-4310-a9b1-fca0f5ea2018',
                'azure_group_name' => 'Odoo_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // BUSINESS INTELLIGENCE (BIZ) - Module ID: 3
            // Using HR groups temporarily (until Amay provides proper groups)
            // ============================================
            [
                'module_id' => 3,
                'role_id' => 1, // Manager
                'azure_group_id' => 'b7c16b40-fb3d-43cd-a9d1-03509ec5debd',
                'azure_group_name' => 'HR_Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 2, // Officer
                'azure_group_id' => '7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8',
                'azure_group_name' => 'HR_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 3, // Admin
                'azure_group_id' => 'b7c16b40-fb3d-43cd-a9d1-03509ec5debd',
                'azure_group_name' => 'HR_Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 4, // User
                'azure_group_id' => '7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8',
                'azure_group_name' => 'HR_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 5, // Analyst
                'azure_group_id' => 'b7c16b40-fb3d-43cd-a9d1-03509ec5debd',
                'azure_group_name' => 'HR_Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 6, // Coordinator
                'azure_group_id' => '7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8',
                'azure_group_name' => 'HR_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 7, // Consultant
                'azure_group_id' => '7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8',
                'azure_group_name' => 'HR_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 3,
                'role_id' => 8, // Reviewer
                'azure_group_id' => '7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8',
                'azure_group_name' => 'HR_Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // FITGAP ANALYSIS - Module ID: 4
            // Using Finance groups temporarily (until Amay provides proper groups)
            // ============================================
            [
                'module_id' => 4,
                'role_id' => 1, // Manager
                'azure_group_id' => '205314fa-dc3e-4364-9d99-005df27ec2ca',
                'azure_group_name' => 'Finance_Editor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 2, // Officer
                'azure_group_id' => '4cfae354-25f3-4da8-9826-312bf8b7bb02',
                'azure_group_name' => 'Finance_Viewer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 3, // Admin
                'azure_group_id' => '205314fa-dc3e-4364-9d99-005df27ec2ca',
                'azure_group_name' => 'Finance_Editor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 4, // User
                'azure_group_id' => '4cfae354-25f3-4da8-9826-312bf8b7bb02',
                'azure_group_name' => 'Finance_Viewer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 5, // Analyst
                'azure_group_id' => '205314fa-dc3e-4364-9d99-005df27ec2ca',
                'azure_group_name' => 'Finance_Editor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 6, // Coordinator
                'azure_group_id' => '4cfae354-25f3-4da8-9826-312bf8b7bb02',
                'azure_group_name' => 'Finance_Viewer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 7, // Consultant
                'azure_group_id' => '4cfae354-25f3-4da8-9826-312bf8b7bb02',
                'azure_group_name' => 'Finance_Viewer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => 4,
                'role_id' => 8, // Reviewer
                'azure_group_id' => '205314fa-dc3e-4364-9d99-005df27ec2ca',
                'azure_group_name' => 'Finance_Editor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('module_role_groups')->insert($mappings);

        $this->command->info('✅ Successfully seeded ' . count($mappings) . ' module-role-group mappings!');
        $this->command->info('📊 Breakdown:');
        $this->command->info('   - SCM: 8 role mappings');
        $this->command->info('   - Office Management: 8 role mappings');
        $this->command->info('   - Business Intelligence: 8 role mappings (temporary HR groups)');
        $this->command->info('   - FITGAP Analysis: 8 role mappings (temporary Finance groups)');
    }
}