<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRaceRequest;
use App\Actions\CreateRaceAction;
use App\Actions\FetchRacesAction;
use App\Actions\RaceResultsAction;
use App\Actions\UpdateRaceResultAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function __construct(
        private readonly CreateRaceAction      $createRaceAction,
        private readonly FetchRacesAction      $fetchRacesAction,
        private readonly RaceResultsAction     $raceResultsAction,
        private readonly UpdateRaceResultAction $updateRaceResultAction,
    ) {}

    public function import(ImportRaceRequest $request): JsonResponse
    {
        $result = $this->createRaceAction->handle($request);

        return response()->json([
            'message' => 'Results imported successfully',
            'data'    => $result,
        ], JsonResponse::HTTP_CREATED);
    }

    public function index(Request $request): JsonResponse
    {
        $races = $this->fetchRacesAction->handle($request);

        return response()->json([
            'data' => $races,
        ]);
    }

    public function results(int $raceId): JsonResponse
    {
        $results = $this->raceResultsAction->handle($raceId);

        return response()->json([
            'data' => $results,
        ]);
    }

    public function update(int $raceId, Request $request): JsonResponse
    {
        $result = $this->updateRaceResultAction->handle($raceId, $request);

        return response()->json([
            'message' => 'Race result updated successfully',
            'data'    => $result,
        ]);
    }
}