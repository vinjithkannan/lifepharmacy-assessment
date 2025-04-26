<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['categories']);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.name', 'like', '%'.$request->category.'%');
            });
        }
        $cacheKey = 'products.page.' . $request->input('page', 1) . '.' . md5($request->fullUrl());

        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($request, $query) {
            $products = $query->paginate($request->input('per_page', 10));

            return ProductResource::collection($products);
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required', 'price' => 'required|numeric'
        ]);
        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function show(int $id)
    {
        $product = Product::with(['categories', 'images', 'productOrders'])->findOrFail($id);
        return new ProductResource($product);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required', 'price' => 'required|numeric'
        ]);
        $product = Product::update($validated);

        return response()->json($product, 201);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'deleted'], 201);
    }

}
