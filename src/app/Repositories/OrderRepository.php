<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class OrderRepository
{
    public function getUserOrders(User $user): Collection
    {
        return $user->orders()->with('items.product')->get();
    }

    public function createOrder(User $user, array $data): Order
    {
        return $user->orders()->create($data);
    }

    public function addItemToOrder(Order $order, array $itemData)
    {
        return $order->items()->create($itemData);
    }

    public function findProduct(int $id): ?Product
    {
        return Product::find($id);
    }

    public function updateProduct(Product $product, array $data)
    {
        $product->update($data);
    }

    public function updateOrder(Order $order, array $data)
    {
        $order->update($data);
    }
}
