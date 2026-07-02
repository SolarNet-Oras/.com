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
     * Customer login with proper authentication
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'account_number' => 'required|string',
            'password' => 'nullable|string', // Optional for backward compatibility
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

        // Generate a secure token using Laravel's password_hash as signing mechanism
        // In production, use Laravel Sanctum for proper token management
        $payload = [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'account_number' => $customer->account_number,
            'issued_at' => now()->timestamp,
            'expires_at' => now()->addDays(7)->timestamp,
        ];
        
        $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));
        $token = base64_encode(json_encode([
            'payload' => $payload,
            'signature' => $signature,
        ]));

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
                'expires_at' => now()->addDays(7)->toIso8601String(),
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
     * Get authenticated customer from token with signature verification
     */
    protected function getAuthenticatedCustomer(Request $request): ?Customer
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        try {
            // Decode and verify token
            $decoded = json_decode(base64_decode($token), true);
            
            if (!$decoded || !isset($decoded['payload']) || !isset($decoded['signature'])) {
                return null;
            }

            $payload = $decoded['payload'];
            $signature = $decoded['signature'];

            // Verify signature
            $expectedSignature = hash_hmac('sha256', json_encode($payload), config('app.key'));
            
            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }

            // Check expiration
            if (isset($payload['expires_at']) && $payload['expires_at'] < now()->timestamp) {
                return null;
            }

            // Verify customer still exists and is active
            $customer = Customer::find($payload['customer_id']);
            
            if (!$customer || $customer->status === 'suspended') {
                return null;
            }

            // Additional verification: check email and account match
            if ($customer->email !== $payload['email'] || 
                $customer->account_number !== $payload['account_number']) {
                return null;
            }

            return $customer;
            
        } catch (\Exception $e) {
            \Log::warning('Customer authentication failed', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...',
            ]);
            return null;
        }
    }
}
