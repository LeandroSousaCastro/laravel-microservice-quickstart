<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{

    protected abstract function model();

    protected abstract function rulesStore();

<<<<<<< HEAD
=======
    protected abstract function rulesUpdate();

>>>>>>> fcc71328817e032523043451b42e1170186bbd28
    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        return $obj;
    }

<<<<<<< HEAD
    // public function store(Request $request)
    // {
    //     $this->validate($request, $this->rules);
    //     $category = Category::create($request->all());
    //     $category->refresh();
    //     return $category;
    // }

    // public function show(Category $category)
    // {
    //     return $category;
    // }

    // public function update(Request $request, Category $category)
    // {
    //     $this->validate($request, $this->rules);
    //     $category->update($request->all());
    //     return $category;
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();
    //     return response()->noContent();
    // }
=======
    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $obj = $this->findOrFail($id);
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->validate($request, $this->rulesUpdate());
        $obj->update($request->all());
        return $obj;
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();
    }
>>>>>>> fcc71328817e032523043451b42e1170186bbd28
}
