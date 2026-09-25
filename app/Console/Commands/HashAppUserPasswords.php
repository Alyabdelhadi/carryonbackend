<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * One-off migration of app_users passwords from plain text to bcrypt.
 * Idempotent: rows that already hold a hash are skipped, so it can be
 * re-run safely. Login also upgrades plain passwords as users sign in.
 */
class HashAppUserPasswords extends Command
{
    protected $signature = 'app-users:hash-passwords {--dry-run : Only count what would change}';

    protected $description = 'Hash every app user password still stored in plain text';

    public function handle(): int
    {
        $plain = 0;
        $done = 0;
        DB::table('app_users')->select(['id', 'password'])->orderBy('id')
            ->chunkById(200, function ($rows) use (&$plain, &$done) {
                foreach ($rows as $row) {
                    if ($row->password === null || $row->password === '' || AppUser::isHashed($row->password)) {
                        continue;
                    }
                    $plain++;
                    if ($this->option('dry-run')) {
                        continue;
                    }
                    // query builder, so model events and timestamps stay untouched
                    DB::table('app_users')->where('id', $row->id)
                        ->where('password', $row->password)
                        ->update(['password' => Hash::make($row->password)]);
                    $done++;
                }
            });

        $this->info($this->option('dry-run')
            ? "{$plain} plain-text password(s) would be hashed."
            : "{$done} password(s) hashed.");
        return self::SUCCESS;
    }
}
