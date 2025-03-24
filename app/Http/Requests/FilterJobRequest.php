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
            'filter' => ['nullable'],
            'filter.job_type' => ['nullable', 'string'],
            'filter.is_remote' => ['nullable', 'boolean'],
            'filter.salary_min' => ['nullable', 'numeric', 'min:0'],
            'filter.salary_max' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'string', 'in:title,company_name,salary_min,salary_max,published_at'],
            'order' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $inputData = $this->all();

        // If filter is a string (legacy format), keep it as is
        if (isset($inputData['filter']) && is_string($inputData['filter'])) {
            return;
        }

        // Convert dot notation to nested array for filter parameters
        $filter = [];

        foreach ($inputData as $key => $value) {
            if (str_starts_with($key, 'filter.')) {
                $filterKey = substr($key, 7); // Remove 'filter.' prefix
                $filter[$filterKey] = $value;
                unset($inputData[$key]);
            }
        }

        if (! empty($filter)) {
            $inputData['filter'] = $filter;
            $this->replace($inputData);
        }
    }
}
