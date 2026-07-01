<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard metrics
     */
    public function metrics(): JsonResponse
    {
        // For now, we'll return mock data since we don't have customers, routers, etc. yet
        // These will be replaced with real queries as we build out the system
        
        $metrics = [
            // Subscriber metrics
            'active_subscribers' => 0, // Will be: Customer::where('status', 'active')->count()
            'expired_subscribers' => 0, // Will be: Customer::where('status', 'expired')->count()
            'suspended_subscribers' => 0, // Will be: Customer::where('status', 'suspended')->count()
            'total_subscribers' => 0, // Will be: Customer::count()
            
            // Connection status (mock data)
            'online_users' => 0, // Will be from DHCP leases or RouterOS
            'offline_users' => 0,
            
            // Financial metrics (mock data)
            'today_revenue' => 0.00, // Will be: Payment::whereDate('paid_at', today())->sum('amount')
            'monthly_revenue' => 0.00, // Will be: Payment::whereMonth('paid_at', now()->month)->sum('amount')
            'pending_payments' => 0, // Will be: Invoice::where('status', 'pending')->count()
            'overdue_invoices' => 0, // Will be: Invoice::where('status', 'overdue')->count()
            
            // Ticket metrics (mock data)
            'open_tickets' => 0, // Will be: Ticket::where('status', 'open')->count()
            'pending_tickets' => 0, // Will be: Ticket::where('status', 'pending')->count()
            'resolved_today' => 0, // Will be: Ticket::whereDate('resolved_at', today())->count()
            
            // Router metrics (mock data)
            'router_status' => [
                'online' => 0, // Will be: Router::where('status', 'online')->count()
                'offline' => 0, // Will be: Router::where('status', 'offline')->count()
                'error' => 0, // Will be: Router::where('status', 'error')->count()
                'total' => 0, // Will be: Router::count()
            ],
            
            // System metrics
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'users_online' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subMinutes(15))
                ->count(),
            
            // Recent activity (mock data for now)
            'recent_signups' => User::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'email', 'created_at']),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email', 'last_login_at']),
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $metrics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get quick stats for widgets
     */
    public function quickStats(): JsonResponse
    {
        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::count(),
                'change' => '+12%',
                'trend' => 'up',
                'icon' => 'users',
            ],
            [
                'label' => 'Active Sessions',
                'value' => User::where('is_active', true)->count(),
                'change' => '+5%',
                'trend' => 'up',
                'icon' => 'activity',
            ],
            [
                'label' => 'System Health',
                'value' => '99.9%',
                'change' => 'Stable',
                'trend' => 'stable',
                'icon' => 'heart',
            ],
            [
                'label' => 'API Requests',
                'value' => '1.2K',
                'change' => '+8%',
                'trend' => 'up',
                'icon' => 'zap',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
