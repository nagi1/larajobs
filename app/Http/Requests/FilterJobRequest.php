<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'in:title,company_name,salary_min,salary_max,published_at'],
            'order' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
