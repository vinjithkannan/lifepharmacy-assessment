<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\Response;
use Tests\TestCase;
use Mockery;

class ProductControllerTest extends TestCase
{
    protected $productServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mock the ProductService
        $this->productServiceMock = Mockery::mock(ProductService::class);
        $this->app->instance(ProductService::class, $this->productServiceMock);
    }

    public function test_index_success()
    {
        // Simulated products data
        $products = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200],
        ];

        // Mock the listProducts method
        $this->productServiceMock->shouldReceive('listProducts')
            ->once()
            ->with(Mockery::type(Request::class))
            ->andReturn($products);

        // Send GET request to list products
        $response = $this->getJson('/api/products');

        // Assert the response status and content
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson($products);
    }

    public function test_store_success()
    {
        // Simulated product data
        $productData = [
            'name' => 'New Product',
            'price' => 300,
            'quantity' => 10,
            'slug' => 'new-product',
        ];

        // Mock the createProduct method
        $this->productServiceMock->shouldReceive('createProduct')
            ->once()
            ->with($productData)
            ->andReturn(new Product($productData));

        // Send POST request to create a product
        $response = $this->postJson('/api/products', $productData);

        // Assert the response status and content
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'name', 'price']]);
    }

    public function test_show_success()
    {
        $product = new Product(['id' => 1, 'name' => 'Product 1', 'price' => 100]);

        // Mock the getProduct method
        $this->productServiceMock->shouldReceive('getProduct')
            ->once()
            ->with(1)
            ->andReturn($product);

        // Send GET request to show a product
        $response = $this->getJson('/api/product/1');

        // Assert the response status and content
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['id' => 1, 'name' => 'Product 1', 'price' => 100]);
    }

    public function test_update_success()
    {
        $productData = [
            'name' => 'Updated Product',
            'price' => 400,
            'quantity' => 20,
            'slug' => 'updated-product',
        ];

        $product = new Product($productData);

        // Mock the updateProduct method
        $this->productServiceMock->shouldReceive('updateProduct')
            ->once()
            ->with($productData, Mockery::type(Request::class))
            ->andReturn($product);

        // Send PUT request to update a product
        $response = $this->putJson('/api/product/1', $productData);

        // Assert the response status and content
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'name', 'price']]);
    }

    public function test_destroy_success()
    {
        $product = new Product(['id' => 1, 'name' => 'Product 1']);

        // Mock the deleteProduct method
        $this->productServiceMock->shouldReceive('deleteProduct')
            ->once()
            ->with($product)
            ->andReturn(true);

        // Send DELETE request to destroy a product
        $response = $this->deleteJson('/api/product/1');

        // Assert the response status and content
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'deleted']);
    }

    // Additional test cases for failure scenarios can be added as needed
}
