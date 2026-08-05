<?php

namespace App\Services;

use App\Constants\OrderConstant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService
{
    private string $disk = 'private';
    private string $directory = 'orders';

    public function upload(UploadedFile $file): string
    {
        $storedFilename = $file->hashName();
        $file->storeAs($this->directory, $storedFilename, $this->disk);

        return $storedFilename;
    }

    public function download(string $storedFilename): StreamedResponse
    {
        $path = $this->directory . '/' . $storedFilename;

        return Storage::disk($this->disk)->download($path);
    }

    public function exists(string $storedFilename): bool
    {
        $path = $this->directory . '/' . $storedFilename;

        return Storage::disk($this->disk)->exists($path);
    }

    public function delete(string $storedFilename): bool
    {
        $path = $this->directory . '/' . $storedFilename;

        return Storage::disk($this->disk)->delete($path);
    }

    public function getSignedUrl(string $storedFilename, int $expirationMinutes = OrderConstant::SIGNED_URL_EXPIRATION): string
    {
        $path = $this->directory . '/' . $storedFilename;

        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($expirationMinutes));
    }

    public function validate(UploadedFile $file): array
    {
        $errors = [];
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        if (!in_array($extension, OrderConstant::ALLOWED_EXTENSIONS)) {
            $errors[] = 'Ekstensi file tidak diizinkan';
        }

        if (!in_array($mimeType, OrderConstant::ALLOWED_MIMES)) {
            $errors[] = 'Tipe file tidak valid';
        }

        if ($file->getSize() === 0) {
            $errors[] = 'File tidak boleh kosong';
        }

        if ($file->getSize() > OrderConstant::MAX_FILE_SIZE * 1024) {
            $errors[] = 'Ukuran file maksimal 100MB';
        }

        return $errors;
    }
}
