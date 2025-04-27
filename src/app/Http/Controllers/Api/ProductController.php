<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        return $this->productService->listProducts($request);
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return ( new ProductResource($product))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return (new ProductResource(['message' => $e->getMessage(), 'code' => $e->getCode()]))
                ->response()
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id)
    {
        return $this->productService->getProduct($id);
    }

    public function update(StoreProductRequest $request)
    {
        try {
            $product = $request->validated();
            $product = $this->productService->updateProduct($product, $request);

            return ( new ProductResource($product))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return (new ProductResource(['message' => $e->getMessage(), 'code' => $e->getCode()]))
            ->response()
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);
        return response()->json(['message' => 'deleted'], 200);
    }
}
