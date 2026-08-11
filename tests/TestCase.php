<?php

namespace Tests;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\CandidateCategory;
use App\Models\Nationality;
use App\Models\User;
use App\Support\ArabicSlug;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * Pin the test database to in-memory SQLite.
     *
     * phpunit.xml's <env> entries do NOT override variables that already exist in
     * the shell, so running the suite from a session that exported DB_DATABASE
     * would let RefreshDatabase truncate that real database. Forcing the config
     * here removes any dependence on the ambient environment.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // Applied here, before RefreshDatabase touches the schema in setUp().
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.url', null);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstDestroyingRealData();
    }

    /** Abort loudly rather than truncate a file-backed or remote database. */
    protected function guardAgainstDestroyingRealData(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            $this->fail(
                "Refusing to run tests against '{$connection}' database '{$database}'. "
                . 'The suite must use in-memory SQLite.'
            );
        }
    }

    protected function createAdmin(UserRole $role = UserRole::SuperAdmin): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function createNationality(array $attributes = []): Nationality
    {
        return Nationality::create(array_merge([
            'name_ar' => 'الفلبين',
            'name_en' => 'Philippines',
            'slug' => 'philippines-' . uniqid(),
            'is_active' => true,
        ], $attributes));
    }

    protected function createCategory(array $attributes = []): CandidateCategory
    {
        return CandidateCategory::create(array_merge([
            'name_ar' => 'مربية أطفال',
            'name_en' => 'Nanny',
            'slug' => 'nanny-' . uniqid(),
            'is_active' => true,
        ], $attributes));
    }

    /**
     * A candidate with sane defaults; the reference/slug mirror what
     * CandidateService produces so URLs behave like production.
     */
    protected function createCandidate(array $attributes = []): Candidate
    {
        $nationality = $attributes['nationality_id'] ?? $this->createNationality()->id;
        $category = $attributes['candidate_category_id'] ?? $this->createCategory()->id;

        $candidate = Candidate::create(array_merge([
            'reference_number' => 'TMP-' . uniqid(),
            'name' => 'سارة',
            'slug' => 'tmp-' . uniqid(),
            'nationality_id' => $nationality,
            'candidate_category_id' => $category,
            'age' => 28,
            'gender' => 'female',
            'profession' => 'مربية أطفال',
            'years_of_experience' => 4,
            'language_arabic' => 'good',
            'language_english' => 'basic',
            'availability_status' => AvailabilityStatus::Available,
            'is_active' => true,
        ], $attributes));

        if (str_starts_with((string) $candidate->reference_number, 'TMP-')) {
            $reference = 'CV-' . str_pad((string) $candidate->id, 5, '0', STR_PAD_LEFT);

            $candidate->forceFill([
                'reference_number' => $reference,
                'slug' => ArabicSlug::forCandidate($reference, $candidate->name),
            ])->save();
        }

        return $candidate->refresh();
    }
}
