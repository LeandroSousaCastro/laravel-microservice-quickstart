<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PDOException;
use PDORow;

class Video extends Model
{
    use Uuid, SoftDeletes, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 50; //50GB
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 10; //10GB
    const THUMB_FILE_MAX_SIZE = 1024 * 5; //5MB
    const BANNER_FILE_MAX_SIZE = 1024 * 10; //10MB

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'trailer_file',
        'thumb_file',
        'banner_file',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'duration' => 'integer',
    ];

    public $incrementing = false;

    public static $fileFields = ['video_file', 'trailer_file', 'thumb_file', 'banner_file'];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            /** @var Video $video */
            $video = static::query()->create($attributes);
            static::handleRelations($video, $attributes);
            $video->uploadFiles($files);
            \DB::commit();
            return $video;
        } catch (\Throwable $e) {
            if (isset(($video))) {
                $video->deleteFiles($files);
            }
            \DB::rollback();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            \DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
            return $saved;
        } catch (\Throwable $e) {
            $this->deleteFiles($files);
            \DB::rollback();
            throw $e;
        }
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }

    public function getVideoFileUrlAttribute()
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }

    public function getTrailerFileUrlAttribute()
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute()
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }
}
