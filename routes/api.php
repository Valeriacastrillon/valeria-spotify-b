<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\UserController;


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('do_login', [LoginController::class, 'do_login'])->name('api.auth.do_login');
    Route::get('logout', [LoginController::class, 'logout'])->name('api.auth.logout');
    Route::post('do_register', [LoginController::class, 'do_register'])->name('api.auth.do_register');
});




Route::middleware(['jwt.auth'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('list', [UserController::class, 'list'])->name('api.users.list');
        Route::post('create/', [UserController::class, 'store'])->name('api.users.store');
        Route::get('show/{id}', [UserController::class, 'show'])->name('api.users.show');
        Route::put('edit/{id}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('delete/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
    });
});