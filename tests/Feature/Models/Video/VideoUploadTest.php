<?php

namespace Tests\Feature\Models\Video;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Tests\Exceptions\TestException;

class VideoUploadTest extends BaseVideoTestCase
{
    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4'),
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function () {
            throw new \Throwable();
        });
        $hasError = false;

        try {
            Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4'),
                ]
            );
        } catch (\Throwable $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function () {
            throw new \Throwable();
        });
        $hasError = false;

        try {
            $video->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                ]
            );
        } catch (\Throwable $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    // public function testFileUrlsWithLocalDrive()
    // {
    //     $filesFields = [];
    //     foreach (Video::$fileFields as $field) {
    //         $fileFields[$field] = "$field.test";
    //     }

    //     $video = factory(Video::class)->create($fileFields);
    //     $localDrive = config('filesystems.default');
    //     $baseUrl = config('filesystems.disks.' . $localDrive)['url'];
    //     foreach ($filesFields as $field => $value) {
    //         $fileUrl = $video->{"{$field}_url"};
    //         $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
    //     }
    // }

    // public function testFileUrlsWithGcsDriver()
    // {
    //     $filesFields = [];
    //     foreach (Video::$fileFields as $field) {
    //         $fileFields[$field] = "$field.test";
    //     }

    //     $video = factory(Video::class)->create($fileFields);
    //     $baseUrl = config('filesystems.disks.gcs.storage_api_url');
    //     \Config::set('filesystems.default', 'gcs');
    //     foreach ($filesFields as $field => $value) {
    //         $fileUrl = $video->{"{$field}_url"};
    //         $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
    //     }
    // }
}
