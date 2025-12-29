<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * List expenses with optional filtering & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::where('user_id', $request->user()->id);

        // Optional filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->query('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->query('end_date'));
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $expenses,
        ], 200);
    }

    /**
     * Store a new expense.
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id]
        ));

        return response()->json([
            'success' => true,
            'data'    => $expense,
        ], 201);
    }

    /**
     * Show a single expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        $this->authorizeExpense($expense);

        return response()->json([
            'success' => true,
            'data'    => $expense,
        ], 200);
    }

    /**
     * Update an expense.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorizeExpense($expense);

        $expense->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $expense,
        ], 200);
    }

    /**
     * Delete an expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorizeExpense($expense);

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully',
        ], 200);
    }

    /**
     * Ensure expense belongs to authenticated user
     */
    private function authorizeExpense(Expense $expense): void
    {
        if ($expense->user_id !== auth()->id()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this expense',
            ], 403));
        }
    }
}
