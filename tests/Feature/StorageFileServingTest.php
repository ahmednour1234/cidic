<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The production host disables symlink() and exec(), so public/storage cannot
 * be linked. Uploaded media is served through a route instead, which must
 * still refuse to read anything outside the disk.
 */
class StorageFileServingTest extends TestCase
{
    public function test_uploaded_media_is_served(): void
    {
        $this->get('/storage/site/about.jpg')
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_missing_file_returns_404(): void
    {
        $this->get('/storage/site/does-not-exist.jpg')->assertNotFound();
    }

    public function test_directory_is_not_served(): void
    {
        $this->get('/storage/site')->assertNotFound();
    }

    /**
     * @dataProvider traversalPaths
     */
    public function test_traversal_is_refused(string $path): void
    {
        $this->get($path)->assertNotFound();
    }

    public static function traversalPaths(): array
    {
        return [
            'parent'        => ['/storage/../.env'],
            'grandparent'   => ['/storage/../../.env'],
            'encoded'       => ['/storage/%2e%2e%2f%2e%2e%2f.env'],
        ];
    }
}
