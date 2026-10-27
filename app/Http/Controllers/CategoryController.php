<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::orderBy('name')->get(['id', 'slug', 'name']);
    }
}
