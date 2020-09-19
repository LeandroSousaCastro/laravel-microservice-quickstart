<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\VideoController;
use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Traits\UploadFiles;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Mockery;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Tests\Exceptions\TestException;
use Tests\Traits\TestResources;
use Tests\Traits\TestUploads;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestUploads, TestResources;

    private $video;
    private $sendData;
    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file_url',
        'trailer_file_url',
        'thumb_file_url',
        'banner_file_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $this->sendData = [
            'title' => 'Title',
            'description' => 'Description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => [],
            ]);

        $resource = VideoResource::collection(collect([$this->video]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJsonFragment($this->video->toArray());

        $resource = new VideoResource($this->video);
        $this->assertResource($response, $resource);
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationCategoriesId()
    {
        $data = [
            'categories_id' => 'a' //Validando se é um array
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
            'year_launched' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 's',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0,
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes',
            ['values' => 'video/mp4']
        );
    }

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes',
            ['values' => 'video/mp4']
        );
    }

    public function testSaveWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );

        $response->assertStatus(201);
        $this->assertFileOnPersists($response, $files);
        $video = Video::find($response->json('data.id'));
        $this->assertIfFilesUrlExists($video, $response);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );
        $response->assertStatus(200);
        $this->assertFileOnPersists($response, $files);

        $newFiles = [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
        ];
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles
        );
        $response->assertStatus(200);
        $this->assertFileOnPersists(
            $response,
            Arr::except($files, ['video_file', 'thumb_file']) + $newFiles
        );

        $id = $response->json('data.id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        $this->assertIfFilesUrlExists($video, $response);
    }

    public function testSave()
    {
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $testData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
            ]
        ];

        foreach ($data as $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'data' => ['created_at', 'updated_at']
            ]);
            $this->assertResource(
                $response,
                new VideoResource(Video::find($response->json('data.id')))
            );

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource(
                $response,
                new VideoResource(Video::find($response->json('data.id')))
            );
        }
    }

    protected function assertFileOnPersists(TestResponse $response, $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create("video_file.mp4"),
            'trailer_file' => UploadedFile::fake()->create("trailer_file.mp4"),
            'thumb_file' => UploadedFile::fake()->create("thumb_file.jpg"),
            'banner_file' => UploadedFile::fake()->create("banner_file.jpg"),
        ];
    }

    public function assertIfFilesUrlExists(Video $video, TestResponse $response)
    {
        $fileFields = Video::$fileFields;
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field) {
            $file = $video->{$field};
            $this->assertEquals(
                \Storage::url($video->relativeFilePath($file)),
                $data[$field . '_url']
            );
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
