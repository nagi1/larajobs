<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'city' => 'string',
            'state' => 'string',
            'country' => 'string',
        ];
    }

    public function jobPosts(): BelongsToMany
    {
        return $this->belongsToMany(JobPost::class);
    }
}
