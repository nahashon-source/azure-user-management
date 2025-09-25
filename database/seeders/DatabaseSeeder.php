<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Locations first
        $this->seedLocations();
        
        // Create Companies
        $this->seedCompanies();
        
        // Create Modules
        $this->seedModules();
        
        // Create Roles
        $this->seedRoles();
        
        // Create Module-Role relationships
        $this->seedModuleRoles();
        
        // Create Test Users
        $this->seedUsers();
    }
    
   private function seedLocations()
{
    $locations = [
        [
            'name' => 'Kenya',
            'code' => 'KEN',
            'country' => 'Kenya',
            'timezone' => 'Africa/Nairobi',
            'is_active' => true,
        ],
        [
            'name' => 'Uganda',
            'code' => 'UGA',
            'country' => 'Uganda',
            'timezone' => 'Africa/Kampala',
            'is_active' => true,
        ],
    ];

    foreach ($locations as $location) {
        Location::firstOrCreate(['code' => $location['code']], $location);
    }

    $this->command->info('Locations seeded successfully');
}

    private function seedCompanies()
    {
        $companies = [
            ['name' => 'BeiGhton Kenya Ltd', 'location' => 'kenya'],
            ['name' => 'BeiGhton Uganda Ltd', 'location' => 'uganda'],
            ['name' => 'East Africa Holdings', 'location' => 'kenya'],
            ['name' => 'Uganda Branch Office', 'location' => 'uganda'],
        ];
        
        foreach ($companies as $company) {
            Company::firstOrCreate(['name' => $company['name']], $company);
        }
        
        $this->command->info('Companies seeded successfully');
    }
    
    private function seedModules()
    {
        $modules = [
            [
                'name' => 'Office Management',
                'code' => 'office',
                'description' => 'General office management and administration'
            ],
            [
                'name' => 'Supply Chain Management',
                'code' => 'scm',
                'description' => 'Supply chain and inventory management'
            ],
            [
                'name' => 'Business Intelligence',
                'code' => 'biz',
                'description' => 'Business intelligence and reporting'
            ],
            [
                'name' => 'FITGAP Analysis',
                'code' => 'fitgap',
                'description' => 'Fit-gap analysis and process optimization'
            ],
        ];
        
        foreach ($modules as $module) {
            Module::firstOrCreate(['code' => $module['code']], $module);
        }
        
        $this->command->info('Modules seeded successfully');
    }
    
    private function seedRoles()
    {
        $roles = [
            ['name' => 'Manager', 'description' => 'Full management access'],
            ['name' => 'Officer', 'description' => 'Operational access'],
            ['name' => 'Admin', 'description' => 'Administrative access'],
            ['name' => 'User', 'description' => 'Basic user access'],
            ['name' => 'Analyst', 'description' => 'Data analysis access'],
            ['name' => 'Coordinator', 'description' => 'Project coordination access'],
            ['name' => 'Consultant', 'description' => 'Consulting access'],
            ['name' => 'Reviewer', 'description' => 'Review and approval access'],
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
        
        $this->command->info('Roles seeded successfully');
    }
    
    private function seedModuleRoles()
    {
        $modules = Module::all();
        $roles = Role::all();
        
        // Attach all roles to all modules (you can customize this based on business logic)
        foreach ($modules as $module) {
            $module->roles()->sync($roles->pluck('id'));
        }
        
        $this->command->info('Module-Role relationships seeded successfully');
    }
    
    private function seedUsers()
    {
        $companies = Company::all();
        
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@beighton.com',
                'employee_id' => 'EMP001',
                'phone' => '+254700000001',
                'location' => 'kenya',
                'company_id' => $companies->where('location', 'kenya')->first()->id,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'modules' => ['office' => 'Manager', 'scm' => 'Officer']
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@beighton.com',
                'employee_id' => 'EMP002',
                'phone' => '+254700000002',
                'location' => 'kenya',
                'company_id' => $companies->where('location', 'kenya')->first()->id,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'modules' => ['biz' => 'Analyst', 'fitgap' => 'Consultant']
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@beighton.com',
                'employee_id' => 'EMP003',
                'phone' => '+256700000003',
                'location' => 'uganda',
                'company_id' => $companies->where('location', 'uganda')->first()->id,
                'status' => 'pending',
                'password' => Hash::make('password123'),
                'modules' => ['office' => 'Admin', 'scm' => 'User']
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@beighton.com',
                'employee_id' => 'EMP004',
                'phone' => '+254700000004',
                'location' => 'kenya',
                'company_id' => $companies->where('location', 'kenya')->skip(1)->first()->id,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'modules' => ['biz' => 'Manager', 'office' => 'Coordinator']
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@beighton.com',
                'employee_id' => 'EMP005',
                'phone' => '+256700000005',
                'location' => 'uganda',
                'company_id' => $companies->where('location', 'uganda')->first()->id,
                'status' => 'inactive',
                'password' => Hash::make('password123'),
                'modules' => ['fitgap' => 'Reviewer']
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@beighton.com',
                'employee_id' => 'EMP006',
                'phone' => '+254700000006',
                'location' => 'kenya',
                'company_id' => $companies->where('location', 'kenya')->first()->id,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'modules' => ['scm' => 'Manager', 'biz' => 'Officer', 'office' => 'User']
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.wilson@beighton.com',
                'employee_id' => 'EMP007',
                'phone' => '+256700000007',
                'location' => 'uganda',
                'company_id' => $companies->where('location', 'uganda')->skip(1)->first()->id,
                'status' => 'pending',
                'password' => Hash::make('password123'),
                'modules' => ['office' => 'Officer', 'fitgap' => 'Analyst']
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@beighton.com',
                'employee_id' => 'EMP008',
                'phone' => '+254700000008',
                'location' => 'kenya',
                'company_id' => $companies->where('location', 'kenya')->first()->id,
                'status' => 'active',
                'password' => Hash::make('password123'),
                'modules' => ['biz' => 'Manager', 'fitgap' => 'Consultant', 'scm' => 'Coordinator']
            ]
        ];
        
        foreach ($users as $userData) {
            // Extract modules data before creating user
            $userModules = $userData['modules'];
            unset($userData['modules']);
            
            // Create user
            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            
            // Assign modules and roles
            foreach ($userModules as $moduleCode => $roleName) {
                $module = Module::where('code', $moduleCode)->first();
                $role = Role::where('name', $roleName)->first();
                
                if ($module && $role) {
                    // Check if already attached to avoid duplicates
                    if (!$user->modules()->where('module_id', $module->id)->exists()) {
                        $user->modules()->attach($module->id, [
                            'role_id' => $role->id,
                            'assigned_at' => now()
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('Test users seeded successfully');
        $this->command->info('Created ' . User::count() . ' users with module assignments');
    }
}