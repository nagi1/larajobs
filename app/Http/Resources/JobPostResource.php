<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'company_name' => $this->company_name,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'is_remote' => $this->is_remote,
            'job_type' => $this->job_type,
            'job_type_value' => $this->job_type?->value,
            'status' => $this->status,
            'status_value' => $this->status?->value,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include relationships data if they're loaded
            'languages' => $this->when($this->relationLoaded('languages'), function () {
                return $this->languages;
            }),
            'locations' => $this->when($this->relationLoaded('locations'), function () {
                return $this->locations;
            }),
            'categories' => $this->when($this->relationLoaded('categories'), function () {
                return $this->categories;
            }),
            'attribute_values' => $this->when($this->relationLoaded('attributeValues'), function () {
                return $this->attributeValues;
            }),
        ];
    }
}
