<?php 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


//Categories
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});
//expenses 
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('expenses', ExpenseController::class);
});
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
