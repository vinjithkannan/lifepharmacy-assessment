<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class ProductServiceTest extends TestCase
{
    protected $productService;
    protected $productRepositoryMock;
    protected $fileUploadServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Create mocks for ProductRepository and FileUploadService
        $this->productRepositoryMock = Mockery::mock(ProductRepository::class);
        $this->fileUploadServiceMock = Mockery::mock(FileUploadService::class);

        // Instantiate the ProductService with mocked dependencies
        $this->productService = new ProductService(
            $this->productRepositoryMock,
            $this->fileUploadServiceMock
        );
    }

    public function test_list_products_with_category_filter()
    {
        // Simulate a request with category filter
        $request = Request::create('/products', 'GET', ['category' => 'Electronics']);

        // Create mock products
        $products = Product::factory()->count(3)->make();

        // Mock the query and pagination logic
        $this->productRepositoryMock
            ->shouldReceive('query')
            ->once()
            ->andReturnSelf();

        $this->productRepositoryMock
            ->shouldReceive('whereHas')
            ->with('categories', Mockery::on(function ($callback) {
                $callback(Mockery::mock('Illuminate\Database\Eloquent\Builder'));
                return true;
            }))
            ->once()
            ->andReturnSelf();

        // Mock Cache behavior
        $cacheKey = 'products.page.1.' . md5($request->fullUrl());
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, Mockery::any(), Mockery::any())
            ->andReturn($products);

        // Call the listProducts method
        $result = $this->productService->listProducts($request);

        // Assert that the result contains the mocked products
        $this->assertCount(3, $result);
    }

    public function test_create_product_success()
    {
        // Prepare product data
        $validated = [
            'name' => 'Product Name',
            'slug' => 'product-name',
            'price' => 100.0,
            'categories' => [1, 2],
            'images' => [
                $this->createMockFile(),
                $this->createMockFile()
            ]
        ];

        // Mock the create method in the repository
        $product = new Product(['name' => 'Product Name']);
        $this->productRepositoryMock
            ->shouldReceive('create')
            ->with($validated)
            ->once()
            ->andReturn($product);

        // Mock the image upload
        $this->fileUploadServiceMock
            ->shouldReceive('uploadFile')
            ->with($validated['images'])
            ->once()
            ->andReturn([
                ['image' => 'uploads/image1.jpg'],
                ['image' => 'uploads/image2.jpg']
            ]);

        // Mock the syncCategories method
        $product->categories = Category::find($validated['categories']);
        $this->productRepositoryMock
            ->shouldReceive('syncCategories')
            ->with($product, $validated)
            ->once();

        // Call the createProduct method
        $result = $this->productService->createProduct($validated);

        // Assertions
        $this->assertEquals('Product Name', $result->name);
    }

    public function test_get_product_success()
    {
        // Mock the product retrieval
        $product = Product::factory()->create();
        $this->productRepositoryMock
            ->shouldReceive('findById')
            ->with($product->id)
            ->once()
            ->andReturn($product);

        // Call the getProduct method
        $result = $this->productService->getProduct($product->id);

        // Assertions
        $this->assertEquals($product->id, $result->id);
    }

    public function test_update_product_success()
    {
        // Prepare product data for update
        $validated = [
            'name' => 'Updated Product',
            'slug' => 'updated-product',
            'price' => 150.0,
            'categories' => [1, 2],
            'images' => [
                $this->createMockFile()
            ]
        ];

        // Mock the update product method
        $product = Product::factory()->create();
        $this->productRepositoryMock
            ->shouldReceive('update')
            ->with($validated, $product->id)
            ->once()
            ->andReturn($product);

        // Mock image upload
        $this->fileUploadServiceMock
            ->shouldReceive('uploadFile')
            ->with($validated['images'])
            ->once()
            ->andReturn([['image' => 'uploads/image.jpg']]);

        // Mock category sync
        $this->productRepositoryMock
            ->shouldReceive('syncCategories')
            ->with($product, $validated)
            ->once();

        // Call the updateProduct method
        $result = $this->productService->updateProduct($validated, (object) ['id' => $product->id]);

        // Assertions
        $this->assertEquals('Updated Product', $result->name);
    }

    public function test_delete_product_success()
    {
        // Create a mock product
        $product = Product::factory()->create();

        // Mock the delete method
        $this->productRepositoryMock
            ->shouldReceive('delete')
            ->with($product)
            ->once()
            ->andReturn(true);

        // Call the deleteProduct method
        $result = $this->productService->deleteProduct($product);

        // Assertions
        $this->assertTrue($result);
    }

    private function createMockFile()
    {
        return Mockery::mock(\Illuminate\Http\UploadedFile::class)
            ->shouldReceive('getClientOriginalExtension')
            ->andReturn('jpg')
            ->getMock();
    }
}
