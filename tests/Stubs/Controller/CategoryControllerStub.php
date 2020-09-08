<?php

namespace Tests\Stubs\Controller;

use App\Http\Controllers\BasicCrudController;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{
    protected function model() {
        return CategoryStub::class;
    }

    protected function rulesStore()
    {        
        return [
            'name' => 'required|max:255',
            'description' => 'nullable'
        ];
    }
<<<<<<< HEAD
=======

    protected function rulesUpdate()
    {        
        return [
            'name' => 'required|max:255',
            'description' => 'nullable'
        ];
    }
>>>>>>> fcc71328817e032523043451b42e1170186bbd28
}
