<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Race;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RaceImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_race_results(): void
    {
        Storage::fake('local');

        $csvContent = "fullName,distance,time,ageCategory\n";
        $csvContent .= "John Doe,long,01:23:45,M40\n";
        $csvContent .= "Jane Smith,long,01:25:00,F35\n";
        $csvContent .= "Bob Wilson,medium,00:45:30,M30\n";

        $file = UploadedFile::fake()->createWithContent('results.csv', $csvContent);

        $response = $this->postJson('/api/imported-races', [
            'title' => 'Porto Marathon 2026',
            'date'  => '2026-03-15',
            'file'  => $file,
        ]);

        $response->assertCreated()
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'title',
                         'date',
                         'long_distance_count',
                         'medium_distance_count',
                         'total_imported',
                     ],
                 ]);

        $this->assertDatabaseHas('races', [
            'full_name'         => 'John Doe',
            'distance'          => 'long',
            'overall_placement' => 1,
        ]);

        $this->assertDatabaseCount('races', 3);
    }

    public function test_import_fails_without_csv_file(): void
    {
        $response = $this->postJson('/api/imported-races', [
            'title' => 'Porto Marathon 2026',
            'date'  => '2026-03-15',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['file']);
    }

    public function test_import_fails_without_title(): void
    {
        $file = UploadedFile::fake()->create('results.csv', 100);

        $response = $this->postJson('/api/imported-races', [
            'date' => '2026-03-15',
            'file' => $file,
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_long_distance_results_sorted_by_time(): void
    {
        Storage::fake('local');

        $csvContent = "fullName,distance,time,ageCategory\n";
        $csvContent .= "Slow Runner,long,02:00:00,M40\n";
        $csvContent .= "Fast Runner,long,01:00:00,M35\n";
        $csvContent .= "Mid Runner,long,01:30:00,M30\n";

        $file = UploadedFile::fake()->createWithContent('results.csv', $csvContent);

        $this->postJson('/api/imported-races', [
            'title' => 'Test Race',
            'date'  => '2026-03-15',
            'file'  => $file,
        ]);

        // Fast runner should be placement 1
        $this->assertDatabaseHas('races', [
            'full_name'         => 'Fast Runner',
            'overall_placement' => 1,
        ]);

        // Slow runner should be placement 3
        $this->assertDatabaseHas('races', [
            'full_name'         => 'Slow Runner',
            'overall_placement' => 3,
        ]);
    }

    public function test_can_fetch_imported_races(): void
    {
        Race::factory()->count(5)->create();

        $response = $this->getJson('/api/imported-races');

        $response->assertOk()
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'date', 'full_name', 'distance', 'finish_time']
                     ]
                 ]);
    }
}