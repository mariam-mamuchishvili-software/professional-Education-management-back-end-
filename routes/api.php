<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CollegeController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ProfessionController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\GroupController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('colleges', CollegeController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('professions', ProfessionController::class);
Route::apiResource('modules', ModuleController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('groups', GroupController::class);