<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\VideoController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';

// Video routes
Route::middleware(['auth'])->group(function () {
    Route::get('/videos', [VideoController::class, 'index'])->name('video.index');
    Route::get('/videos/create', [VideoController::class, 'create'])->name('video.create');
    Route::post('/videos/upload', [VideoController::class, 'uploadVideo'])->name('video.upload');
    Route::post('/videos/convert', [VideoController::class, 'convertToEncryptedHLS'])->name('video.convert');

    Route::post('/videos/export/generate', [VideoController::class, 'generateExportLink'])->name('video.export.generate');
    Route::post('/videos/export/revoke', [VideoController::class, 'revokeExportAccess'])->name('video.export.revoke');
    Route::get('/videos/{id}/export-link', [VideoController::class, 'exportLink'])->name('video.export.link');
    Route::get('/videos/{id}/embed-code', [VideoController::class, 'exportEmbed'])->name('video.export.embed');
    Route::post('/videos/update-meta', [VideoController::class, 'updateMeta'])->name('video.update.meta');
});

// HLS serving and players
Route::get('/video/player/{playlist?}', [VideoController::class, 'showPlayer'])->name('video.player');
Route::get('/video/playlist/{playlist}', [VideoController::class, 'servePlaylist'])->name('video.playlist');
Route::get('/video/key/{key}', [VideoController::class, 'serveKey'])->name('video.key');

// Public token-access endpoints
Route::get('/v/{token}', [VideoController::class, 'showExportVideo'])->name('video.export.show');
Route::get('/e/{token}', [VideoController::class, 'showEmbedVideo'])->name('video.embed.show');

Route::get('/hls/{token}', [VideoController::class, 'servePlaylistWithToken'])->name('video.playlist.token');
Route::get('/hls/{token}/playlist/{filename}', [VideoController::class, 'serveSegmentPlaylist'])->name('video.segment.token');
Route::get('/hls/{token}/key/{key}', [VideoController::class, 'serveKeyWithToken'])->name('video.key.token');
Route::get('/hls/{token}/segment/{filename}', [VideoController::class, 'serveSegmentFile'])->name('video.segment.file');

Route::post('/analytics/{token}', [VideoController::class, 'recordAnalytics'])->name('video.analytics');
