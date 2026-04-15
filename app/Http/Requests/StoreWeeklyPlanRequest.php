<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_start_date' => ['required', 'date', 'unique:weekly_plans,week_start_date'],
            'total_planned_hours' => ['required', 'numeric', 'min:1', 'max:168'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'daily_plans' => ['required', 'array', 'size:7'],
            'daily_plans.*.date' => ['required', 'date'],
            'daily_plans.*.planned_hours' => ['required', 'numeric', 'min:0', 'max:24'],
        ];
    }
}
