<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserProvisioningService;

class RetryPendingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:retry-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry Azure provisioning for pending users';

    /**
     * Execute the console command.
     */
    public function handle(UserProvisioningService $provisioningService)
    {
        $this->info('Starting retry for pending users...');
        $this->newLine();
        
        $results = $provisioningService->retryPendingUsers();
        
        // Display summary
        $this->info("📊 Retry Summary:");
        $this->line("   Total pending users: {$results['total']}");
        $this->info("   ✅ Succeeded: {$results['succeeded']}");
        
        if ($results['failed'] > 0) {
            $this->error("   ❌ Failed: {$results['failed']}");
        } else {
            $this->line("   ❌ Failed: {$results['failed']}");
        }
        
        // Show detailed failures if any
        if ($results['failed'] > 0) {
            $this->newLine();
            $this->error('Failed Users Details:');
            
            foreach ($results['details'] as $detail) {
                if (isset($detail['error'])) {
                    $employeeId = $detail['employee_id'] ?? 'N/A';
                    $this->line("   - Employee ID: {$employeeId}");
                    $this->line("     Error: {$detail['error']}");
                    $this->newLine();
                }
            }
        }
        
        $this->newLine();
        $this->info('Retry process completed.');
        
        // Return exit code (0 = success, 1 = had failures)
        return $results['failed'] > 0 ? 1 : 0;
    }
}