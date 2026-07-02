<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CustomerPortalController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\RouterController;
use App\Http\Controllers\Api\V1\ServicePlanController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'ISP Billing API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// V1 API Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/status', function () {
        return response()->json([
            'message' => 'API v1 is operational',
            'laravel_version' => app()->version(),
        ]);
    });

    // Authentication routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        
        // Protected auth routes
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Protected routes (require authentication)
    Route::middleware('auth:api')->group(function () {
        // Dashboard routes
        Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);
        Route::get('dashboard/quick-stats', [DashboardController::class, 'quickStats']);
        
        // User routes
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/roles', [UserController::class, 'assignRoles']);
        
        // Role routes
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::get('permissions', [RoleController::class, 'permissions']);
        
        // Customer routes
        Route::apiResource('customers', CustomerController::class);
        Route::get('customers-statistics', [CustomerController::class, 'statistics']);
        Route::post('customers/{id}/sync-queue', [CustomerController::class, 'syncQueue']);
        Route::post('customers/bulk-sync-queues', [CustomerController::class, 'bulkSyncQueues']);
        
        // Router routes (MikroTik)
        Route::apiResource('routers', RouterController::class);
        Route::post('routers/{id}/test-connection', [RouterController::class, 'testConnection']);
        Route::post('routers/{id}/sync', [RouterController::class, 'sync']);
        Route::get('routers/{id}/setup-script', [RouterController::class, 'generateSetupScript']);
        Route::get('routers/scripts/queue-management', [RouterController::class, 'getQueueScript']);
        Route::post('routers/{id}/sync-dhcp', [RouterController::class, 'syncDhcpLeases']);
        Route::get('routers/{id}/unmatched-leases', [RouterController::class, 'getUnmatchedLeases']);
        
        // Service Plans routes
        Route::apiResource('service-plans', ServicePlanController::class);
        
        // Invoice routes
        Route::apiResource('invoices', InvoiceController::class);
        Route::get('invoices-statistics', [InvoiceController::class, 'statistics']);
        Route::post('invoices/{id}/mark-sent', [InvoiceController::class, 'markAsSent']);
        Route::post('invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
        Route::get('invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf']);
        Route::post('invoices/generate-recurring', [InvoiceController::class, 'generateRecurring']);
        
        // Payment routes
        Route::get('payments', [PaymentController::class, 'index']);
        Route::get('payments/{id}', [PaymentController::class, 'show']);
        Route::get('payments-statistics', [PaymentController::class, 'statistics']);
    });

    // Customer Portal Routes (separate auth)
    Route::prefix('customer-portal')->group(function () {
        // Public customer login
        Route::post('login', [CustomerPortalController::class, 'login']);

        // Protected customer routes
        Route::middleware('api')->group(function () {
            Route::get('dashboard', [CustomerPortalController::class, 'dashboard']);
            Route::get('invoices', [CustomerPortalController::class, 'invoices']);
            Route::get('invoices/{id}', [CustomerPortalController::class, 'invoice']);
            Route::get('payments', [CustomerPortalController::class, 'payments']);
            Route::put('profile', [CustomerPortalController::class, 'updateProfile']);
        });
    });
});
