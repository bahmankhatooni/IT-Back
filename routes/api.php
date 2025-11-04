<?php


use App\Http\Controllers\Api\ComputerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\EmployeeController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Middleware\CheckRole;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// 🔐 Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 📍 Cities
    Route::get('/cities', [CityController::class, 'index'])->middleware('CheckRole:admin');
    Route::post('/cities', [CityController::class, 'store'])->middleware('CheckRole:admin');
    Route::put('/cities/{id}', [CityController::class, 'update'])->middleware('CheckRole:admin');
    Route::delete('/cities/{id}', [CityController::class, 'destroy'])->middleware('CheckRole:admin');

    // 🏢 Branches
    Route::get('/branches', [BranchController::class, 'index'])->middleware('CheckRole:admin,city_user');
    Route::post('/branches', [BranchController::class, 'store'])->middleware('CheckRole:admin,city_user');
    Route::put('/branches/{id}', [BranchController::class, 'update'])->middleware('CheckRole:admin,city_user');
    Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->middleware('CheckRole:admin,city_user');

    //Employee
    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('CheckRole:admin,city_user');
    Route::get('/employees/form-data', [EmployeeController::class, 'formData'])->middleware('CheckRole:admin,city_user');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('CheckRole:admin,city_user');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->middleware('CheckRole:admin,city_user');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->middleware('CheckRole:admin,city_user');
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->middleware('CheckRole:admin,city_user');

    //Computer
    Route::get('/computers', [ComputerController::class, 'index']);
    Route::post('/computers', [ComputerController::class, 'store']);
    Route::get('/computers/{id}', [ComputerController::class, 'show']);
    Route::put('/computers/{computer}', [ComputerController::class, 'update']);
    Route::delete('/computers/{id}', [ComputerController::class, 'destroy']);
    // 👥 Users
    Route::prefix('users')->middleware('checkRole:admin')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});
