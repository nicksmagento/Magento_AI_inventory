<?php
namespace AI\InventoryOptimizer\Model\Service;

use AI\InventoryOptimizer\Helper\Config;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class AiService
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var Curl
     */
    private $curl;
    
    /**
     * @var Json
     */
    private $json;
    
    /**
     * @var Filesystem
     */
    private $filesystem;
    
    /**
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param Json $json
     * @param Filesystem $filesystem
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        Curl $curl,
        Json $json,
        Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->curl = $curl;
        $this->json = $json;
        $this->filesystem = $filesystem;
    }
    
    /**
     * Generate demand forecast using AI
     *
     * @param array $productData
     * @param int $days
     * @return array
     */
    public function generateDemandForecast($productData, $days)
    {
        try {
            $this->logger->info(sprintf('Generating demand forecast for SKU %s for %d days', $productData['sku'], $days));
            
            // In a real implementation, this would call an external AI service or use a local model
            // For this example, we'll simulate a forecast
            
            $forecast = $this->simulateForecast($productData, $days);
            
            return [
                'sku' => $productData['sku'],
                'forecast_data' => $forecast,
                'confidence' => 0.85
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Forecast Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Analyze stock distribution using AI
     *
     * @param array $inventoryData
     * @return array
     */
    public function analyzeStockDistribution($inventoryData)
    {
        try {
            $this->logger->info(sprintf('Analyzing stock distribution for SKU %s', $inventoryData['sku']));
            
            // In a real implementation, this would call an external AI service or use a local model
            // For this example, we'll simulate recommendations
            
            $recommendations = $this->simulateStockRecommendations($inventoryData);
            
            return [
                'sku' => $inventoryData['sku'],
                'transfers' => $recommendations,
                'confidence' => 0.82
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Stock Analysis Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Calculate optimal warehouse for order fulfillment
     *
     * @param array $orderData
     * @param array $warehouseData
     * @return array
     */
    public function calculateOptimalWarehouse($orderData, $warehouseData)
    {
        try {
            $this->logger->info(sprintf('Calculating optimal warehouse for order %s', $orderData['order_id']));
            
            // In a real implementation, this would call an external AI service or use a local model
            // For this example, we'll simulate a warehouse selection
            
            $optimalWarehouse = $this->simulateWarehouseSelection($orderData, $warehouseData);
            
            return [
                'order_id' => $orderData['order_id'],
                'assigned_warehouse' => $optimalWarehouse['code'],
                'warehouse_name' => $optimalWarehouse['name'],
                'estimated_delivery' => $optimalWarehouse['delivery_days'] . ' days',
                'confidence' => 0.89
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Warehouse Selection Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Analyze fraud risk for an order
     *
     * @param array $orderData
     * @return array
     */
    public function analyzeFraudRisk($orderData)
    {
        try {
            $this->logger->info(sprintf('Analyzing fraud risk for order %s', $orderData['order_id']));
            
            // In a real implementation, this would call an external AI service or use a local model
            // For this example, we'll simulate a fraud risk assessment
            
            $riskAssessment = $this->simulateFraudRiskAssessment($orderData);
            
            return [
                'order_id' => $orderData['order_id'],
                'fraud_risk' => $riskAssessment['risk_level'],
                'risk_score' => $riskAssessment['risk_score'],
                'action' => $riskAssessment['recommended_action'],
                'confidence' => 0.91
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Fraud Analysis Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process natural language command
     *
     * @param string $command
     * @return array
     */
    public function processNaturalLanguageCommand($command)
    {
        try {
            $this->logger->info(sprintf('Processing natural language command: %s', $command));
            
            // In a real implementation, this would call an external NLP service or use a local model
            // For this example, we'll simulate NLP processing
            
            $nlpResult = $this->simulateNlpProcessing($command);
            
            return [
                'intent' => $nlpResult['intent'],
                'parameters' => $nlpResult['parameters'],
                'response' => $nlpResult['response'],
                'confidence' => 0.87
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI NLP Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Train AI model
     *
     * @param string $modelType
     * @param array $trainingData
     * @param array $parameters
     * @return array
     */
    public function trainModel($modelType, array $trainingData, array $parameters = [])
    {
        try {
            $this->logger->info(sprintf(
                'Training %s model with %d samples and parameters: %s',
                $modelType,
                count($trainingData),
                json_encode($parameters)
            ));
            
            // In a real implementation, this would call an external AI training service or use a local model
            // For this example, we'll simulate model training
            
            $trainingResult = $this->simulateModelTraining($modelType, $trainingData, $parameters);
            
            // Save model to filesystem (in a real implementation, this might be a binary model file)
            $this->saveModelToFilesystem($modelType, $trainingResult);
            
            return [
                'model_type' => $modelType,
                'model_version' => $trainingResult['model_version'],
                'accuracy' => $trainingResult['accuracy'],
                'training_time' => $trainingResult['training_time']
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Training Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Save model to filesystem
     *
     * @param string $modelType
     * @param array $modelData
     * @return bool
     */
    private function saveModelToFilesystem($modelType, $modelData)
    {
        try {
            $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $modelDir = 'ai_models/' . $modelType;
            
            if (!$varDirectory->isExist($modelDir)) {
                $varDirectory->create($modelDir);
            }
            
            $modelFile = $modelDir . '/model_' . $modelData['model_version'] . '.json';
            $modelContent = $this->json->serialize($modelData);
            
            $varDirectory->writeFile($modelFile, $modelContent);
            
            $this->logger->info(sprintf('Saved %s model to %s', $modelType, $modelFile));
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error saving model: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load model from filesystem
     *
     * @param string $modelType
     * @param string $version
     * @return array|null
     */
    private function loadModelFromFilesystem($modelType, $version = 'latest')
    {
        try {
            $varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $modelDir = 'ai_models/' . $modelType;
            
            if (!$varDirectory->isExist($modelDir)) {
                return null;
            }
            
            if ($version === 'latest') {
                // Find the latest version
                $files = $varDirectory->read($modelDir);
                $modelFiles = [];
                
                foreach ($files as $file) {
                    if (preg_match('/model_([0-9\.]+)\.json$/', $file, $matches)) {
                        $modelFiles[$matches[1]] = $file;
                    }
                }
                
                if (empty($modelFiles)) {
                    return null;
                }
                
                // Sort by version number
                uksort($modelFiles, 'version_compare');
                $latestFile = end($modelFiles);
            } else {
                $latestFile = $modelDir . '/model_' . $version . '.json';
                if (!$varDirectory->isExist($latestFile)) {
                    return null;
                }
            }
            
            $modelContent = $varDirectory->readFile($latestFile);
            $modelData = $this->json->unserialize($modelContent);
            
            $this->logger->info(sprintf('Loaded %s model from %s', $modelType, $latestFile));
            
            return $modelData;
        } catch (\Exception $e) {
            $this->logger->error('Error loading model: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Simulate forecast generation
     *
     * @param array $productData
     * @param int $days
     * @return array
     */
    private function simulateForecast($productData, $days)
    {
        $forecast = [];
        $sku = $productData['sku'];
        
        // Load model if available
        $model = $this->loadModelFromFilesystem('forecasting');
        
        // Base forecast on historical data if available
        $salesHistory = isset($productData['sales_history']) ? $productData['sales_history'] : [];
        
        // Calculate average daily sales
        $totalSales = 0;
        $salesDays = count($salesHistory);
        
        if ($salesDays > 0) {
            foreach ($salesHistory as $sale) {
                $totalSales += $sale['qty'];
            }
            $avgDailySales = $totalSales / $salesDays;
        } else {
            // No history, use a default value
            $avgDailySales = 5;
        }
        
        // Generate forecast with some randomness
        $currentDate = date('Y-m-d');
        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = date('Y-m-d', strtotime($currentDate . ' +' . $i . ' days'));
            
            // Add some variability based on day of week
            $dayOfWeek = date('N', strtotime($forecastDate));
            $weekendFactor = ($dayOfWeek >= 6) ? 0.7 : 1.0;
            
            // Add some randomness
            $randomFactor = mt_rand(80, 120) / 100;
            
            $forecastQty = round($avgDailySales * $weekendFactor * $randomFactor);
            
            $forecast[] = [
                'date' => $forecastDate,
                'qty' => $forecastQty
            ];
        }
        
        return $forecast;
    }
    
    /**
     * Simulate stock recommendations
     *
     * @param array $inventoryData
     * @return array
     */
    private function simulateStockRecommendations($inventoryData)
    {
        $recommendations = [];
        $warehouses = isset($inventoryData['warehouses']) ? $inventoryData['warehouses'] : [];
        
        // Load model if available
        $model = $this->loadModelFromFilesystem('stock_balancer');
        
        if (count($warehouses) < 2) {
            return $recommendations;
        }
        
        // Find warehouses with excess stock and those with low stock
        $excessWarehouses = [];
        $lowWarehouses = [];
        
        foreach ($warehouses as $warehouse) {
            $daysOfSupply = $warehouse['days_of_supply'];
            
            if ($daysOfSupply > 60) {
                $excessWarehouses[] = $warehouse;
            } elseif ($daysOfSupply < 14) {
                $lowWarehouses[] = $warehouse;
            }
        }
        
        // Generate transfer recommendations
        foreach ($lowWarehouses as $lowWarehouse) {
            foreach ($excessWarehouses as $excessWarehouse) {
                // Calculate transfer quantity
                $targetDaysOfSupply = 30; // Aim for 30 days of supply
                $currentDaysOfSupply = $lowWarehouse['days_of_supply'];
                $dailySalesVelocity = $lowWarehouse['daily_sales_velocity'];
                
                if ($dailySalesVelocity <= 0) {
                    continue;
                }
                
                $neededStock = ($targetDaysOfSupply - $currentDaysOfSupply) * $dailySalesVelocity;
                $availableExcess = $excessWarehouse['current_stock'] - (30 * $excessWarehouse['daily_sales_velocity']);
                
                $transferQty = min($neededStock, $availableExcess);
                $transferQty = max(1, round($transferQty)); // At least 1 unit
                
                if ($transferQty > 0) {
                    $recommendations[] = [
                        'source_warehouse' => $excessWarehouse['warehouse_code'],
                        'destination_warehouse' => $lowWarehouse['warehouse_code'],
                        'quantity' => $transferQty,
                        'reason' => sprintf(
                            'Balance stock: %s has %d days of supply, %s has %d days',
                            $lowWarehouse['warehouse_code'],
                            $currentDaysOfSupply,
                            $excessWarehouse['warehouse_code'],
                            $excessWarehouse['days_of_supply']
                        )
                    ];
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Simulate warehouse selection
     *
     * @param array $orderData
     * @param array $warehouseData
     * @return array
     */
    private function simulateWarehouseSelection($orderData, $warehouseData)
    {
        // Load model if available
        $model = $this->loadModelFromFilesystem('order_router');
        
        // Simple algorithm: choose warehouse with lowest shipping cost
        $bestWarehouse = null;
        $lowestCost = PHP_FLOAT_MAX;
        
        foreach ($warehouseData as $warehouse) {
            if (isset($warehouse['shipping_cost']) && $warehouse['shipping_cost'] < $lowestCost) {
                $lowestCost = $warehouse['shipping_cost'];
                $bestWarehouse = $warehouse;
            }
        }
        
        // If no warehouse found, use the first one
        if ($bestWarehouse === null && !empty($warehouseData)) {
            $bestWarehouse = $warehouseData[0];
        }
        
        return $bestWarehouse;
    }
    
    /**
     * Simulate fraud risk assessment
     *
     * @param array $orderData
     * @return array
     */
    private function simulateFraudRiskAssessment($orderData)
    {
        // Load model if available
        $model = $this->loadModelFromFilesystem('fraud_detection');
        
        // Simple simulation based on order value
        $orderValue = isset($orderData['order_value']) ? $orderData['order_value'] : 0;
        
        // Generate a risk score (0-1)
        $riskScore = 0;
        
        // Higher value orders have higher base risk
        if ($orderValue > 1000) {
            $riskScore += 0.4;
        } elseif ($orderValue > 500) {
            $riskScore += 0.2;
        } elseif ($orderValue > 200) {
            $riskScore += 0.1;
        }
        
        // Add some randomness
        $riskScore += mt_rand(0, 30) / 100;
        
        // Cap at 1.0
        $riskScore = min(1.0, $riskScore);
        
        // Determine risk level
        $riskLevel = 'Low';
        $action = 'approve';
        
        if ($riskScore > 0.7) {
            $riskLevel = 'High';
            $action = 'review';
        } elseif ($riskScore > 0.4) {
            $riskLevel = 'Medium';
            $action = 'monitor';
        }
        
        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'recommended_action' => $action
        ];
    }
    
    /**
     * Simulate NLP processing
     *
     * @param string $command
     * @return array
     */
    private function simulateNlpProcessing($command)
    {
        // Load model if available
        $model = $this->loadModelFromFilesystem('chat_copilot');
        
        // Simple keyword-based intent detection
        $command = strtolower($command);
        $intent = 'unknown';
        $parameters = [];
        $response = 'I\'m not sure how to help with that.';
        
        // Extract SKU if present
        $skuMatch = [];
        if (preg_match('/\b([a-z0-9]{5,10})\b/i', $command, $skuMatch)) {
            $parameters['sku'] = $skuMatch[1];
        }
        
        // Reorder intent
        if (strpos($command, 'reorder') !== false || strpos($command, 'order more') !== false) {
            $intent = 'reorder';
            $response = 'I\'ll create a reorder for ' . $parameters['sku'] . '.';
        }
        
        // Transfer stock intent
        elseif (strpos($command, 'transfer') !== false || strpos($command, 'move stock') !== false || strpos($command, 'balance') !== false) {
            $intent = 'transfer_stock';
            
            // Extract source and destination if present
            if (preg_match('/from\s+([a-z0-9]+)\s+to\s+([a-z0-9]+)/i', $command, $matches)) {
                $parameters['source'] = $matches[1];
                $parameters['destination'] = $matches[2];
                $response = 'I\'ll transfer stock of ' . $parameters['sku'] . ' from warehouse ' . $parameters['source'] . ' to ' . $parameters['destination'] . '.';
            } else {
                $response = 'I\'ll balance stock for ' . $parameters['sku'] . ' across all warehouses.';
            }
        }
        
        // Inventory status intent
        elseif (strpos($command, 'inventory') !== false || strpos($command, 'stock level') !== false || strpos($command, 'how many') !== false) {
            $intent = 'inventory_status';
            $response = 'The current inventory for ' . $parameters['sku'] . ' is 245 units across all warehouses.';
        }
        
        // Forecast intent
        elseif (strpos($command, 'forecast') !== false || strpos($command, 'predict') !== false || strpos($command, 'projection') !== false) {
            $intent = 'forecast';
            
            // Extract days if present
            $daysMatch = [];
            if (preg_match('/(\d+)\s+days?/i', $command, $daysMatch)) {
                $parameters['days'] = (int)$daysMatch[1];
                $response = 'I\'ve generated a ' . $parameters['days'] . '-day forecast for ' . $parameters['sku'] . '. Predicted sales: 120 units.';
            } else {
                $parameters['days'] = 30; // Default
                $response = 'I\'ve generated a 30-day forecast for ' . $parameters['sku'] . '. Predicted sales: 120 units.';
            }
        }
            
            return [
            'intent' => $intent,
            'parameters' => $parameters,
            'response' => $response
        ];
    }
    
    /**
     * Simulate model training
     *
     * @param string $modelType
     * @param array $trainingData
     * @param array $parameters
     * @return array
     */
    private function simulateModelTraining($modelType, array $trainingData, array $parameters = [])
    {
        // Get current model version if exists
        $currentModel = $this->loadModelFromFilesystem($modelType);
        $currentVersion = $currentModel ? $currentModel['model_version'] : '0.0.0';
        
        // Increment version
        $versionParts = explode('.', $currentVersion);
        $versionParts[2] = (int)$versionParts[2] + 1;
        $newVersion = implode('.', $versionParts);
        
        // Simulate training time based on data size
        $trainingTime = count($trainingData) * 0.05; // 0.05 seconds per sample
        
        // Simulate accuracy improvement
        $baseAccuracy = $currentModel ? $currentModel['accuracy'] : 0.7;
        $accuracyImprovement = mt_rand(1, 5) / 100; // 0.01 to 0.05 improvement
        $newAccuracy = min(0.99, $baseAccuracy + $accuracyImprovement);
        
        // Create model data
        $modelData = [
            'model_type' => $modelType,
            'model_version' => $newVersion,
            'accuracy' => $newAccuracy,
            'training_time' => $trainingTime,
            'training_samples' => count($trainingData),
            'parameters' => $parameters,
            'created_at' => date('Y-m-d H:i:s'),
            'features' => $this->extractFeatures($modelType, $trainingData)
        ];
        
        // Simulate training delay
        sleep(1);
        
        return $modelData;
    }
    
    /**
     * Extract features from training data
     *
     * @param string $modelType
     * @param array $trainingData
     * @return array
     */
    private function extractFeatures($modelType, array $trainingData)
    {
        $features = [];
        
        switch ($modelType) {
            case 'forecasting':
                // Extract product categories
                $categories = [];
                foreach ($trainingData as $product) {
                    if (isset($product['category_ids'])) {
                        foreach ($product['category_ids'] as $categoryId) {
                            if (!isset($categories[$categoryId])) {
                                $categories[$categoryId] = 0;
                            }
                            $categories[$categoryId]++;
                        }
                    }
                }
                $features['categories'] = $categories;
                break;
                
            case 'fraud_detection':
                // Extract payment methods
                $paymentMethods = [];
                foreach ($trainingData as $order) {
                    if (isset($order['payment_method'])) {
                        $method = $order['payment_method'];
                        if (!isset($paymentMethods[$method])) {
                            $paymentMethods[$method] = 0;
                        }
                        $paymentMethods[$method]++;
                    }
                }
                $features['payment_methods'] = $paymentMethods;
                break;
                
            case 'chat_copilot':
                // Extract intents
                $intents = [];
                foreach ($trainingData as $example) {
                    if (isset($example['intent'])) {
                        $intent = $example['intent'];
                        if (!isset($intents[$intent])) {
                            $intents[$intent] = 0;
                        }
                        $intents[$intent]++;
                    }
                }
                $features['intents'] = $intents;
                break;
        }
        
        return $features;
    }
} 