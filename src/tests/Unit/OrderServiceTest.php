<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\OrderService;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    protected $orderService;
    protected $orderRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        // Create a mock for the OrderRepository
        $this->orderRepositoryMock = Mockery::mock(OrderRepository::class);

        // Inject the mocked repository into the service
        $this->orderService = new OrderService($this->orderRepositoryMock);
    }

    public function test_list_orders()
    {
        // Create a mock user
        $user = User::factory()->create();

        // Define the expected return value from the repository mock
        $orders = Order::factory()->count(3)->make();  // Creating mock orders

        // Mock the `getUserOrders` method of the repository
        $this->orderRepositoryMock
            ->shouldReceive('getUserOrders')
            ->with($user)
            ->once()
            ->andReturn($orders);

        // Call the `listOrders` method
        $result = $this->orderService->listOrders($user);

        // Assert the result is the same as what we mocked
        $this->assertEquals($orders, $result);
    }

    public function test_create_order_success()
    {
        // Create mock user and product
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'price' => 100]);

        // Mock the `createOrder` method to return an empty order
        $order = Order::factory()->make(['user_id' => $user->id, 'total' => 0]);
        $this->orderRepositoryMock
            ->shouldReceive('createOrder')
            ->with($user, ['total' => 0])
            ->once()
            ->andReturn($order);

        // Mock the `findProduct` method to return the mock product
        $this->orderRepositoryMock
            ->shouldReceive('findProduct')
            ->with($product->id)
            ->once()
            ->andReturn($product);

        // Mock the `addItemToOrder` and `updateProduct` methods
        $this->orderRepositoryMock
            ->shouldReceive('addItemToOrder')
            ->with($order, [
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price
            ])
            ->once();

        $this->orderRepositoryMock
            ->shouldReceive('updateProduct')
            ->with($product, ['quantity' => 9]) // Quantity should decrease after the order
            ->once();

        // Mock the `updateOrder` method to update the order's total
        $this->orderRepositoryMock
            ->shouldReceive('updateOrder')
            ->with($order, ['total' => 100])
            ->once();

        // Simulate request validation data
        $requestData = [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ];

        // Convert request data to Request object
        $request = Request::create('/orders', 'POST', $requestData);

        // Call the `createOrder` method
        $createdOrder = $this->orderService->createOrder($user, $request);

        // Assertions
        $this->assertNotNull($createdOrder);
        $this->assertEquals(100, $createdOrder->total);
        $this->assertCount(1, $createdOrder->items);
    }

    public function test_create_order_product_not_available()
    {
        // Create mock user and product with insufficient stock
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 0, 'price' => 100]);

        // Mock the `createOrder` method to return an empty order
        $order = Order::factory()->make(['user_id' => $user->id, 'total' => 0]);
        $this->orderRepositoryMock
            ->shouldReceive('createOrder')
            ->with($user, ['total' => 0])
            ->once()
            ->andReturn($order);

        // Mock the `findProduct` method to return the mock product
        $this->orderRepositoryMock
            ->shouldReceive('findProduct')
            ->with($product->id)
            ->once()
            ->andReturn($product);

        // Call the `createOrder` method with insufficient product stock
        $requestData = [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ]
        ];

        $request = Request::create('/orders', 'POST', $requestData);

        // Expect the order's total to remain zero due to insufficient stock
        $createdOrder = $this->orderService->createOrder($user, $request);

        // Assertions
        $this->assertNotNull($createdOrder);
        $this->assertEquals(0, $createdOrder->total); // No total change due to insufficient stock
        $this->assertCount(0, $createdOrder->items); // No items in the order due to stock issue
    }
}
