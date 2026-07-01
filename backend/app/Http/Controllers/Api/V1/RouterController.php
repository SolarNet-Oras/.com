<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\MikrotikService;
use App\Services\MikrotikScriptGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RouterController extends Controller
{
    protected MikrotikService $mikrotikService;
    protected MikrotikScriptGenerator $scriptGenerator;

    public function __construct(MikrotikService $mikrotikService, MikrotikScriptGenerator $scriptGenerator)
    {
        $this->mikrotikService = $mikrotikService;
        $this->scriptGenerator = $scriptGenerator;
    }

    /**
     * Display a listing of routers
     */
    public function index(): JsonResponse
    {
        $routers = Router::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $routers,
        ]);
    }

    /**
     * Store a newly created router
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'dhcp_pool_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $router = Router::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Router added successfully',
            'data' => $router,
        ], 201);
    }

    /**
     * Display the specified router
     */
    public function show(string $id): JsonResponse
    {
        $router = Router::find($id);

        if (!$router) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $router,
        ]);
    }

    /**
     * Update the specified router
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $router = Router::find($id);

        if (!$router) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'host' => 'sometimes|required|string|max:255',
            'port' => 'sometimes|required|integer|min:1|max:65535',
            'username' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|required|string',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'dhcp_pool_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $router->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Router updated successfully',
            'data' => $router,
        ]);
    }

    /**
     * Remove the specified router
     */
    public function destroy(string $id): JsonResponse
    {
        $router = Router::find($id);

        if (!$router) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        $router->delete();

        return response()->json([
            'success' => true,
            'message' => 'Router deleted successfully',
        ]);
    }

    /**
     * Test connection to router
     */
    public function testConnection(string $id): JsonResponse
    {
        try {
            $router = Router::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        $result = $this->mikrotikService->testConnection($router);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Trigger manual sync for router
     */
    public function sync(string $id): JsonResponse
    {
        try {
            $router = Router::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        $result = $this->mikrotikService->syncRouter($router);

        return response()->json($result);
    }


    /**
     * Generate setup script for router
     */
    public function generateSetupScript(Request $request, string $id): JsonResponse
    {
        try {
            $router = Router::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Router not found',
            ], 404);
        }

        $billingSystemIp = $request->input('billing_system_ip');
        $script = $this->scriptGenerator->generateSetupScript($router, $billingSystemIp);

        return response()->json([
            'success' => true,
            'data' => [
                'script' => $script,
                'router' => [
                    'name' => $router->name,
                    'host' => $router->host,
                    'port' => $router->port,
                ],
            ],
        ]);
    }

    /**
     * Get queue management script template
     */
    public function getQueueScript(): JsonResponse
    {
        $script = $this->scriptGenerator->generateQueueManagementScript();

        return response()->json([
            'success' => true,
            'data' => [
                'script' => $script,
            ],
        ]);
    }

}
