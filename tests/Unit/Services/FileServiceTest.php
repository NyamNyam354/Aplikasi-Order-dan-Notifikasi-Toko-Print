<?php

namespace Tests\Unit\Services;

use App\Services\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->fileService = app(FileService::class);
    }

    public function test_upload_stores_file(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $storedFilename = $this->fileService->upload($file);

        $this->assertNotEmpty($storedFilename);
        Storage::disk('private')->assertExists('orders/' . $storedFilename);
    }

    public function test_upload_returns_hash_name(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $storedFilename = $this->fileService->upload($file);

        $this->assertNotEmpty($storedFilename);
        $this->assertStringEndsWith('.pdf', $storedFilename);
        $this->assertNotEquals('test.pdf', $storedFilename);
    }

    public function test_exists_returns_true_for_stored_file(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $storedFilename = $this->fileService->upload($file);

        $this->assertTrue($this->fileService->exists($storedFilename));
    }

    public function test_exists_returns_false_for_missing_file(): void
    {
        $this->assertFalse($this->fileService->exists('nonexistent.pdf'));
    }

    public function test_delete_removes_file(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $storedFilename = $this->fileService->upload($file);

        $this->fileService->delete($storedFilename);

        Storage::disk('private')->assertMissing('orders/' . $storedFilename);
    }

    public function test_download_returns_response(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $storedFilename = $this->fileService->upload($file);

        $response = $this->fileService->download($storedFilename);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
    }

    public function test_validate_rejects_invalid_extension(): void
    {
        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'shell.php',
            'application/x-php',
            null,
            true
        );

        $errors = $this->fileService->validate($file);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_rejects_invalid_mime(): void
    {
        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'notes.txt',
            'text/plain',
            null,
            true
        );

        $errors = $this->fileService->validate($file);

        $this->assertNotEmpty($errors);
    }

    public function test_validate_accepts_valid_pdf(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $errors = $this->fileService->validate($file);

        $this->assertEmpty($errors);
    }

    public function test_validate_accepts_valid_docx(): void
    {
        $file = UploadedFile::fake()->create('test.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $errors = $this->fileService->validate($file);

        $this->assertEmpty($errors);
    }
}
