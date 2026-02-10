<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'full_name',
        'distance',
        'finish_time',
        'age_category',
        'overall_placement',
        'age_category_placement',
    ];

    protected $casts = [
        'date'                   => 'date',
        'overall_placement'      => 'integer',
        'age_category_placement' => 'integer',
    ];

    public function scopeLongDistance(Builder $query): Builder
    {
        return $query->where('distance', 'long');
    }

    public function scopeMediumDistance(Builder $query): Builder
    {
        return $query->where('distance', 'medium');
    }

    public function scopeByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', $title);
    }
    
    public function scopeByAgeCategory(Builder $query, string $category): Builder
    {
        return $query->where('age_category', $category);
    }
}