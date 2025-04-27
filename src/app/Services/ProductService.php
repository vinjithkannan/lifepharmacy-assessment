<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepository $productRepository;
    protected FileUploadService $uploadService;

    public function __construct(ProductRepository $productRepository, FileUploadService $uploadService)
    {
        $this->productRepository = $productRepository;
        $this->uploadService = $uploadService;
    }

    public function listProducts(Request $request)
    {
        $query = $this->productRepository->query();

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.name', 'like', '%'.$request->category.'%');
            });
        }

        $cacheKey = 'products.page.' . $request->input('page', 1) . '.' . md5($request->fullUrl());

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request, $query) {
            $products = $this->productRepository->paginate($query, $request->input('per_page', 10));
            return ProductResource::collection($products);
        });
    }

    public function createProduct($validated): Product
    {
        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $product = $this->productRepository->create($validated);
        $this->updateImages($validated, $product->id);
        $this->syncCategories($product, $validated);

        return $product;
    }

    public function getProduct(int $id): ProductResource
    {
        $product = $this->productRepository->findById($id);
        return new ProductResource($product);
    }

    public function updateProduct(array $product, $request): Product
    {
        if (!isset($product['slug'])) {
            $product['slug'] = Str::slug($product['name']);
        }

        $productObj = $this->productRepository->update($product, $request->id);
        $this->updateImages($product, $request->id);
        $this->syncCategories($productObj, $product);

        return $productObj;
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    private function updateImages(array $productData, int $productId)
    {
        if (isset($productData['images'])) {
            $product = $this->productRepository->findById($productId);
            $fileUrls = $this->uploadService->uploadFile($productData['images']);
            $product->images()->createMany($fileUrls);
        }
    }

    private function syncCategories(Product $product, array $categories)
    {
        if (!empty($categories['categories'])) {
            $product->categories()->sync($categories['categories']);
        }
    }
}
