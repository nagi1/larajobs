<?php

namespace App\Http\Controllers;

use App\Exceptions\FilterException;
use App\Http\Requests\FilterJobRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use App\Services\JobFilterService;
use Illuminate\Http\JsonResponse;

class JobPostController extends Controller
{
    /**
     * Display a listing of job posts.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(FilterJobRequest $request, JobFilterService $filterService)
    {
        $query = JobPost::query()->with([
            'jobAttributeValues', 'categories', 'languages', 'locations',
        ]);

        // Get all validated fields
        $validated = $request->validated();

        // Apply filtering if a filter parameter is provided
        if (isset($validated['filter'])) {
            try {
                // Use the filter service for all filters
                $query = $filterService->apply($query, $validated['filter']);
            } catch (FilterException $e) {
                return $this->filterError($e);
            }
        }

        // Apply sorting if requested
        if (isset($validated['sort'])) {
            $direction = $validated['order'] ?? 'asc';
            $query->orderBy($validated['sort'], $direction);
        } else {
            // Default sorting is by created_at desc
            $query->orderBy('created_at', 'desc');
        }

        // Get the paginated results
        $perPage = $validated['per_page'] ?? 15;
        $jobPosts = $query->paginate($perPage);

        // Return a paginated resource collection
        return JobPostResource::collection($jobPosts)
            ->additional([
                'meta' => [
                    'filters' => $validated['filter'] ?? null,
                    'sort' => $validated['sort'] ?? null,
                    'order' => $validated['order'] ?? 'asc',
                ],
            ]);
    }

    /**
     * Return a standardized error response for filter exceptions.
     */
    protected function filterError(FilterException $e): JsonResponse
    {
        return response()->json([
            'error' => 'Invalid filter format',
            'message' => $e->getMessage(),
            'code' => 'INVALID_FILTER',
        ], 400);
    }
}
