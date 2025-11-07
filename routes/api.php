<?php

use App\Http\Controllers\Api\ComputerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PrinterController;
use App\Http\Controllers\Api\ScannerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\EmployeeController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Middleware\CheckRole;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// 🔐 Public Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔒 Protected routes
Route::middleware('auth:sanctum')->group(function () {
// 📊 Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/recent-equipment', [DashboardController::class, 'getRecentEquipment']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    // 👤 Users
        Route::get('/users', [UserController::class, 'index'])->middleware('CheckRole:admin');
        Route::get('/users/{id}', [UserController::class, 'show'])->middleware('CheckRole:admin');
        Route::post('/users', [UserController::class, 'store'])->middleware('CheckRole:admin');
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('CheckRole:admin');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('CheckRole:admin');

    // 📍 Cities
    Route::get('/cities', [CityController::class, 'index'])->middleware('CheckRole:admin');
    Route::post('/cities', [CityController::class, 'store'])->middleware('CheckRole:admin');
    Route::put('/cities/{id}', [CityController::class, 'update'])->middleware('CheckRole:admin');
    Route::delete('/cities/{id}', [CityController::class, 'destroy'])->middleware('CheckRole:admin');

    // 🏢 Branches
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store'])->middleware('CheckRole:admin,city_user');
    Route::put('/branches/{id}', [BranchController::class, 'update'])->middleware('CheckRole:admin,city_user');
    Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->middleware('CheckRole:admin,city_user');

    // 👥 Employees
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/form-data', [EmployeeController::class, 'formData']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // 💻 Computers
    Route::get('/computers', [ComputerController::class, 'index']);
    Route::post('/computers', [ComputerController::class, 'store']);
    Route::get('/computers/{id}', [ComputerController::class, 'show']);
    Route::put('/computers/{id}', [ComputerController::class, 'update']);
    Route::delete('/computers/{id}', [ComputerController::class, 'destroy']);

    // 🖨️ Printers - اضافه کردن routeهای پرینتر
    Route::get('/printers', [PrinterController::class, 'index']);
    Route::post('/printers', [PrinterController::class, 'store']);
    Route::get('/printers/{id}', [PrinterController::class, 'show']);
    Route::put('/printers/{id}', [PrinterController::class, 'update']);
    Route::delete('/printers/{id}', [PrinterController::class, 'destroy']);

    // 📷 Scanners - اضافه کردن routeهای اسکنر
    Route::get('/scanners', [ScannerController::class, 'index']);
    Route::post('/scanners', [ScannerController::class, 'store']);
    Route::get('/scanners/{id}', [ScannerController::class, 'show']);
    Route::put('/scanners/{id}', [ScannerController::class, 'update']);
    Route::delete('/scanners/{id}', [ScannerController::class, 'destroy']);





});

// 🚫 Fallback route برای APIهای protected
Route::fallback(function () {
    return response()->json(['message' => 'Route پیدا نشد'], 404);
});
