<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'title'                  => $this->title,
            'date'                   => $this->date->format('Y-m-d'),
            'full_name'              => $this->full_name,
            'distance'               => $this->distance,
            'finish_time'            => $this->finish_time,
            'age_category'           => $this->age_category,
            'overall_placement'      => $this->overall_placement,
            'age_category_placement' => $this->age_category_placement,
            'created_at'             => $this->created_at->toISOString(),
        ];
    }
}