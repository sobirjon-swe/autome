<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'skill' => ['required', 'in:grammar,vocabulary,listening,reading,speaking,writing'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'session_datetime' => ['required', 'date'],
            'resource_id' => ['nullable', 'exists:resources,id'],
            'daily_plan_id' => ['nullable', 'exists:daily_plans,id'],
            'units_covered' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'send_to_telegram' => ['nullable', 'boolean'],
            'attachment_type' => ['nullable', 'in:audio,video,youtube_link,image,other'],
            'youtube_url' => ['nullable', 'required_if:attachment_type,youtube_link', 'url'],
            'attachment_file' => ['nullable', 'file'],
        ];

        $type = $this->input('attachment_type');
        if ($type === 'audio') {
            $rules['attachment_file'] = ['required', 'file', 'mimes:mp3,m4a,ogg,wav', 'max:51200'];
        } elseif ($type === 'video') {
            $rules['attachment_file'] = ['required', 'file', 'mimes:mp4,mov,webm', 'max:204800'];
        } elseif ($type === 'image') {
            $rules['attachment_file'] = ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'];
        }

        return $rules;
    }
}
