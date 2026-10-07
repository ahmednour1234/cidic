<?php

namespace Database\Seeders;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The real CV-panel staff.
 *
 * Safe to run on production, and safe to re-run: an existing account is
 * matched by email and left with its current password, so a second run never
 * locks anyone out.
 *
 *     php artisan db:seed --class=CvPanelStaffSeeder --force
 *
 * Each new account gets a freshly generated password, printed once when the
 * seeder runs. Nothing is stored in the repository, so the credentials never
 * reach git. Hand them over privately and have each person change their own
 * password after the first sign-in.
 */
class CvPanelStaffSeeder extends Seeder
{
    /**
     * name, email, department.
     *
     * @var list<array{0:string, 1:string, 2:Department}>
     */
    private const STAFF = [
        ['أسامة', 'osama@cidic.online', Department::Coordination],
        ['سامي', 'sami@cidic.online', Department::Coordination],
        ['رهوف', 'rahouf@cidic.online', Department::CustomerService],
        ['خالد', 'khaled@cidic.online', Department::CustomerService],
        ['عبدالله', 'abdullah@cidic.online', Department::BranchManager],
    ];

    public function run(): void
    {
        $branch = Branch::firstOrCreate(['name' => 'الفرع الرئيسي'], ['is_active' => true]);

        // Coordinators start with every nationality; reassign from the panel's
        // "المنسّقون والجنسيات" screen.
        $nationalityIds = Nationality::query()->pluck('id')->all();

        $created = [];
        $skipped = [];

        foreach (self::STAFF as [$name, $email, $department]) {
            $existing = User::withTrashed()->where('email', $email)->first();

            if ($existing) {
                // Re-running must not reset a password someone already changed,
                // so only the panel fields are refreshed.
                $existing->restore();
                $existing->forceFill([
                    'name' => $name,
                    'department' => $department->value,
                    'branch_id' => $existing->branch_id ?? $branch->id,
                    'is_active' => true,
                ])->save();

                $skipped[] = [$name, $email, $department->label()];
                $user = $existing;
            } else {
                $password = $this->password();

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => UserRole::Staff->value,
                    'department' => $department->value,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ]);

                $created[] = [$name, $email, $department->label(), $password];
            }

            if ($department === Department::Coordination) {
                $user->nationalities()->syncWithoutDetaching($nationalityIds);
            }
        }

        $this->report($created, $skipped);
    }

    /**
     * A readable but strong password: three random syllable-ish chunks plus
     * digits, so it can be dictated over the phone without ambiguity.
     */
    private function password(): string
    {
        // No O/0, I/l/1 - the characters people mishear or mistype.
        $alphabet = 'abcdefghjkmnpqrstuvwxyz';
        $chunks = [];

        for ($chunk = 0; $chunk < 3; $chunk++) {
            $chunks[] = collect(range(1, 4))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        }

        return implode('-', $chunks).'-'.random_int(100, 999);
    }

    /** @param list<array> $created @param list<array> $skipped */
    private function report(array $created, array $skipped): void
    {
        $command = $this->command;

        if (! $command) {
            return;
        }

        if ($created !== []) {
            $command->newLine();
            $command->info('Accounts created. These passwords are shown ONCE - copy them now.');
            $command->table(['الاسم', 'البريد', 'القسم', 'كلمة المرور'], $created);
            $command->warn('Share each password privately and ask the user to change it after signing in.');
        }

        if ($skipped !== []) {
            $command->newLine();
            $command->line('Already existed - details refreshed, password left unchanged:');
            $command->table(['الاسم', 'البريد', 'القسم'], $skipped);
        }

        $command->newLine();
        $command->line('Sign in at: '.url('/cv-panel/login'));
    }
}
