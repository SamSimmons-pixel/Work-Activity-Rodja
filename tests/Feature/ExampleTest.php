<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root route redirects unauthenticated user to login.
     */
    public function test_the_application_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
