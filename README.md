🎯 Key Cross-Platform Features:
1. Dual Command Structure

Linux: mkdir azure-user-management && cd azure-user-management
Windows: New-Item -ItemType Directory -Name "azure-user-management"; Set-Location azure-user-management

2. Database Strategy

Development: Use MySQL on both platforms (easy setup)
Production: Use SQL Server on Windows (as per your manager's requirements)
Compatibility: Laravel's database abstraction handles differences

3. Environment Files Strategy

.env.linux - for Linux development
.env.windows - for Windows development
.env.production - for Windows production with SQL Server

4. Platform-Specific Database Setup
🐧 Linux (Current):
bash# Use MySQL for development
sudo apt-get install -y mysql-server php-mysql
sudo mysql -u root -p
CREATE DATABASE azure_user_management;
🪟 Windows (Future):
powershell# Use XAMPP for development, SQL Server for production
# XAMPP: Download from apachefriends.org
# SQL Server: Use manager's provided queries
✅ For Your Current Linux Setup:
Run these commands to continue:
bash# 1. Install MySQL (development database)
sudo apt-get update
sudo apt-get install -y mysql-server php-mysql

# 2. Create database
sudo mysql -u root -p
In MySQL prompt:
sqlCREATE DATABASE azure_user_management;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password123';
GRANT ALL PRIVILEGES ON azure_user_management.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
bash# 3. Update your .envRetryClaude does not have the ability to run the code it generates yet.MContinueEditbash# 3. Update your .env file
Edit your .env file with these settings:
envDB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azure_user_management
DB_USERNAME=laravel_user
DB_PASSWORD=secure_password123
bash# 4. Test the database connection
php artisan config:clear
php artisan migrate:status

# 5. Continue with the setup
php artisan key:generate
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run dev
🚀 Ready-to-Run Commands for Your Current Linux Setup:
bash# Complete setup sequence for Linux:
cd ~/Desktop/azure-user-management

# Install MySQL if not already installed
sudo apt-get install -y mysql-server php-mysql

# Create database
sudo mysql -u root -p -e "
CREATE DATABASE azure_user_management;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password123';
GRANT ALL PRIVILEGES ON azure_user_management.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;"

# Update Laravel configuration
composer require doctrine/dbal laravel/breeze --dev

# Install Breeze authentication
php artisan breeze:install blade
npm install && npm run dev

# Generate app key
php artisan key:generate

# Test database connection
php artisan migrate:status
📝 Update Your .env File Now:
Replace the SQL Server settings in your .env with:
envAPP_NAME="Azure User Management System"
APP_ENV=local
APP_KEY=base64:your-existing-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# MySQL Configuration for Development
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azure_user_management
DB_USERNAME=laravel_user
DB_PASSWORD=secure_password123

# Azure AD Configuration (for later)
AZURE_CLIENT_ID=your-azure-client-id
AZURE_CLIENT_SECRET=your-azure-client-secret
AZURE_TENANT_ID=your-azure-tenant-id
AZURE_REDIRECT_URI=http://localhost:8000/auth/azure/callback

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
✅ Expected Results After Setup:

Database Connection Test:

bashphp artisan migrate:status
# Should show: "Migration table not found." (this is good!)

Start Development Server:

bashphp artisan serve
# Should show: "Laravel development server started: http://127.0.0.1:8000"
🔄 For Future Windows Migration:
When you move to Windows with your manager's SQL Server:

Copy your Laravel project
Update .env file with SQL Server credentials:

envDB_CONNECTION=sqlsrv
DB_HOST=your-manager-provided-host
DB_PORT=1433
DB_DATABASE=your-manager-provided-db
DB_USERNAME=your-manager-provided-user
DB_PASSWORD=your-manager-provided-password

Run the same Laravel commands - they work identically on Windows
Use manager's queries for database setup

🎯 Next Steps After Database Connection Works:

✅ Create all controllers and models
✅ Build the UI from your wireframes
✅ Create migrations (they'll work on both MySQL and SQL Server)
✅ Implement the user management features

Try the MySQL setup commands above and let me know if php artisan migrate:status works!
Once that's working, we can proceed with creating all the controllers, models, and views based on your wireframes.











# Role-Based Azure Group Assignment System

## 📋 Overview

This system enables **dynamic Azure AD group assignment based on user roles**. Instead of assigning all users to a single hardcoded group per module, users are now assigned to different groups based on their role within that module.

### Example: Supply Chain Management (SCM)
- **Manager** → Assigned to `SCM_PowerUser` group
- **Officer** → Assigned to `SCM_BasicUser` group
- **Admin** → Assigned to `SCM_PowerUser` group
- **User** → Assigned to `SCM_BasicUser` group

---

## 🏗️ System Architecture

### Database Structure

#### **`module_role_groups` Table**
Stores the mapping between modules, roles, and Azure groups.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `module_id` | bigint | Foreign key to `modules` table |
| `role_id` | bigint | Foreign key to `roles` table |
| `azure_group_id` | varchar(255) | Azure AD Security Group UUID |
| `azure_group_name` | varchar(255) | Human-readable group name |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Last update time |

**Constraints:**
- Unique combination of (`module_id`, `role_id`)
- Each module-role pair can only have ONE Azure group

---

## 📊 Current Mappings

### Supply Chain Management (Module ID: 2)

| Role | Azure Group | Group ID |
|------|-------------|----------|
| Manager | SCM_PowerUser | `0c5a368f-3c3a-46dd-ab6d-5ca6d9edfabd` |
| Officer | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |
| Admin | SCM_PowerUser | `0c5a368f-3c3a-46dd-ab6d-5ca6d9edfabd` |
| User | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |
| Analyst | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |
| Coordinator | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |
| Consultant | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |
| Reviewer | SCM_BasicUser | `7c9ca1a8-fa16-47c6-a889-38faf5f1d829` |

### Office Management (Module ID: 1)

| Role | Azure Group | Group ID |
|------|-------------|----------|
| Manager | Odoo_Manager | `7f70ba1b-2bb6-4c70-83c9-d836623b5317` |
| Officer | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |
| Admin | Odoo_Manager | `7f70ba1b-2bb6-4c70-83c9-d836623b5317` |
| User | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |
| Analyst | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |
| Coordinator | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |
| Consultant | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |
| Reviewer | Odoo_Employee | `b4acd816-78c8-4310-a9b1-fca0f5ea2018` |

### Business Intelligence (Module ID: 3) - TEMPORARY MAPPING
> ⚠️ **Note:** Currently using HR groups as placeholders. Update when proper BIZ groups are available.

| Role | Azure Group (Temporary) | Group ID |
|------|------------------------|----------|
| Manager | HR_Manager | `b7c16b40-fb3d-43cd-a9d1-03509ec5debd` |
| Analyst | HR_Manager | `b7c16b40-fb3d-43cd-a9d1-03509ec5debd` |
| Others | HR_Employee | `7bb64aa1-87a7-4f2b-bbc2-ee7852ed01b8` |

### FITGAP Analysis (Module ID: 4) - TEMPORARY MAPPING
> ⚠️ **Note:** Currently using Finance groups as placeholders. Update when proper FITGAP groups are available.

| Role | Azure Group (Temporary) | Group ID |
|------|------------------------|----------|
| Manager | Finance_Editor | `205314fa-dc3e-4364-9d99-005df27ec2ca` |
| Admin | Finance_Editor | `205314fa-dc3e-4364-9d99-005df27ec2ca` |
| Analyst | Finance_Editor | `205314fa-dc3e-4364-9d99-005df27ec2ca` |
| Others | Finance_Viewer | `4cfae354-25f3-4da8-9826-312bf8b7bb02` |

---

## 🔧 How to Add/Update Mappings

### Option 1: Using Tinker (Quick Updates)

```php
php artisan tinker

// Example: Update BIZ module when Amay provides correct groups
DB::table('module_role_groups')
    ->where('module_id', 3)  // BIZ module
    ->where('role_id', 1)    // Manager role
    ->update([
        'azure_group_id' => 'NEW_AZURE_GROUP_UUID',
        'azure_group_name' => 'BIZ_PowerUser'
    ]);
```

### Option 2: Using SQL (Bulk Updates)

```sql
-- Update all BIZ mappings at once
UPDATE module_role_groups 
SET 
    azure_group_id = 'new-group-uuid-here',
    azure_group_name = 'BIZ_PowerUser'
WHERE 
    module_id = 3 
    AND role_id IN (1, 3, 5);  -- Manager, Admin, Analyst
```

### Option 3: Add New Module Mappings

```php
php artisan tinker

// Add mappings for a new module
$mappings = [
    ['module_id' => 5, 'role_id' => 1, 'azure_group_id' => 'uuid-1', 'azure_group_name' => 'NewModule_Admin'],
    ['module_id' => 5, 'role_id' => 2, 'azure_group_id' => 'uuid-2', 'azure_group_name' => 'NewModule_User'],
];

DB::table('module_role_groups')->insert($mappings);
```

---

## 🔍 How to Verify Mappings

### Check Current Mappings for a Module

```php
php artisan tinker

// View SCM mappings
DB::table('module_role_groups')
    ->where('module_id', 2)
    ->join('roles', 'module_role_groups.role_id', '=', 'roles.id')
    ->select('roles.name as role_name', 'azure_group_name', 'azure_group_id')
    ->get();
```

### Test Role-Based Group Lookup

```php
php artisan tinker

// Test what group a specific role gets
$module = Module::find(2);  // SCM
$groupInfo = $module->getAzureGroupForRole(2);  // Officer role
print_r($groupInfo);

// Should return:
// Array (
//     [group_id] => 7c9ca1a8-fa16-47c6-a889-38faf5f1d829
//     [group_name] => SCM_BasicUser
// )
```

---

## 📝 Code Implementation

### Key Files Modified

1. **Migration:** `database/migrations/YYYY_MM_DD_create_module_role_groups_table.php`
2. **Model:** `app/Models/ModuleRoleGroup.php`
3. **Seeder:** `database/seeders/ModuleRoleGroupSeeder.php`
4. **Module Model:** `app/Models/Module.php` (added `getAzureGroupForRole()`)
5. **Assignment Service:** `app/Services/ModuleAssignmentService.php`

### New Methods in Module Model

```php
// Get Azure group for a specific role
public function getAzureGroupForRole(int $roleId): ?array
{
    $mapping = ModuleRoleGroup::where('module_id', $this->id)
        ->where('role_id', $roleId)
        ->first();
    
    return $mapping ? [
        'group_id' => $mapping->azure_group_id,
        'group_name' => $mapping->azure_group_name,
    ] : null;
}

// Check if role mapping exists
public function hasRoleMapping(int $roleId): bool
{
    return ModuleRoleGroup::where('module_id', $this->id)
        ->where('role_id', $roleId)
        ->exists();
}
```

---

## 🧪 Testing

### Test User Assignment

```php
php artisan tinker

$assignmentService = app(\App\Services\ModuleAssignmentService::class);
$user = User::find(30);
$module = Module::find(2);  // SCM
$roleId = 2;  // Officer

$result = $assignmentService->assignUserToModule($user, $module, $roleId);
print_r($result);
```

### Check Logs

```bash
# View Azure assignment logs
tail -f storage/logs/azure.log

# Look for entries like:
# [2025-10-07 21:00:02] Attempting Azure group assignment
# {"user_id":30,"module":"Supply Chain Management","role_id":2,
#  "azure_group_id":"7c9ca1a8...","azure_group_name":"SCM_BasicUser"}
```

---

## ⚠️ Important Notes for Amay

### When Adding New Modules

1. **Create Azure Groups first** in Azure AD:
   - At minimum: `ModuleName_PowerUser` and `ModuleName_BasicUser`
   - Note down the Group UUIDs

2. **Add mappings** to `module_role_groups` table:
   ```sql
   INSERT INTO module_role_groups (module_id, role_id, azure_group_id, azure_group_name) 
   VALUES 
       (5, 1, 'power-user-uuid', 'NewModule_PowerUser'),
       (5, 2, 'basic-user-uuid', 'NewModule_BasicUser');
   ```

3. **No code changes required!** The system automatically uses the mappings.

### When Updating BIZ/FITGAP Groups

Once you have the correct Azure groups for BIZ and FITGAP:

```sql
-- Update BIZ mappings (Module ID: 3)
UPDATE module_role_groups 
SET 
    azure_group_id = 'your-biz-poweruser-uuid',
    azure_group_name = 'BIZ_PowerUser'
WHERE module_id = 3 AND role_id IN (1, 3, 5);

UPDATE module_role_groups 
SET 
    azure_group_id = 'your-biz-basicuser-uuid',
    azure_group_name = 'BIZ_BasicUser'
WHERE module_id = 3 AND role_id IN (2, 4, 6, 7, 8);

-- Repeat for FITGAP (Module ID: 4)
```

---

## 🐛 Troubleshooting

### Issue: User assigned to wrong group

**Check the mapping:**
```php
php artisan tinker
$module = Module::find(2);
$groupInfo = $module->getAzureGroupForRole(YOUR_ROLE_ID);
print_r($groupInfo);
```

**Fix:** Update the mapping in database.

### Issue: "No Azure group mapping found"

**Cause:** Missing entry in `module_role_groups` table.

**Fix:** Add the mapping:
```sql
INSERT INTO module_role_groups (module_id, role_id, azure_group_id, azure_group_name)
VALUES (2, 5, 'your-group-uuid', 'YourGroupName');
```

### Issue: Assignment shows "already member" error

**Cause:** User is already in the group (expected behavior).

**Status:** This is actually a success! The system tried to add them but Azure said they're already there.

---

## 📊 Database Queries for Reporting

### View All Mappings

```sql
SELECT 
    m.name AS module_name,
    r.name AS role_name,
    mrg.azure_group_name,
    mrg.azure_group_id
FROM module_role_groups mrg
JOIN modules m ON mrg.module_id = m.id
JOIN roles r ON mrg.role_id = r.id
ORDER BY m.name, r.name;
```

### Find Users with Specific Module-Role Combination

```sql
SELECT 
    u.name AS user_name,
    u.email,
    m.name AS module_name,
    r.name AS role_name,
    mrg.azure_group_name
FROM user_modules um
JOIN users u ON um.user_id = u.id
JOIN modules m ON um.module_id = m.id
JOIN roles r ON um.role_id = r.id
JOIN module_role_groups mrg ON mrg.module_id = um.module_id AND mrg.role_id = um.role_id
WHERE m.id = 2;  -- SCM module
```

### Count Users per Group

```sql
SELECT 
    m.name AS module_name,
    mrg.azure_group_name,
    COUNT(DISTINCT um.user_id) AS user_count
FROM module_role_groups mrg
JOIN user_modules um ON mrg.module_id = um.module_id AND mrg.role_id = um.role_id
JOIN modules m ON mrg.module_id = m.id
GROUP BY m.name, mrg.azure_group_name
ORDER BY m.name, user_count DESC;
```

---

## 🎯 Benefits of This System

1. **Flexible:** Add new modules/groups without code changes
2. **Role-Based:** Different permissions based on user role
3. **Maintainable:** All mappings in one table
4. **Auditable:** Logs show exactly which group users are assigned to
5. **Scalable:** Easy to add more roles or modules

---

## 📞 Support

For questions or issues:
- Check logs: `storage/logs/azure.log`
- Review mappings: `SELECT * FROM module_role_groups;`
- Test assignments using the examples above

---

**Last Updated:** October 7, 2025  
**Version:** 1.0  
**Author:** Development Team
























Cross-Platform Setup Instructions
Step 1: Project Initialization
🐧 Linux Commands:
bash# Create project directory
mkdir azure-user-management
cd azure-user-management

# Install Laravel
composer create-project laravel/laravel .
🪟 Windows PowerShell Commands:
powershell# Create project directory
New-Item -ItemType Directory -Name "azure-user-management"
Set-Location azure-user-management

# Install Laravel
composer create-project laravel/laravel .

Step 2: Cross-Platform Database Configuration
Environment Configuration (.env) - BOTH PLATFORMS
envAPP_NAME="Azure User Management System"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ===== DEVELOPMENT DATABASE (MySQL) =====
# Use this for development on both Linux and Windows
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azure_user_management
DB_USERNAME=root
DB_PASSWORD=

# ===== PRODUCTION DATABASE (SQL Server) =====
# Comment out during development, uncomment for Windows production
# DB_CONNECTION=sqlsrv
# DB_HOST=your-windows-sql-server
# DB_PORT=1433
# DB_DATABASE=azure_user_management_prod
# DB_USERNAME=sa
# DB_PASSWORD=your-strong-password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Azure AD Configuration (same for both platforms)
AZURE_CLIENT_ID=your-azure-client-id
AZURE_CLIENT_SECRET=your-azure-client-secret
AZURE_TENANT_ID=your-azure-tenant-id
AZURE_REDIRECT_URI=http://localhost:8000/auth/azure/callback
🐧 Linux Database Setup (Development):
bash# Install MySQL
sudo apt-get update
sudo apt-get install -y mysql-server php-mysql

# Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# Create database
sudo mysql -u root -p
🪟 Windows Database Setup (Development):
powershell# Option 1: Install XAMPP (Recommended for development)
# Download XAMPP from https://www.apachefriends.org/
# After installation, start MySQL from XAMPP Control Panel

# Option 2: Install MySQL Standalone
# Download MySQL installer from https://dev.mysql.com/downloads/installer/
# Follow installation wizard

# Create database using phpMyAdmin (XAMPP) or MySQL Workbench
# Or use command line:
# mysql -u root -p
🪟 Windows Production (SQL Server):
powershell# Install SQL Server PHP extensions (via Composer)
composer require doctrine/dbal

# For SQL Server Native Client (if using actual SQL Server)
# Download from: https://www.microsoft.com/en-us/download/details.aspx?id=50402
# Follow installation instructions

Step 3: Cross-Platform Package Installation
🐧 Linux:
bash# Install required packages
composer require doctrine/dbal
composer require laravel/breeze --dev

# Install Node.js dependencies
npm install
🪟 Windows PowerShell:
powershell# Install required packages (same commands work)
composer require doctrine/dbal
composer require laravel/breeze --dev

# Install Node.js dependencies (same command works)
npm install

Step 4: Create Controllers (BOTH PLATFORMS - Same Commands)
bash# Main controllers (works on both Linux and Windows)
php artisan make:controller UserManagementController
php artisan make:controller DashboardController
php artisan make:controller Api/UserController --api
php artisan make:controller Api/ModuleController --api
php artisan make:controller Api/RoleController --api
php artisan make:controller Auth/AzureAuthController
php artisan make:controller ProcedureController
php artisan make:controller ReportController

Step 5: Create Models (BOTH PLATFORMS - Same Commands)
bash# Core models (works on both platforms)
php artisan make:model User -m
php artisan make:model Module -m
php artisan make:model Role -m
php artisan make:model UserModule -m
php artisan make:model Company -m
php artisan make:model Location -m
php artisan make:model Procedure -m

# Pivot/Junction models
php artisan make:model UserRole -m
php artisan make:model ModuleRole -m

Step 6: Create Directory Structure
🐧 Linux Commands:
bash# Create view directories
mkdir -p resources/views/layouts
mkdir -p resources/views/dashboard
mkdir -p resources/views/users
mkdir -p resources/views/modules
mkdir -p resources/views/reports
mkdir -p resources/views/components
mkdir -p resources/views/partials

# Create service directories
mkdir -p app/Services
mkdir -p app/Http/Requests

# Create view files
touch resources/views/layouts/app.blade.php
touch resources/views/layouts/guest.blade.php
touch resources/views/dashboard/index.blade.php
touch resources/views/users/index.blade.php
touch resources/views/users/create.blade.php
touch resources/views/users/edit.blade.php
touch resources/views/users/show.blade.php
🪟 Windows PowerShell Commands:
powershell# Create view directories
New-Item -ItemType Directory -Path "resources\views\layouts" -Force
New-Item -ItemType Directory -Path "resources\views\dashboard" -Force
New-Item -ItemType Directory -Path "resources\views\users" -Force
New-Item -ItemType Directory -Path "resources\views\modules" -Force
New-Item -ItemType Directory -Path "resources\views\reports" -Force
New-Item -ItemType Directory -Path "resources\views\components" -Force
New-Item -ItemType Directory -Path "resources\views\partials" -Force

# Create service directories
New-Item -ItemType Directory -Path "app\Services" -Force
New-Item -ItemType Directory -Path "app\Http\Requests" -Force

# Create view files
New-Item -ItemType File -Path "resources\views\layouts\app.blade.php" -Force
New-Item -ItemType File -Path "resources\views\layouts\guest.blade.php" -Force
New-Item -ItemType File -Path "resources\views\dashboard\index.blade.php" -Force
New-Item -ItemType File -Path "resources\views\users\index.blade.php" -Force
New-Item -ItemType File -Path "resources\views\users\create.blade.php" -Force
New-Item -ItemType File -Path "resources\views\users\edit.blade.php" -Force
New-Item -ItemType File -Path "resources\views\users\show.blade.php" -Force
bash# Install SQL Server support
composer require doctrine/dbal

# Install additional packages for SQL Server
composer require illuminate/database
Configure Environment (.env)
envAPP_NAME="Azure User Management System"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# SQL Server Configuration
DB_CONNECTION=sqlsrv
DB_HOST=your-sql-server-host
DB_PORT=1433
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Additional SQL Server options
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Azure AD Configuration (for future use)
AZURE_CLIENT_ID=your-azure-client-id
AZURE_CLIENT_SECRET=your-azure-client-secret
AZURE_TENANT_ID=your-azure-tenant-id
AZURE_REDIRECT_URI=http://localhost:8000/auth/azure/callback
Update Database Configuration (config/database.php)
bash# Add this to your database config if not present
# You may need to manually add SQL Server configuration

Step 3: Create Controllers
bash# Main controllers
php artisan make:controller UserManagementController
php artisan make:controller DashboardController
php artisan make:controller Api/UserController --api
php artisan make:controller Api/ModuleController --api
php artisan make:controller Api/RoleController --api
php artisan make:controller Auth/AzureAuthController

# Additional utility controllers
php artisan make:controller ProcedureController
php artisan make:controller ReportController

Step 4: Create Models
bash# Core models
php artisan make:model User -m
php artisan make:model Module -m
php artisan make:model Role -m
php artisan make:model UserModule -m
php artisan make:model Company -m
php artisan make:model Location -m
php artisan make:model Procedure -m

# Pivot/Junction models
php artisan make:model UserRole -m
php artisan make:model ModuleRole -m

Step 5: Create Migrations (Database Structure)
bash# User-related migrations
php artisan make:migration create_users_table --create=users
php artisan make:migration create_companies_table --create=companies
php artisan make:migration create_locations_table --create=locations

# Module and Role migrations
php artisan make:migration create_modules_table --create=modules
php artisan make:migration create_roles_table --create=roles
php artisan make:migration create_module_roles_table --create=module_roles

# Junction tables
php artisan make:migration create_user_modules_table --create=user_modules
php artisan make:migration create_user_roles_table --create=user_roles

# System tables
php artisan make:migration create_procedures_table --create=procedures
php artisan make:migration create_audit_logs_table --create=audit_logs

# Additional user fields migration
php artisan make:migration add_azure_fields_to_users_table --table=users

Step 6: Create Seeders
bash# Create seeders for initial data
php artisan make:seeder CompanySeeder
php artisan make:seeder LocationSeeder
php artisan make:seeder ModuleSeeder
php artisan make:seeder RoleSeeder
php artisan make:seeder ProcedureSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder DatabaseSeeder

# Create factories for testing
php artisan make:factory UserFactory --model=User
php artisan make:factory ModuleFactory --model=Module
php artisan make:factory RoleFactory --model=Role

Step 7: Install Frontend Dependencies
bash# Install Node.js dependencies
npm install

# Install additional frontend packages
npm install bootstrap@5.3.0 @popperjs/core
npm install axios
npm install sweetalert2
npm install chart.js

# Install Laravel UI or Breeze (choose one)
# Option 1: Laravel Breeze (recommended for API)
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run dev

# Option 2: Laravel UI (if you prefer)
# composer require laravel/ui
# php artisan ui bootstrap --auth

Step 8: Create Routes
bash# Routes will be manually created in routes/web.php and routes/api.php
# No artisan command needed, but we'll structure them properly

Step 9: Create Views Structure
bash# Create directory structure for views
mkdir -p resources/views/layouts
mkdir -p resources/views/dashboard
mkdir -p resources/views/users
mkdir -p resources/views/modules
mkdir -p resources/views/reports
mkdir -p resources/views/components
mkdir -p resources/views/auth
mkdir -p resources/views/partials

# Create individual view files (will be done manually)
touch resources/views/layouts/app.blade.php
touch resources/views/layouts/guest.blade.php
touch resources/views/dashboard/index.blade.php
touch resources/views/users/index.blade.php
touch resources/views/users/create.blade.php
touch resources/views/users/edit.blade.php
touch resources/views/users/show.blade.php

Step 10: Configure Middleware and Services
bash# Create custom middleware
php artisan make:middleware CheckAzureAuth
php artisan make:middleware LogUserActivity

# Create service classes
php artisan make:command CreateUserService
mkdir -p app/Services
touch app/Services/AzureService.php
touch app/Services/UserProvisioningService.php
touch app/Services/ModuleAssignmentService.php

Step 11: Create Form Requests (Validation)
bash# Create form request classes for validation
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
php artisan make:request AssignModuleRequest
php artisan make:request StoreModuleRequest

Step 12: Database Operations
bash# Generate application key
php artisan key:generate

# Test database connection
php artisan migrate:status

# Run migrations (only after database is ready)
# php artisan migrate

# Seed database (only after migrations)
# php artisan db:seed

Step 13: Asset Compilation
bash# Compile assets for development
npm run dev

# For production
# npm run build

# Watch for changes during development
# npm run watch

Step 14: Testing Setup
bash# Create test files
php artisan make:test UserManagementTest
php artisan make:test ModuleAssignmentTest --unit
php artisan make:test DashboardTest

# Create test database
php artisan make:test DatabaseTest

Step 15: Start Development Server
bash# Start the development server
php artisan serve

# Alternative with specific host/port
# php artisan serve --host=0.0.0.0 --port=8000

Project Structure Overview
azure-user-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── UserManagementController.php
│   │   │   ├── DashboardController.php
│   │   │   └── Api/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Module.php
│   │   ├── Role.php
│   │   └── ...
│   └── Services/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── dashboard/
│   │   ├── users/
│   │   └── ...
│   ├── js/
│   └── css/
├── routes/
│   ├── web.php
│   └── api.php
├── public/
├── storage/
└── tests/

Quick Start Commands Summary
bash# 1. Create project
mkdir azure-user-management && cd azure-user-management
composer create-project laravel/laravel .

# 2. Install dependencies
composer require doctrine/dbal
composer require laravel/breeze --dev

# 3. Setup authentication
php artisan breeze:install blade
npm install && npm run dev

# 4. Generate key
php artisan key:generate

# 5. Create all controllers
php artisan make:controller UserManagementController
php artisan make:controller DashboardController
php artisan make:controller Api/UserController --api

# 6. Create all models
php artisan make:model User -m
php artisan make:model Module -m
php artisan make:model Role -m

# 7. Start server
php artisan serve

Next Steps After Database Setup

Configure .env with actual SQL Server credentials
Run migrations when database is ready
Create seeders for initial data
Test connections to Azure AD APIs
Implement user interface based on wireframes
Add API integrations for external systems

