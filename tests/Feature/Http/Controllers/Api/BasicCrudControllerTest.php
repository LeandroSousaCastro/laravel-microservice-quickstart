    <?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\BasicCrudController;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controller\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;

class BasicCrudControllerTest extends TestCase
{
    private $controller;

    protected  function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /** @var CategoryStub */
        $category = CategoryStub::create([
            'name' => 'Teste',
            'description' => 'Teste description'
        ]);
        $result = $this->controller->index()->toArray();
        $this->assertEquals([$category->toArray()], $result);
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        //Mockery PHP
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        //Mockery PHP
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'Teste', 'description' => 'Test Description']);
        $obj = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray()
        );
    }

    //Testando metodos protegidos - ReflectionClass acessa e altera as propriedades das classes.
    public function testIfFindOrFailFetchModel()
    {
        /** @var CategoryStub */
        $category = CategoryStub::create(['name' => 'Teste', 'description' => 'Description teste']);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);

        $this->assertInstanceOf(CategoryStub::class, $result);

    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [0]);

        $this->assertInstanceOf(CategoryStub::class, $result);

    }

    public function testShow()
    {
        $category = CategoryStub::create(['name' => 'Teste', 'description' => 'Description teste']);
        $result = $this->controller->show($category->id);
        $this->assertEquals($result->toArray(), CategoryStub::find(1)->toArray());
    }

    // public function testUpdate()
    // {
    //     $category = CategoryStub::create(['name' => 'Teste', 'description' => 'Description teste']);

    //     $request = \Mockery::mock(Request::class);
    //     $request
    //         ->shouldReceive('all')
    //         ->once()
    //         ->andReturn(['name' => 'Teste Changed', 'description' => 'Test Changed']);
        
    //     $obj = $this->controller->update($request, $category->id);
    //     $this->assertEquals(
    //         CategoryStub::find(1)->toArray(),
    //         $obj->toArray()
    //     );
    // }

    public function testDestroy()
    {
        $category = CategoryStub::create(['name' => 'Teste', 'description' => 'Description teste']);
        $response = $this->controller->destroy($category->id);
        $this
            ->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }
}
