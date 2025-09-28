<?php

use Idoneo\HumanoMailer\Http\Controllers\MessageController;
use Idoneo\HumanoMailer\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Messages
    Route::get('message/list', [MessageController::class, 'index'])->name('message-list');
    Route::get('message/create', [MessageController::class, 'create'])->name('message.create');
    Route::get('message/{id}', [MessageController::class, 'show'])->name('message.show');
    Route::get('message/{id}/edit', [MessageController::class, 'edit'])->name('message.edit');
    Route::get('message/{id}/preview', [MessageController::class, 'preview'])->name('message.preview');
    Route::post('message/{id}/start', [MessageController::class, 'startCampaign'])->name('message.start');
    Route::post('message/{id}/pause', [MessageController::class, 'pauseCampaign'])->name('message.pause');
    Route::post('message/{id}/test', [MessageController::class, 'testSend'])->name('message.test');
    Route::get('message/{id}/link-details/{encodedLink}', [MessageController::class, 'getLinkDetails'])->name('message.link-details');
    Route::post('message', [MessageController::class, 'store'])->name('message.store');
    Route::put('message/{id}', [MessageController::class, 'update'])->name('message.update');
    Route::delete('message/{id}', [MessageController::class, 'destroy'])->name('message.destroy');

    Route::get('/send-sms', [MessageController::class, 'sendSmsMessage']);
    Route::get('/send-whatsapp', [MessageController::class, 'sendWhatsAppMessage']);

    // Templates
    Route::get('template/list', [TemplateController::class, 'index'])->name('template.index');
    Route::get('template/create', [TemplateController::class, 'create'])->name('template.create');
    Route::post('template', [TemplateController::class, 'store'])->name('template.store');
    Route::get('template/{hashedId}', [TemplateController::class, 'show'])->name('template.show');
    Route::get('template/{hashedId}/edit', [TemplateController::class, 'edit'])->name('template.edit');
    Route::get('template/{hashedId}/editor', [TemplateController::class, 'editor'])->name('template.editor');
    Route::delete('template/{hashedId}', [TemplateController::class, 'destroy'])->name('template.destroy');
});

// Public routes (no authentication required)
Route::get('/unsubscribe/{email}', [MessageController::class, 'unsubscribe']);
