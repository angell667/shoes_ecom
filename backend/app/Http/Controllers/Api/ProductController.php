<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        }

        $perPage = $request->per_page ?? 12;
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();
        
        return response()->json($product);
    }

    public function featured()
    {
        $products = Cache::remember('products_featured', 3600, function () {
            return Product::with('category')
                ->where('is_active', true)
                ->where('is_featured', true)
                ->limit(8)
                ->get();
        });

        return response()->json($products);
    }

    public function brands()
    {
        $brands = Cache::remember('products_brands', 3600, function () {
            return Product::where('is_active', true)
                ->distinct()
                ->pluck('brand')
                ->filter();
        });

        return response()->json($brands);
    }

    public function byCategory($categoryId)
    {
        $products = Product::with('category')
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->paginate(12);

        return response()->json($products);
    }



    public function search(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "{$search}%")
                      ->orWhere('name', 'like', "% {$search}%")
                      ->orWhere('brand', 'like', "{$search}%");
            })
            ->select('id', 'name', 'slug', 'price', 'sale_price', 'image', 'brand')
            ->limit(8)
            ->get()
            ->map(function($product) {
                $product->effective_price = $product->sale_price ?? $product->price;
                return $product;
            });

        return response()->json($products);
    }
}
