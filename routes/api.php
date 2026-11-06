
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\StudentController;

// PUBLIC LOGIN
Route::post('/login', [AuthController::class, 'login']);

// PROTECTED API ROUTES
Route::middleware('auth:sanctum')->group(function () {

    // GET AUTHENTICATED USER
    Route::get('/me', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // STUDENT RECORDS - VIEW
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);

    // ADMINISTRATOR ONLY
    Route::middleware('can:manage-students')->group(function () {

        Route::post('/students', [StudentController::class, 'store']);

        Route::put('/students/{id}', [StudentController::class, 'update']);

        Route::delete('/students/{id}', [StudentController::class, 'destroy']);

    });

});
