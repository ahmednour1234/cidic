<?php

namespace Tests\Feature;

use Tests\TestCase;

class CvPanelLocaleTest extends TestCase
{
    public function test_switching_language_changes_the_login_page(): void
    {
        foreach (['en' => 'ltr', 'fil' => 'ltr', 'si' => 'ltr', 'ar' => 'rtl'] as $locale => $dir) {
            $this->from('/cv-panel/login')
                ->post("/cv-panel/locale/{$locale}")
                ->assertRedirect('/cv-panel/login');

            $this->get('/cv-panel/login')
                ->assertOk()
                ->assertSee('lang="'.$locale.'"', false)
                ->assertSee('dir="'.$dir.'"', false)
                ->assertSee(__('cvp-auth.login.submit', [], $locale));
        }
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->post('/cv-panel/locale/xx');
    }
}
