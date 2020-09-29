<?php

namespace Tests\Feature\Models\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Stubs\Models\UploadFileStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    protected $obj;

    public function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFileStub();
        UploadFileStub::dropTable();
        UploadFileStub::makeTable();
    }

    public function testMakeOldFieldsOnSaving()
    {
        $this->obj->fill([
            'name' => 'Test',
            'file1'=> 'test1.mp4',
            'file2'=> 'test2.mp4'
        ]);
        $this->obj->save();

        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
            'name' => 'Test name',
            'file2'=> 'test3.mp4',
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->obj->oldFiles);

    }

    public function testMakeOldFieldsNullOnSaving()
    {
        $this->obj->fill([
            'name' => 'Test'
        ]);
        $this->obj->save();

        $this->obj->update([
            'name' => 'Test name',
            'file2'=> 'test3.mp4',
        ]);
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
