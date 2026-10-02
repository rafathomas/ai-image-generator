<?php

use App\Http\Controllers\Api\PhotoEditController;
use Illuminate\Support\Facades\Route;

Route::apiResource('photo-edits', PhotoEditController::class)->only(['index', 'store', 'show']);
