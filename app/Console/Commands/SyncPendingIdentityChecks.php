<?php

namespace App\Console\Commands;

use App\Models\IdentityVerification;
use App\Services\IdentityVerificationService;
use Illuminate\Console\Command;

/**
 * Safety net for the Shufti callback: asks Shufti for the verdict of every
 * check still pending after a few minutes. Checks with no verdict after
 * 24 h are marked failed so the app asks the user to verify again.
 * Manual-review attempts are left to the admin.
 */
class SyncPendingIdentityChecks extends Command
{
    protected $signature = 'identity:sync-pending';

    protected $description = 'Resolve Shufti identity checks that are still pending';

    public function handle(IdentityVerificationService $identity): int
    {
        $pending = IdentityVerification::where('status', IdentityVerification::PENDING)
            ->where('source', '!=', IdentityVerificationService::SOURCE_MANUAL)
            ->where('created_at', '<', now()->subMinutes(5))
            ->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($pending as $attempt) {
            $identity->resolve($attempt);
            $this->line("{$attempt->reference}: {$attempt->status}");
        }
        $this->info($pending->count() . ' pending check(s) processed.');
        return self::SUCCESS;
    }
}
