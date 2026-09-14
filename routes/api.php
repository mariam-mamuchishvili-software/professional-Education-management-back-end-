<?php

use App\Http\Controllers\Api\CollegeController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ProfessionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Resource Collections (CRUD)
|--------------------------------------------------------------------------
| index | store | show | update | destroy
*/

Route::apiResource('colleges', CollegeController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('professions', ProfessionController::class);
Route::apiResource('modules', ModuleController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('groups', GroupController::class);

/*
|--------------------------------------------------------------------------
| Colleges <-> Teachers   (college_teacher)
|--------------------------------------------------------------------------
*/

Route::get('colleges/{college}/teachers', [CollegeController::class, 'teachers'])
    ->name('colleges.teachers.index');

Route::post('colleges/{college}/teachers', [CollegeController::class, 'attachTeacher'])
    ->name('colleges.teachers.store');

Route::delete('colleges/{college}/teachers/{teacher}', [CollegeController::class, 'detachTeacher'])
    ->name('colleges.teachers.destroy');

Route::get('teachers/{teacher}/colleges', [TeacherController::class, 'colleges'])
    ->name('teachers.colleges.index');

/*
|--------------------------------------------------------------------------
| Modules <-> Teachers   (module_teacher)
|--------------------------------------------------------------------------
*/

Route::get('teachers/{teacher}/modules', [TeacherController::class, 'modules'])
    ->name('teachers.modules.index');

Route::post('teachers/{teacher}/modules', [TeacherController::class, 'attachModule'])
    ->name('teachers.modules.store');

Route::delete('teachers/{teacher}/modules/{module}', [TeacherController::class, 'detachModule'])
    ->name('teachers.modules.destroy');

Route::get('modules/{module}/teachers', [ModuleController::class, 'teachers'])
    ->name('modules.teachers.index');

/*
|--------------------------------------------------------------------------
| Professions <-> Modules   (module_profession)
|--------------------------------------------------------------------------
*/

Route::get('professions/{profession}/modules', [ProfessionController::class, 'modules'])
    ->name('professions.modules.index');

Route::post('professions/{profession}/modules', [ProfessionController::class, 'attachModule'])
    ->name('professions.modules.store');

Route::delete('professions/{profession}/modules/{module}', [ProfessionController::class, 'detachModule'])
    ->name('professions.modules.destroy');

Route::get('modules/{module}/professions', [ModuleController::class, 'professions'])
    ->name('modules.professions.index');

/*
|--------------------------------------------------------------------------
| Professions -> Groups   (one-to-many)
|--------------------------------------------------------------------------
*/

Route::get('professions/{profession}/groups', [ProfessionController::class, 'groups'])
    ->name('professions.groups.index');

/*
|--------------------------------------------------------------------------
| Groups <-> Students   (group_student)
|--------------------------------------------------------------------------
*/

Route::get('groups/{group}/students', [GroupController::class, 'students'])
    ->name('groups.students.index');

Route::post('groups/{group}/students', [GroupController::class, 'enrollStudent'])
    ->name('groups.students.store');

Route::delete('groups/{group}/students/{student}', [GroupController::class, 'removeStudent'])
    ->name('groups.students.destroy');

Route::get('students/{student}/groups', [StudentController::class, 'groups'])
    ->name('students.groups.index');

/*
|--------------------------------------------------------------------------
| Students -> Modules   (groups-is gavlit)
|--------------------------------------------------------------------------
*/

Route::get('students/{student}/modules', [StudentController::class, 'modules'])
    ->name('students.modules.index');
