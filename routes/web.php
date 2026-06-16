<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GolfCourseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('golf-courses.index'));

// ログイン（未認証のみ）
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// 認証必須エリア
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // 削除済み一覧・復元・完全削除（モデルバインディングを使わず onlyTrashed で検索）
    Route::get('/golf-courses/trashed',      [GolfCourseController::class, 'trashed'])->name('golf-courses.trashed');
    Route::post('/golf-courses/{id}/restore', [GolfCourseController::class, 'restore'])
        ->whereNumber('id')
        ->name('golf-courses.restore');
    Route::delete('/golf-courses/{id}/force', [GolfCourseController::class, 'forceDestroy'])
        ->whereNumber('id')
        ->name('golf-courses.force-destroy');

    // 削除確認画面
    Route::get('/golf-courses/{golfCourse}/delete',
        [GolfCourseController::class, 'confirmDelete'])->name('golf-courses.confirm-delete');

    // 通常 CRUD（URLパラメータ名を camelCase に合わせる）
    Route::resource('golf-courses', GolfCourseController::class)
        ->parameters(['golf-courses' => 'golfCourse']);
});
