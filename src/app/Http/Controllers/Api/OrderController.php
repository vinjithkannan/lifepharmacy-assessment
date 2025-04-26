<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->orders()->with('items.product')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = $request->user()->orders()->create([
            'total' => 0,
        ]);

        $total = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product->quantity >= $item['quantity']) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->quantity -= $item['quantity'];
                $product->update();

                $total += $product->price * $item['quantity'];
            }

        }

        $order->update(['total' => $total]);

        return response()->json($order->load('items.product'), 201);
    }
}
