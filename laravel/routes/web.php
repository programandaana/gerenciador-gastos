<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotaFiscal\DeleteNotaFiscalController;
use App\Http\Controllers\NotaFiscal\ListarNotasFiscaisController;
use App\Http\Controllers\NotaFiscal\ReadNotaFiscal;
use App\Http\Controllers\NotaFiscal\UploadReceiptController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobStatusController; // Adicionado

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

Route::delete('receipt/{id}', DeleteNotaFiscalController::class)->name('receipt.delete');

Route::get('notas-fiscais', ListarNotasFiscaisController::class)
    ->name('view.receipt.list');

// Job Status API
Route::get('job-status/{uuid}', [JobStatusController::class, 'show'])->name('job.status.show');
Route::get('job-status', [JobStatusController::class, 'index'])->name('job.status.index');
Route::delete('job-status/{uuid}', [JobStatusController::class, 'destroy'])->name('job.status.destroy');

// Relatórios
Route::get('notas-fiscais/{id}', [ReadNotaFiscal::class, '__invoke'])
    ->name('view.receipt.read');
