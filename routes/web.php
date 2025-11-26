<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Models\Transaction;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página inicial
Route::get('/', function () {
    return view('welcome');
});

// DASHBOARD → Agora usa o CONTROLLER corretamente
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [WalletController::class, 'dashboard'])
        ->name('dashboard');

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /** Financeiro **/
    Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
    Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
    Route::post('/reverse/{id}', [WalletController::class, 'reverse'])->name('reverse');

    /** Extrato **/
    Route::get('/transactions', [WalletController::class, 'transactionsPage'])
        ->name('transactions.index');

    Route::get('/transactions/filter', [WalletController::class, 'filterTransactions'])
        ->name('transactions.filter');

});

require __DIR__ . '/auth.php';
