<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use Mockery;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $orderServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mock OrderService
        $this->orderServiceMock = Mockery::mock(OrderService::class);
        $this->app->instance(OrderService::class, $this->orderServiceMock);
    }

    public function test_index_success()
    {
        $user = User::factory()->create(); // Create a user
        $orders = [['id' => 1, 'total' => 100], ['id' => 2, 'total' => 200]]; // Simulated orders

        // Mock the listOrders method
        $this->orderServiceMock->shouldReceive('listOrders')
            ->once()
            ->with($user)
            ->andReturn($orders);

        // Authenticate the user
        $this->actingAs($user);

        // Send GET request to list orders
        $response = $this->getJson('/api/orders');

        // Assert the response is OK and contains the expected orders
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson($orders);
    }

    public function test_store_success()
    {
        $user = User::factory()->create(); // Create a user
        $orderData = [
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
                ['product_id' => 2, 'quantity' => 1]
            ]
        ]; // Simulated order data

        // Mock the createOrder method
        $this->orderServiceMock->shouldReceive('createOrder')
            ->once()
            ->with($user, Mockery::on(function ($request) {
                return $request->has('items'); // Ensure 'items' exists in the request
            }))
            ->andReturn(['id' => 1, 'total' => 300, 'items' => $orderData['items']]);

        // Authenticate the user
        $this->actingAs($user);

        // Send POST request to create an order
        $response = $this->postJson('/api/orders', $orderData);

        // Assert the response status and structure
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['id', 'total', 'items']);
    }
}
