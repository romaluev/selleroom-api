<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;


Route::post('sign-up', [RegisteredUserController::class, 'store']);
Route::get('login', [RegisteredUserController::class, 'login']);
