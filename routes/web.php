<?php
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('images.index');
});

Route::get('images', [ImageController::class, 'index'])->name('images.index');
Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
Route::post('images', [ImageController::class, 'store'])->name('images.store');
Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
route::get('/analyze-image/{filename}', [ImageController::class, 'analyzeImage'])->name('images.analyze');
route::get('images/{id}/analyze', [ImageController::class, 'analyze'])->name('images.analyze');