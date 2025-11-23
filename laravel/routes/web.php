<?php

use App\Http\Controllers\NotaFiscal\ListarNotasFiscaisController;
use App\Http\Controllers\NotaFiscal\UploadReceiptController;
use Illuminate\Support\Facades\Route;

/*
 * Views
 */
Route::get('/', fn() => view('home'))->name('view.home');
Route::get('receipt/upload', fn() => view('receipt.upload'))
    ->name('view.receipt.upload');


/*
 * Controllers
 */
// Receipt
Route::post('receipt', UploadReceiptController::class)->name('receipt.upload');
Route::get('notas-fiscais', ListarNotasFiscaisController::class)
    ->name('view.receipt.list');
