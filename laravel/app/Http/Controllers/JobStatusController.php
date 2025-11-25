<?php

namespace App\Http\Controllers;

use App\Models\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JobStatusController extends Controller
{
    public function index(): JsonResponse
    {
        // Retorna todos os JobStatus, o frontend fará o filtro de exibição
        $jobStatuses = JobStatus::all();

        return response()->json($jobStatuses);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $jobStatus = JobStatus::where('uuid', $uuid)->first();

        if (!$jobStatus) {
            return response()->json(['message' => 'Job Status not found.'], 404);
        }

        return response()->json($jobStatus);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $jobStatus = JobStatus::find($uuid);

        if (!$jobStatus) {
            return response()->json(['message' => 'Job Status not found.'], 404);
        }

        $jobStatus->delete(); // Exclui o registro

        return response()->json(['message' => 'Job Status removido.']);
    }
}
