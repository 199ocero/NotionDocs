<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NotionOAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'check.notion.token'])->group(function () {
    Route::get('/login/notion', [NotionOAuthController::class, 'redirectToProvider'])->name('login.notion');
    Route::get('/login/notion/callback', [NotionOAuthController::class, 'handleProviderCallback']);
});