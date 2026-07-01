<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Display a listing of customers
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        
        $query = Customer::with(['technician:id,name', 'servicePlan:id,name']);
        
        // Search
        if ($search) {
            $query->search($search);
        }
        
        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }
        
        // Sort
        $query->orderBy('created_at', 'desc');
        
        $customers = $query->paginate($perPage);
        
        return response()->json([
            'status' => 'success',
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'from' => $customers->firstItem(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'to' => $customers->lastItem(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string|unique:customers,account_number',
            'full_name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_coordinates' => 'nullable|array',
            'gps_coordinates.latitude' => 'required_with:gps_coordinates|numeric',
            'gps_coordinates.longitude' => 'required_with:gps_coordinates|numeric',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'installation_date' => 'required|date',
            'router_id' => 'nullable|exists:routers,id',
            'service_plan_id' => 'nullable|exists:service_plans,id',
            'monthly_fee' => 'required|numeric|min:0',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
            'vlan' => 'nullable|string|max:10',
            'status' => 'required|in:active,suspended,expired,pending',
            'onu_information' => 'nullable|string',
            'olt_port' => 'nullable|string|max:50',
            'technician_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => $customer->load(['technician', 'servicePlan']),
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['technician', 'router', 'servicePlan'])
            ->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'account_number' => 'sometimes|required|string|unique:customers,account_number,' . $id,
            'full_name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'gps_coordinates' => 'nullable|array',
            'gps_coordinates.latitude' => 'required_with:gps_coordinates|numeric',
            'gps_coordinates.longitude' => 'required_with:gps_coordinates|numeric',
            'contact_number' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'installation_date' => 'sometimes|required|date',
            'router_id' => 'nullable|exists:routers,id',
            'service_plan_id' => 'nullable|exists:service_plans,id',
            'monthly_fee' => 'sometimes|required|numeric|min:0',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
            'vlan' => 'nullable|string|max:10',
            'status' => 'sometimes|required|in:active,suspended,expired,pending',
            'onu_information' => 'nullable|string',
            'olt_port' => 'nullable|string|max:50',
            'technician_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'data' => $customer->load(['technician', 'servicePlan']),
        ]);
    }

    /**
     * Remove the specified customer (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully',
        ]);
    }

    /**
     * Get customer statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::active()->count(),
            'suspended' => Customer::where('status', 'suspended')->count(),
            'expired' => Customer::where('status', 'expired')->count(),
            'pending' => Customer::where('status', 'pending')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Manually sync queue for a customer
     */
    public function syncQueue(string $id): JsonResponse
    {
        try {
            $customer = Customer::with(['servicePlan', 'router'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $result = $this->queueService->syncCustomerQueue($customer);

        return response()->json($result);
    }

    /**
     * Bulk sync queues for multiple customers
     */
    public function bulkSyncQueues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'required|string|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->queueService->bulkSyncQueues($request->input('customer_ids'));

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

            'suspended' => Customer::suspended()->count(),
            'expired' => Customer::expired()->count(),
            'pending' => Customer::where('status', 'pending')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
