<?php

namespace App\Http\Requests;

use App\Constants\OrderConstant;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:' . OrderConstant::MAX_FILE_SIZE,
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();

                    if (!in_array($extension, OrderConstant::ALLOWED_EXTENSIONS)) {
                        $fail('Ekstensi file tidak diizinkan');
                    }

                    if (!in_array($mimeType, OrderConstant::ALLOWED_MIMES)) {
                        $fail('Tipe file tidak valid');
                    }

                    if ($value->getSize() === 0) {
                        $fail('File tidak boleh kosong');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:' . OrderConstant::MAX_NOTES_LENGTH],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.max' => 'Ukuran file maksimal 100MB',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
