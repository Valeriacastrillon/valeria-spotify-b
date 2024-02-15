<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('users')->group(function () {
    Route::get('list', [UserController::class, 'list'])->name('api.users.list');
    Route::post('store/', [UserController::class, 'store'])->name('api.users.store');
    Route::get('show/{id}', [UserController::class, 'show'])->name('api.users.show');
    Route::put('update/{id}', [UserController::class, 'update'])->name('api.users.update');
    Route::delete('destroy/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
});

Route::prefix('auth')->group(function () {
    Route::post('do_login', [LoginController::class, 'do_login'])->name('api.auth.do_login');
    Route::get('logout', [LoginController::class, 'logout'])->name('api.auth.logout');
    Route::post('do_register', [LoginController::class, 'do_register'])->name('api.auth.do_register');
});