<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    protected $fileUploadService;

    public function setUp(): void
    {
        parent::setUp();

        // Initialize the FileUploadService
        $this->fileUploadService = new FileUploadService();

        // Fake the storage disk
        Storage::fake('public');
    }

    public function test_upload_file_success()
    {
        // Create a fake file to simulate file upload
        $file = UploadedFile::fake()->image('example.jpg');

        // Call the uploadFile method
        $urls = $this->fileUploadService->uploadFile([$file]);

        // Assert that the file was uploaded and returned a URL
        $filePath = 'uploads/' . Str::random(20) . '.jpg'; // Random file name

        // Assert that the file was uploaded to the 'public' disk
        Storage::disk('public')->assertExists($filePath);

        // Assert the URLs array contains the expected URL
        $this->assertCount(1, $urls);
        $this->assertArrayHasKey('image', $urls[0]);
        $this->assertStringContainsString('uploads', $urls[0]['image']);
        $this->assertStringContainsString('.jpg', $urls[0]['image']);
    }

    public function test_upload_multiple_files_success()
    {
        // Create multiple fake files
        $files = [
            UploadedFile::fake()->image('example1.jpg'),
            UploadedFile::fake()->image('example2.png')
        ];

        // Call the uploadFile method
        $urls = $this->fileUploadService->uploadFile($files);

        // Assert that two files were uploaded
        $this->assertCount(2, $urls);

        // Check that both files exist in storage
        Storage::disk('public')->assertExists('uploads/' . Str::random(20) . '.jpg');
        Storage::disk('public')->assertExists('uploads/' . Str::random(20) . '.png');
    }

    public function test_file_upload_failure_due_to_invalid_extension()
    {
        // Create a fake file with an invalid extension
        $file = UploadedFile::fake()->create('example.txt', 100); // .txt file

        // Call the uploadFile method, expect it to throw an error
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->fileUploadService->uploadFile([$file]);
    }
}
