<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('quiz.index') : redirect()->route('login');
});
Route::middleware(['auth'])
    ->controller(QuizController::class)
    ->name('quiz.')
    ->prefix('quiz')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/questions/{category}', 'getQuestions')->name('questions');
        Route::post('/check-answer', 'checkAnswer')->name('check');
    });

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__ . '/auth.php';
