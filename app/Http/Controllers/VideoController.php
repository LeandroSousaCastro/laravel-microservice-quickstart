<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Auto commit - Padrão de banco de dados relacionais
 * Modo transação
 * 
 * - begin - Marca inicio da transação
 * - transactions - executa todas as transações pertinentes
 * - commit - persite as transações no banco
 * - rollback - desfaz todas as transações do checkpoint
 */
class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id',
            'genres_id' => 'required|array|exists:genres,id'
        ];
    }

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
        DB::transaction(function () use ($obj, $validateData, $request, $self) {
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
        });
        return $obj;
    }

    protected function handleRelations(Video $video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    }

    protected function model()
    {
        return Video::class;
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