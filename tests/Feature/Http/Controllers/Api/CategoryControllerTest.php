<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();

        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json('POST', route('categories.store', []));
        $this->assertInvalidationRequired($response);

        
        $response = $this->json('POST', route('categories.store', [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]));        
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route( 'categories.update', ['category' => $category->id]),
                [
                    'name' => str_repeat('a', 256),
                    'is_active' => 'a'
                ]
            );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

    }

    protected function assertInvalidationRequired(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']) //Verifica se está presente
            ->assertJsonMissingValidationErrors(['is_active']) //Validando campo que não é obrigatório
            ->assertJsonFragment([
                \Illuminate\Support\Facades\Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Illuminate\Support\Facades\Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Illuminate\Support\Facades\Lang::get('validation.boolean', ['attribute' => 'is active']) //Atenção ao fragmento da mensagem, a mensagem será geranda sem "_" do atributo.
            ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('categories.store', [
            'name' => 'Teste'
        ]));

        $id = $response->json('id');
        $category = Category::find($id);
        
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue((bool)$response->json('is_active'));
        $this->assertNull($response->json('description'));
        
        $response = $this->json('POST', route('categories.store', [
            'name' => 'Teste',
            'description' => 'Description',
            'is_active' => false
        ]));

        $response->assertJsonFragment([
            'description' => 'Description',
            'is_active' => false
        ]);

    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'Description',
            'is_active' => false
        ]);
        $response = $this->json('PUT', route( 'categories.update', ['category' => $category->id]), [
            'name' => 'Teste',
            'description' => 'Description test',
            'is_active' => true
        ]);

        $id = $response->json('id');
        $category = Category::find($id);
        
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description' => 'Description test',
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route( 'categories.update', ['category' => $category->id]),
            [
                'name' => 'Teste',
                'description' => '',
            ]);

        $response->assertJsonFragment([
                'description' => null
            ]);
    }

    public function testDestroy()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $category->id]));
        $response->assertStatus(204);
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }
}
