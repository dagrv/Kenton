<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Tags
Route::get('/tags', \App\Http\Controllers\TagController::class);

// Offices
Route::get('/offices', [\App\Http\Controllers\OfficeController::class, 'index']);