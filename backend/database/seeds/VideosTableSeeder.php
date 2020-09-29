<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class VideosTableSeeder extends Seeder
{

    protected $allGenres;
    protected $relations = [
        'genres_id' => [],
        'categories_id' => [],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = Genre::all();
        Model::reguard(); //mass assigment
        factory(\App\Models\Video::class, 10)
            ->make()
            ->each(function(Video $video) use ($self) {
                $self->fetchRelations();
                \App\Models\Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'video_file' => $self->getVideoFile(),
                            'trailer_file' =>  $self->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
    }

    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }

    public function getImageFile()
    {
        return new UploadedFile(
            storage_path('fake/thumbs/laravel.PNG'),
            'laravel.PNG'
        );
    }

    public function getVideoFile()
    {
        return new UploadedFile(
            storage_path('fake/videos/01-teste.mp4'),
            '01-teste.mp4'
        );
    }
}
