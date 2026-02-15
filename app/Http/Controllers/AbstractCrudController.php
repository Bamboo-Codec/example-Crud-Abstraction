<?php

namespace App\Http\Controllers;

use App\Services\Crud\CrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractCrudController extends Controller
{
    protected CrudService $crudService;
    protected string $relationName;
    protected array $validationRules;
    protected $uniqueField = null;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->crudService->index($request, $this->relationName);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->crudService->store($request, $this->relationName, $this->validationRules, $this->uniqueField);
            return response()->json($data, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $resource = $this->crudService->show($request, $this->relationName, $id);
            return response()->json([$this->relationName => $resource]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $this->crudService->update($request, $this->relationName, $id, $this->validationRules);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $data = $this->crudService->destroy($request, $this->relationName, $id);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}