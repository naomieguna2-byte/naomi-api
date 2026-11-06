<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\AuthController;

Route::apiResource('students', StudentController::class);
Route::post('/login', [AuthController::class, 'login']);
