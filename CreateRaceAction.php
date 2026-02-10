<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\RaceImportData;
use App\Models\Race;
use App\Http\Requests\ImportRaceRequest;
use App\Services\CsvParserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class CreateRaceAction
{
    public function __construct(
        private readonly CsvParserService $csvParser,
    ) {}

    public function handle(ImportRaceRequest $request): array
    {
        $data = RaceImportData::fromRequest($request);

        return DB::transaction(function () use ($data) {
            // Parse CSV
            $rows = $this->csvParser->parse($data->file->getPathname());

            // Process long distance
            $longCount   = $this->processLongDistance($rows, $data);

            // Process medium distance
            $mediumCount = $this->processMediumDistance($rows, $data);

            Log::info('Race results imported', [
                'title'         => $data->title,
                'date'          => $data->date,
                'long_count'    => $longCount,
                'medium_count'  => $mediumCount,
            ]);

            return [
                'title'                => $data->title,
                'date'                 => $data->date,
                'long_distance_count'  => $longCount,
                'medium_distance_count'=> $mediumCount,
                'total_imported'       => $longCount + $mediumCount,
            ];
        });
    }

    private function processLongDistance(Collection $rows, RaceImportData $data): int
    {
        $longResults = $rows
            ->filter(fn($row) => $row['distance'] === 'long')
            ->sortBy(fn($row) => $this->timeToSeconds($row['time']))
            ->values();

        $overallPlacement      = 1;
        $ageCategoryPlacements = [];

        $longResults->each(function ($result) use ($data, &$overallPlacement, &$ageCategoryPlacements) {
            $ageCategory = $result['ageCategory'];

            if (!isset($ageCategoryPlacements[$ageCategory])) {
                $ageCategoryPlacements[$ageCategory] = 1;
            }

            Race::create([
                'title'                  => $data->title,
                'date'                   => $data->date,
                'full_name'              => $result['fullName'],
                'distance'               => $result['distance'],
                'finish_time'            => $result['time'],
                'age_category'           => $ageCategory,
                'overall_placement'      => $overallPlacement++,
                'age_category_placement' => $ageCategoryPlacements[$ageCategory]++,
            ]);
        });

        return $longResults->count();
    }

    private function processMediumDistance(Collection $rows, RaceImportData $data): int
    {
        $mediumResults = $rows->filter(fn($row) => $row['distance'] === 'medium');

        // Use insert for performance on large datasets
        $records = $mediumResults->map(fn($result) => [
            'title'                  => $data->title,
            'date'                   => $data->date,
            'full_name'              => $result['fullName'],
            'distance'               => $result['distance'],
            'finish_time'            => $result['time'],
            'age_category'           => $result['ageCategory'],
            'overall_placement'      => null,
            'age_category_placement' => null,
            'created_at'             => now(),
            'updated_at'             => now(),
        ])->values()->toArray();

        Race::insert($records);

        return $mediumResults->count();
    }

    /**
     * Convert time string to seconds for accurate sorting
     */
    private function timeToSeconds(string $time): int
    {
        $parts = explode(':', $time);

        return match(count($parts)) {
            3 => ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2],
            2 => ((int)$parts[0] * 60) + (int)$parts[1],
            default => 0,
        };
    }
}