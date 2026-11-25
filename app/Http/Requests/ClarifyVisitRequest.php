<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Permintaan klarifikasi admin kepada sales atas hasil check-in. Otorisasi ditangani RouteVisitPolicy. */
class ClarifyVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clarification_note' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'clarification_note.required' => 'Tulis hal yang perlu diklarifikasi.',
            'clarification_note.min' => 'Catatan klarifikasi minimal 3 karakter.',
            'clarification_note.max' => 'Catatan klarifikasi maksimal 500 karakter.',
        ];
    }
}
