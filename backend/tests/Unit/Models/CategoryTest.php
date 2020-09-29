<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CategoryTest extends TestCase
{

    private $category;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); //Executado apenas uma vez, usado para realizar configurações globais
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    protected function tearDown(): void
    {
        parent::tearDown(); //Executado após cada teste.
    }

    public function testFillableAttribute()
    {

        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());

    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass(); //Executado apenas uma vez, quando todos os testes forem executaados
        //Limpar cache, banco de dados ou remover arquivos
    }

    public function testIfUseTraitsAttribute()
    {
        
        $traits = [SoftDeletes::class, Uuid::class];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);

    }

    public function testIncrementingAttribute()
    {

        $this->assertFalse($this->category->getIncrementing());

    }

    public function testDatesAttribute()
    {
        
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->category->getDates());
        }

        $this->assertCount(count($dates), $this->category->getDates());

    }
}
