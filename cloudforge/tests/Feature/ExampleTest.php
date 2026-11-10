<?php

namespace Tests\Feature;

use Database\Seeders\CatalogoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** La portada responde y muestra el catálogo cargado. */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(CatalogoSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CloudForge');
    }
}