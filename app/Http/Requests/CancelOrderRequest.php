<?php

namespace App\Http\Requests;

use App\Constants\OrderConstant;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => [
                'required',
                'string',
                'min:' . OrderConstant::MIN_CANCEL_REASON_LENGTH,
                'max:' . OrderConstant::MAX_NOTES_LENGTH,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi',
            'cancel_reason.min' => 'Alasan pembatalan minimal 10 karakter',
            'cancel_reason.max' => 'Alasan pembatalan maksimal 1000 karakter',
        ];
    }
}
