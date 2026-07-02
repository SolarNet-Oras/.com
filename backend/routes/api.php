<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CustomerPortalController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\HsgqOltController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\RouterController;
use App\Http\Controllers\Api\V1\ServicePlanController;
use App\Http\Controllers\Api\V1\TicketController;
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
        
        // User routes (admin only)
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('users/{user}/roles', [UserController::class, 'assignRoles']);
        });
        
        // Role routes (admin only)
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
            Route::get('permissions', [RoleController::class, 'permissions']);
        });
        
        // Customer routes (require permission)
        Route::middleware(['permission:manage_customers'])->group(function () {
            Route::apiResource('customers', CustomerController::class);
            Route::get('customers-statistics', [CustomerController::class, 'statistics']);
            Route::post('customers/{id}/sync-queue', [CustomerController::class, 'syncQueue']);
            Route::post('customers/bulk-sync-queues', [CustomerController::class, 'bulkSyncQueues']);
        });
        
        // Router routes (MikroTik) - require permission
        Route::middleware(['permission:manage_routers'])->group(function () {
            Route::apiResource('routers', RouterController::class);
            Route::post('routers/{id}/test-connection', [RouterController::class, 'testConnection']);
            Route::post('routers/{id}/sync', [RouterController::class, 'sync']);
            Route::get('routers/{id}/setup-script', [RouterController::class, 'generateSetupScript']);
            Route::get('routers/scripts/queue-management', [RouterController::class, 'getQueueScript']);
            Route::post('routers/{id}/sync-dhcp', [RouterController::class, 'syncDhcpLeases']);
            Route::get('routers/{id}/unmatched-leases', [RouterController::class, 'getUnmatchedLeases']);
        });
        
        // Service Plans routes (require permission)
        Route::middleware(['permission:manage_plans'])->group(function () {
            Route::apiResource('service-plans', ServicePlanController::class);
        });
        
        // Invoice routes (require permission)
        Route::middleware(['permission:manage_billing|view_billing'])->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index']);
            Route::get('invoices/{id}', [InvoiceController::class, 'show']);
            Route::get('invoices-statistics', [InvoiceController::class, 'statistics']);
            Route::get('invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf']);
        });
        
        Route::middleware(['permission:manage_billing'])->group(function () {
            Route::post('invoices', [InvoiceController::class, 'store']);
            Route::put('invoices/{id}', [InvoiceController::class, 'update']);
            Route::delete('invoices/{id}', [InvoiceController::class, 'destroy']);
            Route::post('invoices/{id}/mark-sent', [InvoiceController::class, 'markAsSent']);
            Route::post('invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
            Route::post('invoices/generate-recurring', [InvoiceController::class, 'generateRecurring']);
        });
        
        // Payment routes (require permission)
        Route::middleware(['permission:manage_billing|view_billing'])->group(function () {
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('payments/{id}', [PaymentController::class, 'show']);
            Route::get('payments-statistics', [PaymentController::class, 'statistics']);
        });
        
        // Ticket routes (require permission)
        Route::middleware(['permission:manage_tickets|view_tickets'])->group(function () {
            Route::get('tickets', [TicketController::class, 'index']);
            Route::get('tickets/{id}', [TicketController::class, 'show']);
            Route::get('tickets-statistics', [TicketController::class, 'statistics']);
        });
        
        Route::middleware(['permission:manage_tickets'])->group(function () {
            Route::post('tickets', [TicketController::class, 'store']);
            Route::put('tickets/{id}', [TicketController::class, 'update']);
            Route::delete('tickets/{id}', [TicketController::class, 'destroy']);
            Route::post('tickets/{id}/assign', [TicketController::class, 'assign']);
            Route::post('tickets/{id}/comments', [TicketController::class, 'addComment']);
            Route::patch('tickets/{id}/status', [TicketController::class, 'updateStatus']);
        });
        
        // Report routes
        Route::get('reports/revenue', [ReportController::class, 'revenue']);
        Route::get('reports/customer-growth', [ReportController::class, 'customerGrowth']);
        Route::get('reports/payment-methods', [ReportController::class, 'paymentMethods']);
        Route::get('reports/service-plans', [ReportController::class, 'servicePlanPopularity']);
        Route::get('reports/tickets', [ReportController::class, 'ticketsOverview']);
        
        // HSGQ OLT routes
        Route::get('hsgq-olt', [HsgqOltController::class, 'index']);
        Route::get('hsgq-olt/{oltId}/onts', [HsgqOltController::class, 'getOnts']);
        Route::post('hsgq-olt/{oltId}/discover', [HsgqOltController::class, 'discoverOnts']);
        Route::post('hsgq-olt/{oltId}/onts/{ontId}/authorize', [HsgqOltController::class, 'authorizeOnt']);
        Route::post('hsgq-olt/{oltId}/onts/{ontId}/reboot', [HsgqOltController::class, 'rebootOnt']);
        Route::get('hsgq-olt/{oltId}/onts/{ontId}/statistics', [HsgqOltController::class, 'getOntStatistics']);
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
