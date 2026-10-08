<?php

namespace App\Console\Commands;

use App\Enums\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Issues a fresh password for CV-panel staff.
 *
 * The seeder deliberately never touches an existing account's password, so
 * this is the way to recover when the first run's output was lost.
 *
 *     php artisan cv-panel:reset-password osama@cidic.online
 *     php artisan cv-panel:reset-password --all
 */
class ResetCvPanelPassword extends Command
{
    protected $signature = 'cv-panel:reset-password
                            {email? : The account to reset}
                            {--all : Reset every panel account}
                            {--password= : Use this password instead of a generated one}';

    protected $description = 'Generate a new password for CV panel staff';

    public function handle(): int
    {
        $email = $this->argument('email');
        $all = (bool) $this->option('all');

        if (! $email && ! $all) {
            $this->error('Give an email, or pass --all.');

            return self::FAILURE;
        }

        $users = User::query()
            ->whereNotNull('department')
            ->when($email, fn ($q) => $q->where('email', $email))
            ->orderBy('name')
            ->get();

        if ($users->isEmpty()) {
            $this->error($email
                ? "No CV-panel account found for {$email}."
                : 'No CV-panel accounts found.');

            return self::FAILURE;
        }

        $rows = [];

        foreach ($users as $user) {
            $password = (string) ($this->option('password') ?: $this->password());

            $user->forceFill([
                'password' => Hash::make($password),
                // A reset is pointless if the account cannot sign in.
                'is_active' => true,
            ])->save();

            $rows[] = [
                $user->name,
                $user->email,
                Department::tryFrom((string) $user->department)?->label() ?? '—',
                $password,
            ];
        }

        $this->newLine();
        $this->info('Password reset. Shown ONCE - copy now.');
        $this->table(['الاسم', 'البريد', 'القسم', 'كلمة المرور الجديدة'], $rows);
        $this->warn('Share privately and ask each user to change it after signing in.');
        $this->newLine();
        $this->line('Sign in at: '.url('/cv-panel/login'));

        return self::SUCCESS;
    }

    /** Readable but strong; avoids characters people mishear or mistype. */
    private function password(): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz';
        $chunks = [];

        for ($chunk = 0; $chunk < 3; $chunk++) {
            $chunks[] = collect(range(1, 4))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        }

        return implode('-', $chunks).'-'.random_int(100, 999);
    }
}
