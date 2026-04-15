<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:book,course,podcast,youtube_channel,other'],
            'skill' => ['required', 'in:grammar,vocabulary,listening,reading,speaking,writing'],
            'total_units' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
