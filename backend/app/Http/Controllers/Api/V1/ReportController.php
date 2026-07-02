<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Revenue report
     */
    public function revenue(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subMonth()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $revenue = Payment::whereBetween('payment_date', [$startDate, $endDate])
                         ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                         ->groupBy('date')
                         ->orderBy('date')
                         ->get();

        $totalRevenue = (float) Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $invoiceCount = Invoice::whereBetween('issue_date', [$startDate, $endDate])->count();
        $averageInvoice = $invoiceCount > 0 ? round($totalRevenue / $invoiceCount, 2) : 0;

        return response()->json([
            'daily_revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'invoice_count' => $invoiceCount,
            'average_invoice' => $averageInvoice,
            'period' => ['start' => $startDate, 'end' => $endDate],
        ]);
    }

    /**
     * Customer growth report
     */
    public function customerGrowth(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subMonths(6));
        $endDate = $request->get('end_date', now());

        $growth = Customer::whereBetween('created_at', [$startDate, $endDate])
                         ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
                         ->groupBy('month')
                         ->orderBy('month')
                         ->get();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'suspended' => Customer::where('status', 'suspended')->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
        ];

        return response()->json([
            'growth' => $growth,
            'stats' => $stats,
        ]);
    }

    /**
     * Payment methods breakdown
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $methods = Payment::whereBetween('payment_date', [$startDate, $endDate])
                         ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                         ->groupBy('payment_method')
                         ->get();

        return response()->json($methods);
    }

    /**
     * Service plan popularity
     */
    public function servicePlanPopularity(): JsonResponse
    {
        $plans = Customer::join('service_plans', 'customers.service_plan_id', '=', 'service_plans.id')
                        ->selectRaw('service_plans.name, service_plans.price, COUNT(customers.id) as customer_count')
                        ->groupBy('service_plans.id', 'service_plans.name', 'service_plans.price')
                        ->orderBy('customer_count', 'desc')
                        ->get();

        return response()->json($plans);
    }

    /**
     * Support tickets overview
     */
    public function ticketsOverview(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $stats = [
            'total' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_status' => Ticket::whereBetween('created_at', [$startDate, $endDate])
                                ->selectRaw('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->get(),
            'by_category' => Ticket::whereBetween('created_at', [$startDate, $endDate])
                                  ->selectRaw('category, COUNT(*) as count')
                                  ->groupBy('category')
                                  ->get(),
            'avg_resolution_time' => Ticket::whereNotNull('resolved_at')
                                          ->whereBetween('created_at', [$startDate, $endDate])
                                          ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600) as hours')
                                          ->value('hours'),
        ];

        return response()->json($stats);
    }
}
