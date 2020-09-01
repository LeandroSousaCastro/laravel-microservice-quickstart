<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);

        $categoriesKey = array_keys($categories->first()->getAttributes());

        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoriesKey);
    }

    public function testCreate()
    {
        $category = Category::create([
            'name' => 'Teste'
        ]);        
        $category->refresh();
        $this->assertEquals('Teste', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue((bool) $category->is_active);

        $category = Category::create([
            'name' => 'Teste',
            'description' => null
        ]);        
        $category->refresh();
        $this->assertNull($category->description);

        $category = Category::create([
            'name' => 'Teste',
            'description' => 'test description'
        ]);        
        $category->refresh();
        $this->assertEquals('test description', $category->description);

        $category = Category::create([
            'name' => 'Teste',
            'is_active' => false
        ]);        
        $category->refresh();
        $this->assertFalse((bool) $category->is_active);

    }

    public function testUpdate()
    {
        /** @var Category  $category */
        $category = factory(Category::class)->create([
            'description' => 'test description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'Name Update',
            'description' => 'test description update',
            'is_active' => true
        ];
        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }

    }
}
