<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    function subcategories(Category $category){
        // $categories = Category::pluck('name','id');
        return view('categories.subcategories', compact('category'));
    }
}
