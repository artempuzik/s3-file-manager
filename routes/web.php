<?php

use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('file-manager');
});

Route::prefix('file-manager')->group(function () {
    Route::get('/', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::post('/create-folder', [FileManagerController::class, 'createFolder'])->name('file-manager.create-folder');
    Route::post('/upload-file', [FileManagerController::class, 'uploadFile'])->name('file-manager.upload-file');
    Route::post('/delete-file', [FileManagerController::class, 'deleteFile'])->name('file-manager.delete-file');
    Route::get('/download-file/{path}', [FileManagerController::class, 'downloadFile'])->name('file-manager.download-file');
    Route::get('/get-url/{path}', [FileManagerController::class, 'getFileUrl'])->name('file-manager.get-url');
});
