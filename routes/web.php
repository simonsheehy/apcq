<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/nouvelles', [PostController::class, 'index'])->name('posts.index');
Route::get('/nouvelles/{slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/membres', [MemberController::class, 'index'])->name('members.index');
Route::get('/partenaires', [PartnerController::class, 'index'])->name('partners.index');
Route::get('/a-propos', [PageController::class, 'show'])->defaults('slug', 'a-propos')->name('about');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/contact', ContactController::class)->name('contact');
