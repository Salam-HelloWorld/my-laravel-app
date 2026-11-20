<?php

namespace Tests\Feature;

use App\Support\ItemStore;
use Tests\TestCase;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(ItemStore::class)->clear();
    }

    public function test_health(): void
    {
        $this->getJson('/health')->assertOk()->assertExactJson(['status' => 'ok']);
    }

    public function test_config_never_leaks_the_secret(): void
    {
        config(['app.api_secret' => 'super-secret-value']);

        $response = $this->getJson('/config')->assertOk()->assertJson(['api_secret_configured' => true]);
        $this->assertStringNotContainsString('super-secret-value', $response->getContent());
    }

    public function test_crud_flow(): void
    {
        $item = ['id' => 1, 'name' => 'Book', 'description' => null, 'price' => 12.5, 'in_stock' => true];

        $this->postJson('/items', ['name' => 'Book', 'price' => 12.5])->assertCreated()->assertExactJson($item);
        $this->getJson('/items')->assertOk()->assertExactJson([$item]);
        $this->getJson('/items/1')->assertOk()->assertExactJson($item);

        $this->patchJson('/items/1', ['price' => 10, 'in_stock' => false])
            ->assertOk()
            ->assertJson(['name' => 'Book', 'price' => 10, 'in_stock' => false]);

        $this->deleteJson('/items/1')->assertNoContent();
        $this->getJson('/items/1')->assertNotFound();
    }

    public function test_not_found(): void
    {
        $this->getJson('/items/99')->assertNotFound();
        $this->patchJson('/items/99', ['price' => 1])->assertNotFound();
        $this->deleteJson('/items/99')->assertNotFound();
    }

    public function test_validation(): void
    {
        $this->postJson('/items', ['name' => '', 'price' => 1])->assertUnprocessable();
        $this->postJson('/items', ['name' => 'X', 'price' => -1])->assertUnprocessable();
    }

    public function test_docs_available(): void
    {
        $this->get('/docs')->assertOk();
        $this->getJson('/openapi.json')->assertOk()->assertJsonPath('paths./items.get.summary', 'List items');
    }
}
