<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== PUBLIC API ROUTES (No Authentication) ====================

// Public Room API (for website)
Route::prefix('rooms')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\RoomApiController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\Api\RoomApiController::class, 'show']);
});

// Public Bookings API (for website)
Route::prefix('bookings')->group(function () {
    Route::get('/available-rooms', [App\Http\Controllers\Hotel\BookingController::class, 'availableRoomsApi']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [App\Http\Controllers\Hotel\BookingController::class, 'createOnlineBookingApi']);
        Route::get('/', [App\Http\Controllers\Hotel\BookingController::class, 'getMyBookingsApi']);
        Route::get('/{booking}', [App\Http\Controllers\Hotel\BookingController::class, 'getMyBookingByIdApi']);
        Route::get('/{booking}/receipt', [App\Http\Controllers\Hotel\BookingController::class, 'downloadReceiptApi']);
        Route::post('/{booking}/cancel', [App\Http\Controllers\Hotel\BookingController::class, 'cancelBookingApi']);
    });
});

// Company Settings API (for website)
Route::prefix('settings')->group(function () {
    Route::get('/company', [App\Http\Controllers\SettingsController::class, 'getCompanySettingsApi']);
});

// Guest Authentication API (for website)
Route::prefix('guest')->group(function () {
    Route::post('/register', [App\Http\Controllers\Api\GuestApiController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Api\GuestApiController::class, 'login']);
    Route::get('/bank-accounts', [App\Http\Controllers\Api\GuestApiController::class, 'getBankAccounts']); // Public endpoint for bank accounts
    Route::get('/branches', [App\Http\Controllers\Api\GuestApiController::class, 'getBranches']); // Public endpoint for branches
    Route::post('/messages', [App\Http\Controllers\Api\GuestApiController::class, 'sendMessage']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [App\Http\Controllers\Api\GuestApiController::class, 'me']);
        Route::put('/profile', [App\Http\Controllers\Api\GuestApiController::class, 'updateProfile']);
        Route::get('/messages', [App\Http\Controllers\Api\GuestApiController::class, 'getMyMessages']);
        Route::post('/logout', [App\Http\Controllers\Api\GuestApiController::class, 'logout']);
    });
});

// Milipuko mobile app (hrapp)
Route::prefix('milipuko')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\MilipukoMobileController::class, 'login']);
    Route::post('/login/pin', [App\Http\Controllers\Api\MilipukoMobileController::class, 'loginPin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\MilipukoMobileController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Api\MilipukoMobileController::class, 'me']);
        Route::get('/fomu', [App\Http\Controllers\Api\MilipukoMobileController::class, 'fomu']);
        Route::post('/vibali', [App\Http\Controllers\Api\MilipukoMobileController::class, 'storeKibali']);
        Route::get('/walipuaji', [App\Http\Controllers\Api\MilipukoMobileController::class, 'walipuaji']);
        Route::post('/walipuaji/{mlipuzi}/picha', [App\Http\Controllers\Api\MilipukoMobileController::class, 'picha']);
        Route::get('/scan', [App\Http\Controllers\Api\MilipukoMobileController::class, 'scan']);
        Route::post('/vibali/tumia', [App\Http\Controllers\Api\MilipukoMobileController::class, 'tumia']);
    });
});

// SmartPOS Mobile App (Accountant & Admin)
Route::prefix('smartpos')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\SmartPosMobileController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\SmartPosMobileController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Api\SmartPosMobileController::class, 'me']);
        Route::get('/dashboard', [App\Http\Controllers\Api\SmartPosMobileController::class, 'dashboard']);
        Route::get('/bills', [App\Http\Controllers\Api\SmartPosMobileController::class, 'bills']);
        Route::post('/bills/{encodedId}/pay', [App\Http\Controllers\Api\SmartPosMobileController::class, 'payBill']);
        Route::get('/bank-accounts', [App\Http\Controllers\Api\SmartPosMobileController::class, 'bankAccounts']);
    });
});

// ==================== PROTECTED API ROUTES (Require Authentication) ====================

Route::middleware('auth:sanctum')->group(function () {
    
    // ==================== TEACHER ROUTES (Future Implementation) ====================
    Route::prefix('teacher')->group(function () {
        // Will be implemented when teacher mobile app is needed
    });
    
    // ==================== ADMIN ROUTES (Future Implementation) ====================
    Route::prefix('admin')->group(function () {
        // Will be implemented when admin mobile app is needed
    });
});

// ==================== WEBHOOK ROUTES (No Authentication Required) ====================
Route::prefix('webhooks')->group(function () {
    Route::post('/lipisha', [App\Http\Controllers\WebhookController::class, 'lipisha']);
});
