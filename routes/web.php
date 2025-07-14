<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Inertia\Inertia;

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
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::middleware([
    'tenant', // Requires tenant to be set
    'auth',   // Requires authentication
    'verified' // Requires email verification
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Include the settings routes
    require __DIR__.'/settings.php';
});


// Auth routes should remain accessible for tenants to log in
require __DIR__.'/auth.php';
