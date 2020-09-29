<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\BasicCrudController;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\TestResponse;
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
        $result = $this->controller->index();
        $serialized = $result->response()->getData(true);
        $this->assertEquals(
            [$category->toArray()],
            $serialized['data']
        );
        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
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
        $result = $this->controller->store($request);
        $serialized = $result->response()->getData(true);
        $this->assertEquals(
            CategoryStub::first()->toArray(),
            $serialized['data']
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
        $serialized = $result->response()->getData(true);
        $this->assertEquals($serialized['data'], $category->toArray());
    }

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
