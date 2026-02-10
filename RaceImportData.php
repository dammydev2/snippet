<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\ImportRaceRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RaceImportData
{
    public function __construct(
        public readonly string       $title,
        public readonly string       $date,
        public readonly UploadedFile $file,
    ) {}

    public static function fromRequest(ImportRaceRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            date:  $request->validated('date'),
            file:  $request->file('file'),
        );
    }
}