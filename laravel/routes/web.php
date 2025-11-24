<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotaFiscal\ListarNotasFiscaisController;
use App\Http\Controllers\NotaFiscal\ReadNotaFiscal;
use App\Http\Controllers\NotaFiscal\UploadReceiptController;
use Illuminate\Support\Facades\Route;

/*
 * Views
 */
Route::get('/', HomeController::class)
    ->name('view.home');
Route::get('receipt/upload', fn() => view('receipt.upload'))
    ->name('view.receipt.upload');

/*
 * Controllers
 */
// Receipt
Route::post('receipt', UploadReceiptController::class)
    ->name('receipt.upload');

Route::get('notas-fiscais', ListarNotasFiscaisController::class)
    ->name('view.receipt.list');

Route::get('notas-fiscais/{id}', [ReadNotaFiscal::class, '__invoke'])
    ->name('view.receipt.read');
