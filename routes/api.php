<?php
// routes/api.php

use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// KirimDev webhook — tidak butuh auth middleware
// Keamanan dijaga via HMAC signature verification di controller
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'handle']);
