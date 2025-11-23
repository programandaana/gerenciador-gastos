<?php

use App\Http\Controllers\UploadReceiptController;
use Illuminate\Support\Facades\Route;

/*
 * Views
 */
Route::get('/', fn() => view('home'))->name('view.home');
Route::get('receipt/upload', fn() => view('receipt.upload'))->name('view.receipt.upload');

/*
 * Controllers
 */
Route::post('receipt', UploadReceiptController::class)->name('receipt.upload');
