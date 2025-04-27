<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function listOrders(User $user)
    {
        return $this->orderRepository->getUserOrders($user);
    }

    public function createOrder(User $user, Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = $this->orderRepository->createOrder($user, ['total' => 0]);
        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = $this->orderRepository->findProduct($item['product_id']);

            if ($product && $product->quantity >= $item['quantity']) {
                $this->orderRepository->addItemToOrder($order, [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->quantity -= $item['quantity'];
                $this->orderRepository->updateProduct($product, ['quantity' => $product->quantity]);

                $total += $product->price * $item['quantity'];
            }
        }

        $this->orderRepository->updateOrder($order, ['total' => $total]);

        return $order->load('items.product');
    }
}
