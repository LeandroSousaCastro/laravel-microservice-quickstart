<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $castMember;
    private $sendData;
    private $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
        $this->sendData = [
            'name' => 'Title',
            'type' => CastMember::TYPE_ACTOR,
        ];
    }

    public function testIndex()
    {

        $response = $this->get(route('cast_members.index'));
        $response
            ->assertStatus(200)->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => [],
            ])
            ->assertJsonFragment($this->castMember->toArray());
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $this->assertResource(
            $response,
            new CastMemberResource(CastMember::find($response->json('data.id')))
        );
    }

    public function testInvalidationRequired()
    {
        $data = [
            'name' => '',
            'type' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testSave()
    {

        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData,
            ],
            [
                'send_data' => $this->sendData + ['type' => CastMember::TYPE_DIRECTOR],
                'test_data' => $this->sendData + ['type' => CastMember::TYPE_DIRECTOR],
            ]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'data' => ['created_at', 'updated_at']
            ]);

            $this->assertResource(
                $response,
                new CastMemberResource(CastMember::find($response->json('data.id')))
            );
        }
    }

    public function testUpdate()
    {
        $response = $this->assertUpdate($this->sendData, $this->sendData + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        $this->assertResource(
            $response,
            new CastMemberResource(CastMember::find($response->json('data.id')))
        );
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(204);
        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
