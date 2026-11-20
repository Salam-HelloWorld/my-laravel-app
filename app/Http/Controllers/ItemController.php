<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Support\ItemStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ItemController extends Controller
{
    public function __construct(private readonly ItemStore $store) {}

    /** List items. */
    public function index(): JsonResponse
    {
        return response()->json($this->store->all());
    }

    /** Get one item. */
    public function show(int $id): JsonResponse
    {
        return response()->json($this->store->find($id) ?? abort(404, 'Item not found'));
    }

    /** Create an item. */
    public function store(StoreItemRequest $request): JsonResponse
    {
        return response()->json($this->store->create($request->validated()), 201);
    }

    /** Partially update an item. */
    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        return response()->json($this->store->update($id, $request->validated()) ?? abort(404, 'Item not found'));
    }

    /** Delete an item. */
    public function destroy(int $id): Response
    {
        if (! $this->store->delete($id)) {
            abort(404, 'Item not found');
        }

        return response()->noContent();
    }
}
