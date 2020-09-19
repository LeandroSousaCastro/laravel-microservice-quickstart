<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{

    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id'
    ];

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = DB::transaction(function () use ($request, $validateData, $self) {
            $obj = $this->model()::create($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        $resource = $this->resource();  
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        DB::transaction(function () use ($obj, $validateData, $request, $self) {
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
        });
        $resource = $this->resource();  
        return new $resource($obj);
    }

    protected function handleRelations(Genre $genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenreResource::class;
    }
}
