<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{

    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,null'
    ];

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /** @var Video $obj */
        $obj = DB::transaction(function () use ($request, $validateData, $self) {
            $obj = $this->model()::create($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });

        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj = DB::transaction(function () use ($obj, $validateData, $request, $self) {
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        return $obj;
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
}
