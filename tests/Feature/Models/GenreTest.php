<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);

        $genresKey = array_keys($genres->first()->getAttributes());

        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $genresKey);
    }

    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'Teste'
        ]);        
        $genre->refresh();
        $this->assertEquals('Teste', $genre->name);
        $this->assertTrue((bool) $genre->is_active);

        $genre = Genre::create([
            'name' => 'Teste',
            'is_active' => false
        ]);        
        $genre->refresh();
        $this->assertFalse((bool) $genre->is_active);

    }

    public function testUpdate()
    {
        /** @var Genre  $genre */
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'Name Update',
            'is_active' => true
        ];
        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }

    }
}
