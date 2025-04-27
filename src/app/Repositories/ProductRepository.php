<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function query()
    {
        return Product::with(['categories']);
    }

    public function paginate($query, int $perPage = 10)
    {
        return $query->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function findById(int $id): Product
    {
        return Product::with(['categories', 'images', 'productOrders'])->findOrFail($id);
    }

    public function update(array $data, int $id): Product
    {
        $product = $this->findById($id);
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
