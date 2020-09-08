<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpParser\Node\Expr\Cast;

class Category extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $dates = ['deleted_at'];
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
