<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return response()->json($categories);
    }

    public function show(Category $category)
    {
        $category->load('products', 'products.category');
        return response()->json($category);
    }

    public function showBySlug($slug)
    {
        $category = Category::where('slug', $slug)
            ->with(['products' => function($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        return response()->json($category);
    }

    public function navbar()
    {
        $categories = Category::navbarCategories()->get();
        return response()->json($categories);
    }
}
