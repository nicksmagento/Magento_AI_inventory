<?php
namespace AI\InventoryOptimizer\Model\Integration;

use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Sap extends AbstractIntegration
{
    /**
     * @param Config $config
     * @param Logger $logger
     * @param ClientInterface $httpClient
     * @param Json $jsonSerializer
     */
    public function __construct(
        Config $config,
        Logger $logger,
        ClientInterface $httpClient,
        Json $jsonSerializer
    ) {
        parent::__construct(
            $config,
            $logger,
            $httpClient,
            $jsonSerializer,
            'sap',
            'SAP ERP'
        );
    }
    
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        try {
            $this->logActivity('initialize', 'Initializing SAP integration');
            
            // Perform any initialization tasks
            $credentials = $this->getCredentials();
            if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
                throw new \Exception('SAP credentials are not configured');
            }
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('SAP initialization error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function testConnection()
    {
        try {
            $this->logActivity('test_connection', 'Testing SAP connection');
            
            // Make a simple API call to test connection
            $response = $this->makeApiRequest('GET', 'api/v1/system/status', [], $this->getAuthHeaders());
            
            return isset($response['status']) && $response['status'] === 'ok';
        } catch (\Exception $e) {
            $this->logger->error('SAP connection test error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function importInventory(array $filters = [])
    {
        try {
            $this->logActivity('import_inventory', 'Importing inventory from SAP', $filters);
            
            // Build request parameters
            $params = [];
            if (!empty($filters['sku'])) {
                $params['material_number'] = $filters['sku'];
            }
            if (!empty($filters['warehouse'])) {
                $params['warehouse_id'] = $filters['warehouse'];
            }
            
            // Make API request to get inventory data
            $response = $this->makeApiRequest('GET', 'api/v1/inventory', $params, $this->getAuthHeaders());
            
            // Transform SAP inventory data to Magento format
            $inventory = [];
            if (isset($response['items']) && is_array($response['items'])) {
                foreach ($response['items'] as $item) {
                    $inventory[] = [
                        'sku' => $item['material_number'],
                        'source_code' => $this->mapWarehouseCode($item['warehouse_id']),
                        'quantity' => $item['available_stock'],
                        'status' => $item['available_stock'] > 0 ? 1 : 0
                    ];
                }
            }
            
            return $inventory;
        } catch (\Exception $e) {
            $this->logger->error('SAP inventory import error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * @inheritdoc
     */
    public function exportInventory(array $data)
    {
        try {
            $this->logActivity('export_inventory', 'Exporting inventory to SAP', ['count' => count($data)]);
            
            // Transform Magento inventory data to SAP format
            $sapData = [];
            foreach ($data as $item) {
                $sapData[] = [
                    'material_number' => $item['sku'],
                    'warehouse_id' => $this->mapWarehouseCode($item['source_code'], true),
                    'available_stock' => $item['quantity']
                ];
            }
            
            // Make API request to update inventory in SAP
            $response = $this->makeApiRequest('POST', 'api/v1/inventory/update', ['items' => $sapData], $this->getAuthHeaders());
            
            return isset($response['success']) && $response['success'] === true;
        } catch (\Exception $e) {
            $this->logger->error('SAP inventory export error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function importOrders(array $filters = [])
    {
        try {
            $this->logActivity('import_orders', 'Importing orders from SAP', $filters);
            
            // Build request parameters
            $params = [];
            if (!empty($filters['date_from'])) {
                $params['created_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $params['created_to'] = $filters['date_to'];
            }
            
            // Make API request to get order data
            $response = $this->makeApiRequest('GET', 'api/v1/orders', $params, $this->getAuthHeaders());
            
            // Transform SAP order data to Magento format
            $orders = [];
            if (isset($response['orders']) && is_array($response['orders'])) {
                foreach ($response['orders'] as $order) {
                    $items = [];
                    foreach ($order['items'] as $item) {
                        $items[] = [
                            'sku' => $item['material_number'],
                            'qty' => $item['quantity'],
                            'price' => $item['price']
                        ];
                    }
                    
                    $orders[] = [
                        'external_id' => $order['order_number'],
                        'customer_email' => $order['customer']['email'],
                        'customer_firstname' => $order['customer']['first_name'],
                        'customer_lastname' => $order['customer']['last_name'],
                        'items' => $items,
                        'shipping_address' => [
                            'firstname' => $order['shipping']['first_name'],
                            'lastname' => $order['shipping']['last_name'],
                            'street' => $order['shipping']['street'],
                            'city' => $order['shipping']['city'],
                            'region' => $order['shipping']['region'],
                            'postcode' => $order['shipping']['postal_code'],
                            'country_id' => $order['shipping']['country_code'],
                            'telephone' => $order['shipping']['phone']
                        ]
                    ];
                }
            }
            
            return $orders;
        } catch (\Exception $e) {
            $this->logger->error('SAP order import error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * @inheritdoc
     */
    public function exportOrders(array $data)
    {
        try {
            $this->logActivity('export_orders', 'Exporting orders to SAP', ['count' => count($data)]);
            
            // Transform Magento order data to SAP format
            $sapOrders = [];
            foreach ($data as $order) {
                $items = [];
                foreach ($order['items'] as $item) {
                    $items[] = [
                        'material_number' => $item['sku'],
                        'quantity' => $item['qty_ordered'],
                        'price' => $item['price']
                    ];
                }
                
                $sapOrders[] = [
                    'order_number' => $order['increment_id'],
                    'customer' => [
                        'email' => $order['customer_email'],
                        'first_name' => $order['customer_firstname'],
                        'last_name' => $order['customer_lastname']
                    ],
                    'items' => $items,
                    'shipping' => [
                        'first_name' => $order['shipping_address']['firstname'],
                        'last_name' => $order['shipping_address']['lastname'],
                        'street' => $order['shipping_address']['street'],
                        'city' => $order['shipping_address']['city'],
                        'region' => $order['shipping_address']['region'],
                        'postal_code' => $order['shipping_address']['postcode'],
                        'country_code' => $order['shipping_address']['country_id'],
                        'phone' => $order['shipping_address']['telephone']
                    ]
                ];
            }
            
            // Make API request to create orders in SAP
            $response = $this->makeApiRequest('POST', 'api/v1/orders/create', ['orders' => $sapOrders], $this->getAuthHeaders());
            
            return isset($response['success']) && $response['success'] === true;
        } catch (\Exception $e) {
            $this->logger->error('SAP order export error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        try {
            $this->logActivity('get_status', 'Getting SAP integration status');
            
            // Make API request to get system status
            $response = $this->makeApiRequest('GET', 'api/v1/system/status', [], $this->getAuthHeaders());
            
            return [
                'connected' => isset($response['status']) && $response['status'] === 'ok',
                'version' => $response['version'] ?? 'unknown',
                'last_sync' => $response['last_sync'] ?? null,
                'pending_items' => $response['pending_items'] ?? 0
            ];
        } catch (\Exception $e) {
            $this->logger->error('SAP status check error: ' . $e->getMessage());
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get authentication headers
     *
     * @return array
     */
    private function getAuthHeaders()
    {
        $credentials = $this->getCredentials();
        
        // In a real implementation, this would handle OAuth or other authentication methods
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'x-sap-client-id' => $credentials['client_id']
        ];
    }
    
    /**
     * Get access token
     *
     * @return string
     */
    private function getAccessToken()
    {
        // In a real implementation, this would handle token retrieval and caching
        $credentials = $this->getCredentials();
        
        // Check if we have a cached token
        $cachedToken = $this->config->getCachedToken('sap');
        if ($cachedToken && $cachedToken['expires_at'] > time()) {
            return $cachedToken['token'];
        }
        
        // Request new token
        $tokenUrl = $this->getApiEndpoint('api/v1/auth/token');
        $this->httpClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->httpClient->post($tokenUrl, http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret']
        ]));
        
        $response = $this->jsonSerializer->unserialize($this->httpClient->getBody());
        
        if (isset($response['access_token'])) {
            // Cache the token
            $this->config->setCachedToken('sap', [
                'token' => $response['access_token'],
                'expires_at' => time() + ($response['expires_in'] ?? 3600)
            ]);
            
            return $response['access_token'];
        }
        
        throw new \Exception('Failed to get SAP access token');
    }
    
    /**
     * Map warehouse code between Magento and SAP
     *
     * @param string $code
     * @param bool $reverse
     * @return string
     */
    private function mapWarehouseCode($code, $reverse = false)
    {
        $mapping = $this->config->getWarehouseMapping('sap');
        
        if ($reverse) {
            // Magento to SAP
            return $mapping[$code] ?? $code;
        } else {
            // SAP to Magento
            $flipped = array_flip($mapping);
            return $flipped[$code] ?? $code;
        }
    }
} 