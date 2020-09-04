<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class GenreTest extends TestCase
{

    private $genre;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); //Executado apenas uma vez, usado para realizar configurações globais
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = new Genre();
    }

    protected function tearDown(): void
    {
        parent::tearDown(); //Executado após cada teste.
    }

    public function testFillableAttribute()
    {

        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genre->getFillable());

    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass(); //Executado apenas uma vez, quando todos os testes forem executaados
        //Limpar cache, banco de dados ou remover arquivos
    }

    public function testIfUseTraitsAttribute()
    {
        
        $traits = [SoftDeletes::class, Uuid::class];
        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($traits, $genreTraits);

    }

    public function testIncrementingAttribute()
    {

        $this->assertFalse($this->genre->getIncrementing());

    }

    public function testDatesAttribute()
    {
        
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->genre->getDates());
        }

        $this->assertCount(count($dates), $this->genre->getDates());

    }
}
