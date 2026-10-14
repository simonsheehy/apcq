<?php

use App\Http\Controllers\BoardMemberController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Middleware\EnsureEmployeeIsActive;
use App\Livewire\CinemaValidation;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/nouvelles', [PostController::class, 'index'])->name('posts.index');
Route::get('/nouvelles/article/{slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/nouvelles/{tag:slug}', [PostController::class, 'byTag'])->name('posts.by-tag');
Route::get('/membres', [MemberController::class, 'index'])->name('members.index');
Route::get('/conseil-administration', [BoardMemberController::class, 'index'])->name('board-members.index');
Route::get('/conseil-administration/{slug}', [BoardMemberController::class, 'show'])->name('board-members.show');
Route::get('/equipe', [TeamMemberController::class, 'index'])->name('team-members.index');
Route::get('/equipe/{slug}', [TeamMemberController::class, 'show'])->name('team-members.show');
Route::get('/evenements', [EventController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/partenaires', [PartnerController::class, 'index'])->name('partners.index');
Route::get('/a-propos', [PageController::class, 'show'])->defaults('slug', 'a-propos')->name('about');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/contact', ContactController::class)->name('contact');
Route::get('/validation/{token}', CinemaValidation::class)
    ->middleware('throttle:180,1')
    ->name('cinemas.validation');
    
Route::prefix('formation')->name('portal.')->group(function (): void {
    Route::middleware('guest:employee')->group(function (): void {
        Route::get('/connexion', [Portal\LoginController::class, 'create'])->name('login');
        Route::post('/connexion', [Portal\LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
        Route::get('/mot-de-passe-oublie', [Portal\ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('/mot-de-passe-oublie', [Portal\ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
        Route::get('/reinitialiser/{token}', [Portal\ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reinitialiser', [Portal\ResetPasswordController::class, 'store'])->middleware('throttle:10,1')->name('password.update');
        Route::get('/invitation/{employee}', [Portal\InvitationController::class, 'show'])->middleware('signed')->name('invitation.show');
        Route::post('/invitation/{employee}', [Portal\InvitationController::class, 'store'])->middleware(['signed', 'throttle:10,1'])->name('invitation.store');
    });

    Route::middleware(['auth:employee', EnsureEmployeeIsActive::class])->group(function (): void {
        Route::get('/', Portal\HomeController::class)->name('home');
        Route::post('/deconnexion', [Portal\LoginController::class, 'destroy'])->name('logout');
    });
});
