<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
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
    Route::view('/videos/playground', 'video.playground')->name('video.playground');
    Route::post('/videos/upload', [VideoController::class, 'uploadVideo'])->name('video.upload');
    Route::post('/videos/convert', [VideoController::class, 'convertToEncryptedHLS'])->name('video.convert');
    Route::post('/videos/view', [VideoController::class, 'setViewMode'])->name('video.viewmode');
    Route::get('/videos/progress', function () {
        $videos = auth()->user()->videos()->whereIn('status', ['queued','processing'])->get(['id','status','progress']);
        return response()->json(['data' => $videos]);
    })->name('video.progress');

    Route::post('/videos/export/generate', [VideoController::class, 'generateExportLink'])->name('video.export.generate');
    Route::post('/videos/export/revoke', [VideoController::class, 'revokeExportAccess'])->name('video.export.revoke');
    Route::get('/videos/{id}/export-link', [VideoController::class, 'exportLink'])->name('video.export.link');
    Route::get('/videos/{id}/embed-code', [VideoController::class, 'exportEmbed'])->name('video.export.embed');
    Route::post('/videos/update-meta', [VideoController::class, 'updateMeta'])->name('video.update.meta');
    Route::post('/videos/subtitle', [VideoController::class, 'uploadSubtitle'])->name('video.subtitle.upload');
    Route::post('/videos/chapters', [VideoController::class, 'uploadChapters'])->name('video.chapters.upload');
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

// Notification Channel routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notification-channels', App\Livewire\NotificationChannel\Index::class)->name('notification-channel.index');
    Route::get('/notification-channels/create', App\Livewire\NotificationChannel\Form::class)->name('notification-channel.create');
    Route::get('/notification-channels/{id}/edit', App\Livewire\NotificationChannel\Form::class)->name('notification-channel.edit');
    // WA Unofficial utilities
    Route::get('/wa/session', App\Livewire\Wa\Session::class)->name('wa.session');
    Route::get('/wa/automation', App\Livewire\Wa\Automation::class)->name('wa.automation');
    Route::view('/wa/docs', 'wa.docs')->name('wa.docs');

    // Broadcasts
    Route::get('/broadcasts', App\Livewire\Broadcast\Form::class)->name('broadcast.form');
    Route::get('/broadcasts/history', App\Livewire\Broadcast\Index::class)->name('broadcast.index');
    Route::get('/broadcasts/{id}', App\Livewire\Broadcast\Show::class)->name('broadcast.show');

    // Customer Groups
    Route::get('/customer-groups', App\Livewire\CustomerGroup\Index::class)->name('customer-group.index');
    Route::get('/customer-groups/create', App\Livewire\CustomerGroup\Form::class)->name('customer-group.create');
    Route::get('/customer-groups/{id}/edit', App\Livewire\CustomerGroup\Form::class)->name('customer-group.edit');

    // Customers
    Route::get('/customers', App\Livewire\Customer\Index::class)->name('customer.index');
    Route::get('/customers/create', App\Livewire\Customer\Form::class)->name('customer.create');
    Route::get('/customers/{id}/edit', App\Livewire\Customer\Form::class)->name('customer.edit');
    Route::get('/customers/import', App\Livewire\Customer\Import::class)->name('customer.import');
});
