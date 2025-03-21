<?php

namespace App\Models;

use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => AttributeType::class,
        'options' => 'array',
    ];

    public function jobAttributeValues(): HasMany
    {
        return $this->hasMany(JobAttributeValue::class);
    }
}
