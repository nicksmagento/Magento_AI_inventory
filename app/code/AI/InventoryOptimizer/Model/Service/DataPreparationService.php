<?php
namespace AI\InventoryOptimizer\Model\Service;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class DataPreparationService
{
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;
    
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;
    
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;
    
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        GetProductSalableQtyInterface $getProductSalableQty,
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }
    
    /**
     * Prepare training data based on model type
     *
     * @param string $modelType
     * @return array
     */
    public function prepareTrainingData($modelType)
    {
        switch ($modelType) {
            case 'forecasting':
                return $this->prepareForecastingData();
            
            case 'stock_balancer':
                return $this->prepareStockBalancerData();
            
            case 'order_router':
                return $this->prepareOrderRoutingData();
            
            case 'fraud_detection':
                return $this->prepareFraudDetectionData();
            
            case 'chat_copilot':
                return $this->prepareChatCopilotData();
            
            default:
                $this->logger->warning(sprintf('Unknown model type for training: %s', $modelType));
                return [];
        }
    }
    
    /**
     * Prepare data for forecasting model training
     *
     * @return array
     */
    private function prepareForecastingData()
    {
        $this->logger->info('Preparing forecasting training data');
        
        $trainingData = [];
        
        try {
            // Get historical sales data for the past year
            $endDate = $this->dateTime->gmtDate();
            $startDate = $this->dateTime->gmtDate(null, $this->dateTime->gmtTimestamp() - 365 * 86400);
            
            $connection = $this->resourceConnection->getConnection();
            $salesItemTable = $this->resourceConnection->getTableName('sales_order_item');
            $salesOrderTable = $this->resourceConnection->getTableName('sales_order');
            
            $select = $connection->select()
                ->from(
                    ['soi' => $salesItemTable],
                    [
                        'sku' => 'soi.sku',
                        'order_date' => 'so.created_at',
                        'qty_ordered' => 'SUM(soi.qty_ordered)'
                    ]
                )
                ->join(
                    ['so' => $salesOrderTable],
                    'soi.order_id = so.entity_id',
                    []
                )
                ->where('so.created_at >= ?', $startDate)
                ->where('so.created_at <= ?', $endDate)
                ->where('so.status NOT IN (?)', ['canceled', 'closed'])
                ->group(['soi.sku', 'DATE(so.created_at)']);
            
            $salesData = $connection->fetchAll($select);
            
            // Organize data by SKU and date
            $skuData = [];
            foreach ($salesData as $row) {
                $sku = $row['sku'];
                $date = substr($row['order_date'], 0, 10);
                $qty = (float)$row['qty_ordered'];
                
                if (!isset($skuData[$sku])) {
                    $skuData[$sku] = [];
                }
                
                $skuData[$sku][$date] = $qty;
            }
            
            // Format data for training
            foreach ($skuData as $sku => $data) {
                // Get current stock level
                $stockLevel = 0;
                try {
                    $stockLevel = $this->getProductSalableQty->execute($sku, 'default');
                } catch (\Exception $e) {
                    $this->logger->warning(sprintf('Could not get stock level for SKU %s: %s', $sku, $e->getMessage()));
                }
                
                // Add product metadata
                $product = $this->productCollectionFactory->create()
                    ->addFieldToFilter('sku', $sku)
                    ->getFirstItem();
                
                $productData = [
                    'sku' => $sku,
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'current_stock' => $stockLevel,
                    'category_ids' => $product->getCategoryIds(),
                    'sales_history' => []
                ];
                
                // Add sales history with date features
                foreach ($data as $date => $qty) {
                    $timestamp = strtotime($date);
                    $dayOfWeek = date('N', $timestamp); // 1 (Monday) to 7 (Sunday)
                    $month = date('n', $timestamp); // 1 to 12
                    $dayOfMonth = date('j', $timestamp); // 1 to 31
                    
                    $productData['sales_history'][] = [
                        'date' => $date,
                        'qty' => $qty,
                        'day_of_week' => (int)$dayOfWeek,
                        'month' => (int)$month,
                        'day_of_month' => (int)$dayOfMonth,
                        'is_weekend' => ($dayOfWeek >= 6) ? 1 : 0
                    ];
                }
                
                $trainingData[] = $productData;
            }
            
            $this->logger->info(sprintf('Prepared forecasting training data for %d products', count($trainingData)));
            return $trainingData;
        } catch (\Exception $e) {
            $this->logger->error('Data Preparation Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Prepare data for stock balancer model training
     *
     * @return array
     */
    private function prepareStockBalancerData()
    {
        $this->logger->info('Preparing stock balancer training data');
        
        $trainingData = [];
        
        try {
            // Get products with inventory in multiple warehouses
            $connection = $this->resourceConnection->getConnection();
            $inventoryTable = $this->resourceConnection->getTableName('inventory_source_item');
            
            $select = $connection->select()
                ->from(
                    ['isi' => $inventoryTable],
                    [
                        'sku' => 'isi.sku',
                        'source_code' => 'isi.source_code',
                        'quantity' => 'isi.quantity',
                        'status' => 'isi.status'
                    ]
                )
                ->where('isi.status = ?', 1); // In stock items
            
            $inventoryData = $connection->fetchAll($select);
            
            // Group by SKU
            $skuInventory = [];
            foreach ($inventoryData as $row) {
                $sku = $row['sku'];
                $sourceCode = $row['source_code'];
                $quantity = (float)$row['quantity'];
                
                if (!isset($skuInventory[$sku])) {
                    $skuInventory[$sku] = [];
                }
                
                $skuInventory[$sku][$sourceCode] = $quantity;
            }
            
            // Get sales velocity by warehouse
            $endDate = $this->dateTime->gmtDate();
            $startDate = $this->dateTime->gmtDate(null, $this->dateTime->gmtTimestamp() - 90 * 86400); // Last 90 days
            
            $salesItemTable = $this->resourceConnection->getTableName('sales_order_item');
            $salesOrderTable = $this->resourceConnection->getTableName('sales_order');
            $shipmentTable = $this->resourceConnection->getTableName('sales_shipment');
            $shipmentItemTable = $this->resourceConnection->getTableName('sales_shipment_item');
            
            $select = $connection->select()
                ->from(
                    ['soi' => $salesItemTable],
                    [
                        'sku' => 'soi.sku',
                        'qty_shipped' => 'SUM(ssi.qty)',
                        'warehouse' => 'ss.source_code' // Assuming source code is stored in shipment
                    ]
                )
                ->join(
                    ['so' => $salesOrderTable],
                    'soi.order_id = so.entity_id',
                    []
                )
                ->join(
                    ['ss' => $shipmentTable],
                    'so.entity_id = ss.order_id',
                    []
                )
                ->join(
                    ['ssi' => $shipmentItemTable],
                    'ssi.parent_id = ss.entity_id AND ssi.order_item_id = soi.item_id',
                    []
                )
                ->where('so.created_at >= ?', $startDate)
                ->where('so.created_at <= ?', $endDate)
                ->group(['soi.sku', 'ss.source_code']);
            
            $salesData = $connection->fetchAll($select);
            
            // Calculate sales velocity by warehouse
            $skuVelocity = [];
            foreach ($salesData as $row) {
                $sku = $row['sku'];
                $warehouse = $row['warehouse'];
                $qtyShipped = (float)$row['qty_shipped'];
                
                // Calculate daily velocity (qty shipped / 90 days)
                $dailyVelocity = $qtyShipped / 90;
                
                if (!isset($skuVelocity[$sku])) {
                    $skuVelocity[$sku] = [];
                }
                
                $skuVelocity[$sku][$warehouse] = $dailyVelocity;
            }
            
            // Format data for training
            foreach ($skuInventory as $sku => $inventoryBySource) {
                // Only include SKUs with multiple warehouses
                if (count($inventoryBySource) <= 1) {
                    continue;
                }
                
                $warehouseData = [];
                $totalStock = 0;
                
                foreach ($inventoryBySource as $sourceCode => $quantity) {
                    $velocity = isset($skuVelocity[$sku][$sourceCode]) ? $skuVelocity[$sku][$sourceCode] : 0;
                    
                    $warehouseData[] = [
                        'warehouse_code' => $sourceCode,
                        'current_stock' => $quantity,
                        'daily_sales_velocity' => $velocity,
                        'days_of_supply' => $velocity > 0 ? $quantity / $velocity : 999 // Avoid division by zero
                    ];
                    
                    $totalStock += $quantity;
                }
                
                // Get product data
                $product = $this->productCollectionFactory->create()
                    ->addFieldToFilter('sku', $sku)
                    ->getFirstItem();
                
                $trainingData[] = [
                    'sku' => $sku,
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'total_stock' => $totalStock,
                    'warehouses' => $warehouseData
                ];
            }
            
            $this->logger->info(sprintf('Prepared stock balancer training data for %d products', count($trainingData)));
            return $trainingData;
        } catch (\Exception $e) {
            $this->logger->error('Stock Balancer Data Preparation Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Prepare data for order routing model training
     *
     * @return array
     */
    private function prepareOrderRoutingData()
    {
        $this->logger->info('Preparing order routing training data');
        
        $trainingData = [];
        
        try {
            // Get completed orders with shipping information
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('status', ['in' => ['complete', 'closed']]);
            $collection->addFieldToFilter('created_at', ['gteq' => date('Y-m-d H:i:s', strtotime('-180 days'))]);
            $collection->setPageSize(1000); // Limit to recent orders
            
            foreach ($collection as $order) {
                $shippingAddress = $order->getShippingAddress();
                if (!$shippingAddress) {
                    continue;
                }
                
                $orderItems = [];
                foreach ($order->getAllItems() as $item) {
                    $orderItems[] = [
                        'sku' => $item->getSku(),
                        'qty' => $item->getQtyOrdered()
                    ];
                }
                
                // Get shipment information
                $shipments = $order->getShipmentsCollection();
                if ($shipments->getSize() == 0) {
                    continue;
                }
                
                $shipmentData = [];
                foreach ($shipments as $shipment) {
                    $sourceCode = $shipment->getSourceCode() ?? 'default';
                    $createdAt = $shipment->getCreatedAt();
                    $trackingNumbers = [];
                    
                    foreach ($shipment->getAllTracks() as $track) {
                        $trackingNumbers[] = $track->getTrackNumber();
                    }
                    
                    $shipmentData[] = [
                        'source_code' => $sourceCode,
                        'created_at' => $createdAt,
                        'tracking_numbers' => $trackingNumbers
                    ];
                }
                
                // Calculate shipping metrics
                $orderCreatedAt = strtotime($order->getCreatedAt());
                $firstShipmentAt = isset($shipmentData[0]) ? strtotime($shipmentData[0]['created_at']) : $orderCreatedAt;
                $processingTime = ($firstShipmentAt - $orderCreatedAt) / 3600; // Hours
                
                $trainingData[] = [
                    'order_id' => $order->getIncrementId(),
                    'customer_location' => [
                        'city' => $shippingAddress->getCity(),
                        'region' => $shippingAddress->getRegion(),
                        'country' => $shippingAddress->getCountryId(),
                        'postcode' => $shippingAddress->getPostcode()
                    ],
                    'order_items' => $orderItems,
                    'shipping_method' => $order->getShippingMethod(),
                    'shipping_amount' => $order->getShippingAmount(),
                    'shipments' => $shipmentData,
                    'processing_time_hours' => $processingTime,
                    'fulfillment_warehouse' => isset($shipmentData[0]) ? $shipmentData[0]['source_code'] : 'unknown'
                ];
            }
            
            $this->logger->info(sprintf('Prepared order routing training data for %d orders', count($trainingData)));
            return $trainingData;
        } catch (\Exception $e) {
            $this->logger->error('Order Routing Data Preparation Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Prepare data for fraud detection model training
     *
     * @return array
     */
    private function prepareFraudDetectionData()
    {
        $this->logger->info('Preparing fraud detection training data');
        
        $trainingData = [];
        
        try {
            // Get orders with payment information
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('created_at', ['gteq' => date('Y-m-d H:i:s', strtotime('-365 days'))]);
            
            // Add fraud indicators (this is simplified - in a real system, you'd have actual fraud data)
            // For this example, we'll use some heuristics to simulate fraud indicators
            foreach ($collection as $order) {
                $payment = $order->getPayment();
                if (!$payment) {
                    continue;
                }
                
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();
                
                // Skip orders without addresses
                if (!$billingAddress || !$shippingAddress) {
                    continue;
                }
                
                // Calculate address match score (simplified)
                $addressMatchScore = 0;
                if ($billingAddress->getCity() == $shippingAddress->getCity()) $addressMatchScore++;
                if ($billingAddress->getRegion() == $shippingAddress->getRegion()) $addressMatchScore++;
                if ($billingAddress->getCountryId() == $shippingAddress->getCountryId()) $addressMatchScore++;
                if ($billingAddress->getPostcode() == $shippingAddress->getPostcode()) $addressMatchScore++;
                
                // Normalize to 0-1 range
                $addressMatchScore = $addressMatchScore / 4;
                
                // Simulate fraud indicators (in a real system, these would come from actual fraud data)
                $isFraud = false;
                $riskScore = 0;
                
                // High-value orders from certain countries might be higher risk
                $highRiskCountries = ['AA', 'ZZ']; // Example country codes
                $isHighRiskCountry = in_array($billingAddress->getCountryId(), $highRiskCountries);
                
                if ($isHighRiskCountry) {
                    $riskScore += 0.3;
                }
                
                // Different billing and shipping addresses might indicate higher risk
                if ($addressMatchScore < 0.5) {
                    $riskScore += 0.2;
                }
                
                // High-value orders might be higher risk
                if ($order->getGrandTotal() > 1000) {
                    $riskScore += 0.2;
                }
                
                // Orders with many items might be higher risk
                if (count($order->getAllItems()) > 10) {
                    $riskScore += 0.1;
                }
                
                // Orders that were canceled or refunded might have been fraudulent
                if (in_array($order->getStatus(), ['canceled', 'closed', 'fraud'])) {
                    $riskScore += 0.4;
                    // If the order was explicitly marked as fraud, set the flag
                    if ($order->getStatus() == 'fraud') {
                        $isFraud = true;
                    }
                }
                
                // Cap risk score at 1.0
                $riskScore = min(1.0, $riskScore);
                
                // If risk score is very high, consider it fraud for training purposes
                if ($riskScore > 0.7) {
                    $isFraud = true;
                }
                
                $trainingData[] = [
                    'order_id' => $order->getIncrementId(),
                    'customer_ip' => $order->getRemoteIp(),
                    'payment_method' => $payment->getMethod(),
                    'order_total' => $order->getGrandTotal(),
                    'currency' => $order->getOrderCurrencyCode(),
                    'item_count' => count($order->getAllItems()),
                    'billing_country' => $billingAddress->getCountryId(),
                    'shipping_country' => $shippingAddress->getCountryId(),
                    'address_match_score' => $addressMatchScore,
                    'customer_id' => $order->getCustomerId() ? 1 : 0, // Guest or registered
                    'customer_order_count' => $order->getCustomerId() ? $this->getCustomerOrderCount($order->getCustomerId()) : 0,
                    'is_fraud' => $isFraud ? 1 : 0,
                    'risk_score' => $riskScore
                ];
            }
            
            $this->logger->info(sprintf('Prepared fraud detection training data for %d orders', count($trainingData)));
            return $trainingData;
        } catch (\Exception $e) {
            $this->logger->error('Fraud Detection Data Preparation Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer order count
     *
     * @param int $customerId
     * @return int
     */
    private function getCustomerOrderCount($customerId)
    {
        try {
            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('customer_id', $customerId);
            return $collection->getSize();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Prepare data for chat copilot model training
     *
     * @return array
     */
    private function prepareChatCopilotData()
    {
        $this->logger->info('Preparing chat copilot training data');
        
        // For chat copilot, we need example conversations and intents
        // This would typically come from a dataset of merchant interactions
        $trainingData = [
            // Reorder intent examples
            [
                'text' => 'I need to reorder SKU ABC123',
                'intent' => 'reorder',
                'parameters' => ['sku' => 'ABC123']
            ],
            [
                'text' => 'Create a reorder for product XYZ789',
                'intent' => 'reorder',
                'parameters' => ['sku' => 'XYZ789']
            ],
            [
                'text' => 'We\'re running low on ABC123, please reorder',
                'intent' => 'reorder',
                'parameters' => ['sku' => 'ABC123']
            ],
            
            // Transfer stock intent examples
            [
                'text' => 'Transfer stock of ABC123 from warehouse A to B',
                'intent' => 'transfer_stock',
                'parameters' => ['sku' => 'ABC123', 'source' => 'A', 'destination' => 'B']
            ],
            [
                'text' => 'Move 10 units of XYZ789 to warehouse C',
                'intent' => 'transfer_stock',
                'parameters' => ['sku' => 'XYZ789', 'quantity' => 10, 'destination' => 'C']
            ],
            [
                'text' => 'Balance stock for product ABC123',
                'intent' => 'transfer_stock',
                'parameters' => ['sku' => 'ABC123']
            ],
            
            // Inventory status intent examples
            [
                'text' => 'What\'s the inventory level for ABC123?',
                'intent' => 'inventory_status',
                'parameters' => ['sku' => 'ABC123']
            ],
            [
                'text' => 'Show me stock levels for XYZ789',
                'intent' => 'inventory_status',
                'parameters' => ['sku' => 'XYZ789']
            ],
            [
                'text' => 'How many units of ABC123 do we have?',
                'intent' => 'inventory_status',
                'parameters' => ['sku' => 'ABC123']
            ],
            
            // Forecast intent examples
            [
                'text' => 'Generate a forecast for ABC123',
                'intent' => 'forecast',
                'parameters' => ['sku' => 'ABC123']
            ],
            [
                'text' => 'What\'s the demand forecast for XYZ789 for the next 30 days?',
                'intent' => 'forecast',
                'parameters' => ['sku' => 'XYZ789', 'days' => 30]
            ],
            [
                'text' => 'Predict sales for ABC123',
                'intent' => 'forecast',
                'parameters' => ['sku' => 'ABC123']
            ],
            
            // Unknown intent examples
            [
                'text' => 'What\'s the weather like today?',
                'intent' => 'unknown',
                'parameters' => []
            ],
            [
                'text' => 'Help me with marketing',
                'intent' => 'unknown',
                'parameters' => []
            ],
            [
                'text' => 'I need to create a new product',
                'intent' => 'unknown',
                'parameters' => []
            ]
        ];
        
        $this->logger->info(sprintf('Prepared chat copilot training data with %d examples', count($trainingData)));
        return $trainingData;
    }
} 