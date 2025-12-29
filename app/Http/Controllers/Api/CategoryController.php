<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List all categories for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $request->user()
            ->categories()
            ->withCount('expenses') // optional: show how many expenses per category
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Create a new category for authenticated user.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }

    /**
     * Show a single category (user-scoped).
     */
    public function show(Request $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update a category (user-scoped).
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Delete a category (user-scoped).
     * Set category_id of expenses to null before deleting the category.
     */
    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        // Option 1: set expenses' category_id to null
        $category->expenses()->update(['category_id' => null]);

        // Option 2: alternatively, delete all expenses
        // $category->expenses()->delete();

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }

    /**
     * Private helper to ensure user owns the category.
     */
    private function authorizeCategory(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this category.'
            ], 403));
        }
    }
}
