<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerPortalController extends Controller
{
    /**
     * Customer login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'account_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::where('email', $request->email)
                           ->where('account_number', $request->account_number)
                           ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Generate a simple token (in production, use JWT or Sanctum)
        $token = base64_encode($customer->id . '|' . time());

        return response()->json([
            'status' => 'success',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'account_number' => $customer->account_number,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'contact_number' => $customer->contact_number,
                    'status' => $customer->status,
                ],
                'access_token' => $token,
                'token_type' => 'bearer',
            ],
        ]);
    }

    /**
     * Get customer dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $totalInvoices = Invoice::where('customer_id', $customer->id)->count();
        $unpaidInvoices = Invoice::where('customer_id', $customer->id)
                                ->whereIn('status', ['sent', 'partial', 'overdue'])
                                ->count();
        $totalOutstanding = Invoice::where('customer_id', $customer->id)
                                  ->whereIn('status', ['sent', 'partial', 'overdue'])
                                  ->sum('balance');
        $lastPayment = Payment::where('customer_id', $customer->id)
                             ->latest('payment_date')
                             ->first();

        return response()->json([
            'customer' => $customer->load('servicePlan', 'router'),
            'stats' => [
                'total_invoices' => $totalInvoices,
                'unpaid_invoices' => $unpaidInvoices,
                'total_outstanding' => (float) $totalOutstanding,
                'last_payment' => $lastPayment ? [
                    'amount' => $lastPayment->amount,
                    'date' => $lastPayment->payment_date->format('Y-m-d'),
                    'method' => $lastPayment->payment_method,
                ] : null,
            ],
        ]);
    }

    /**
     * Get customer invoices
     */
    public function invoices(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $query = Invoice::with(['items', 'payments'])
                       ->where('customer_id', $customer->id);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest('issue_date')
                         ->paginate($request->get('per_page', 10));

        return response()->json($invoices);
    }

    /**
     * Get single invoice
     */
    public function invoice(Request $request, string $id): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $invoice = Invoice::with(['items', 'payments'])
                         ->where('customer_id', $customer->id)
                         ->findOrFail($id);

        return response()->json($invoice);
    }

    /**
     * Get customer payments
     */
    public function payments(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $payments = Payment::with('invoice')
                          ->where('customer_id', $customer->id)
                          ->latest('payment_date')
                          ->paginate($request->get('per_page', 10));

        return response()->json($payments);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $this->getAuthenticatedCustomer($request);

        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'gps_coordinates' => 'nullable|array',
            'gps_coordinates.latitude' => 'nullable|numeric',
            'gps_coordinates.longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update($request->only([
            'contact_number',
            'address',
            'gps_coordinates',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Get authenticated customer from token
     */
    protected function getAuthenticatedCustomer(Request $request): ?Customer
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        // Decode token (simple implementation - use JWT in production)
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);

        if (count($parts) !== 2) {
            return null;
        }

        $customerId = $parts[0];

        return Customer::find($customerId);
    }
}
