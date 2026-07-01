<?php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    /**
     * Test connection to MikroTik router
     * 
     * @param Router $router
     * @return array{success: bool, message: string, data: array|null}
     */
    public function testConnection(Router $router): array
    {
        try {
            // Create config
            $config = (new Config())
                ->set('host', $router->host)
                ->set('user', $router->username)
                ->set('pass', $router->password)
                ->set('port', $router->port);

            // Create client and connect
            $client = new Client($config);
            
            // Fetch system resource to get RouterOS version and uptime
            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();
            
            $systemInfo = $response[0] ?? [];
            
            $data = [
                'version' => $systemInfo['version'] ?? 'Unknown',
                'uptime' => $systemInfo['uptime'] ?? 'Unknown',
                'cpu_load' => $systemInfo['cpu-load'] ?? 'Unknown',
                'free_memory' => $systemInfo['free-memory'] ?? 'Unknown',
                'total_memory' => $systemInfo['total-memory'] ?? 'Unknown',
                'board_name' => $systemInfo['board-name'] ?? 'Unknown',
            ];
            
            // Update router record
            $router->update([
                'connection_status' => 'online',
                'routeros_version' => $data['version'],
                'last_connected_at' => now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Connected successfully to ' . $router->name,
                'data' => $data,
            ];
            
        } catch (Exception $e) {
            Log::error('MikroTik connection failed', [
                'router_id' => $router->id,
                'host' => $router->host,
                'error' => $e->getMessage(),
            ]);
            
            // Update router status
            $router->update([
                'connection_status' => 'offline',
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Sync data from MikroTik router
     * Placeholder for future implementation
     * 
     * @param Router $router
     * @return array
     */
    public function syncRouter(Router $router): array
    {
        // TODO: Implement in Phase 5 - full sync functionality
        // - Sync DHCP leases
        // - Sync active connections
        // - Sync bandwidth queues
        // - etc.
        
        return [
            'success' => true,
            'message' => 'Sync functionality will be implemented in Phase 5',
            'synced_items' => [
                'dhcp_leases' => 0,
                'active_connections' => 0,
                'queues' => 0,
            ],
        ];
    }

    /**
     * Get DHCP leases from router
     * Placeholder for Phase 6
     * 
     * @param Router $router
     * @return array
     */
    public function getDhcpLeases(Router $router): array
    {
        try {
            $config = (new Config())
                ->set('host', $router->host)
                ->set('user', $router->username)
                ->set('pass', $router->password)
                ->set('port', $router->port);

            $client = new Client($config);
            
            $query = new Query('/ip/dhcp-server/lease/print');
            $leases = $client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $leases,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
