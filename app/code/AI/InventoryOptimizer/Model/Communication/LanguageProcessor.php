<?php
namespace AI\InventoryOptimizer\Model\Communication;

use AI\InventoryOptimizer\Model\Config;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use AI\InventoryOptimizer\Logger\Logger;

class LanguageProcessor
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var PriceHelper
     */
    private $priceHelper;
    
    /**
     * @var Logger
     */
    private $logger;
    
    public function __construct(
        Config $config,
        PriceHelper $priceHelper,
        Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->priceHelper = $priceHelper;
    }
    
    /**
     * Transform technical recommendation into merchant-centric language
     *
     * @param array $recommendation
     * @return array
     */
    public function transformRecommendation($recommendation)
    {
        try {
            $type = $recommendation['type'] ?? '';
            
            switch ($type) {
                case 'reorder':
                    return $this->transformReorderRecommendation($recommendation);
                    
                case 'stock_transfer':
                    return $this->transformStockTransferRecommendation($recommendation);
                    
                case 'order_routing':
                    return $this->transformOrderRoutingRecommendation($recommendation);
                    
                case 'fraud_alert':
                    return $this->transformFraudAlertRecommendation($recommendation);
                    
                default:
                    return $recommendation;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error transforming recommendation: ' . $e->getMessage());
            return $recommendation;
        }
    }
    
    /**
     * Transform reorder recommendation
     *
     * @param array $recommendation
     * @return array
     */
    private function transformReorderRecommendation($recommendation)
    {
        // Original technical message
        $technicalMessage = $recommendation['message'] ?? '';
        
        // Extract data
        $sku = $recommendation['sku'] ?? '';
        $productName = $recommendation['product_name'] ?? '';
        $quantity = $recommendation['quantity'] ?? 0;
        $daysUntilStockout = $recommendation['days_until_stockout'] ?? 0;
        $potentialRevenue = $recommendation['potential_revenue'] ?? 0;
        $confidence = $recommendation['confidence'] ?? 0.8;
        
        // Create merchant-centric message with revenue impact
        $merchantMessage = sprintf(
            'Reordering %d units of %s could generate approximately %s in Q%d revenue. Without this reorder, you\'ll likely stock out in %d days.',
            $quantity,
            $productName,
            $this->priceHelper->currency($potentialRevenue, true, false),
            $this->getCurrentQuarter(),
            $daysUntilStockout
        );
        
        // Add customer experience impact if available
        if (isset($recommendation['customer_impact'])) {
            $merchantMessage .= sprintf(
                ' This would help maintain a positive experience for approximately %d customers who purchase this product monthly.',
                $recommendation['customer_impact']
            );
        }
        
        // Add business goal alignment if configured
        $businessGoals = $this->config->getBusinessGoals();
        if (!empty($businessGoals) && isset($businessGoals['revenue_growth']) && $businessGoals['revenue_growth']) {
            $merchantMessage .= ' This aligns with your revenue growth goal for this quarter.';
        }
        
        // Update the recommendation
        $recommendation['original_message'] = $technicalMessage;
        $recommendation['message'] = $merchantMessage;
        
        return $recommendation;
    }
    
    /**
     * Transform stock transfer recommendation
     *
     * @param array $recommendation
     * @return array
     */
    private function transformStockTransferRecommendation($recommendation)
    {
        // Original technical message
        $technicalMessage = $recommendation['message'] ?? '';
        
        // Extract data
        $sku = $recommendation['sku'] ?? '';
        $productName = $recommendation['product_name'] ?? '';
        $quantity = $recommendation['quantity'] ?? 0;
        $sourceWarehouse = $recommendation['source_warehouse'] ?? '';
        $destinationWarehouse = $recommendation['destination_warehouse'] ?? '';
        $potentialRevenue = $recommendation['potential_revenue'] ?? 0;
        $timeImpact = $recommendation['delivery_time_impact'] ?? 0;
        
        // Create merchant-centric message with time-saving emphasis
        $merchantMessage = sprintf(
            'Transferring %d units of %s from %s to %s will reduce delivery times by %.1f days for customers in the %s region.',
            $quantity,
            $productName,
            $this->getWarehouseName($sourceWarehouse),
            $this->getWarehouseName($destinationWarehouse),
            $timeImpact,
            $this->getRegionForWarehouse($destinationWarehouse)
        );
        
        // Add revenue impact
        if ($potentialRevenue > 0) {
            $merchantMessage .= sprintf(
                ' This could prevent approximately %s in lost sales from stockouts.',
                $this->priceHelper->currency($potentialRevenue, true, false)
            );
        }
        
        // Add business goal alignment if configured
        $businessGoals = $this->config->getBusinessGoals();
        if (!empty($businessGoals) && isset($businessGoals['customer_satisfaction']) && $businessGoals['customer_satisfaction']) {
            $merchantMessage .= ' This supports your goal of improving customer satisfaction through faster delivery times.';
        }
        
        // Update the recommendation
        $recommendation['original_message'] = $technicalMessage;
        $recommendation['message'] = $merchantMessage;
        $recommendation['time_impact_hours'] = $timeImpact * 24;
        
        return $recommendation;
    }
    
    /**
     * Transform order routing recommendation
     *
     * @param array $recommendation
     * @return array
     */
    private function transformOrderRoutingRecommendation($recommendation)
    {
        // Original technical message
        $technicalMessage = $recommendation['message'] ?? '';
        
        // Extract data
        $orderId = $recommendation['order_id'] ?? '';
        $warehouseCode = $recommendation['warehouse_code'] ?? '';
        $shippingCostSavings = $recommendation['shipping_cost_savings'] ?? 0;
        $deliveryTimeImprovement = $recommendation['delivery_time_improvement'] ?? 0;
        $customerRegion = $recommendation['customer_region'] ?? '';
        
        // Create merchant-centric message with customer experience connection
        $merchantMessage = sprintf(
            'Routing order #%s through %s will deliver to your %s customer %.1f days faster than the default warehouse.',
            $orderId,
            $this->getWarehouseName($warehouseCode),
            $customerRegion,
            $deliveryTimeImprovement
        );
        
        // Add cost savings
        if ($shippingCostSavings > 0) {
            $merchantMessage .= sprintf(
                ' This also saves %s in shipping costs.',
                $this->priceHelper->currency($shippingCostSavings, true, false)
            );
        }
        
        // Add business goal alignment if configured
        $businessGoals = $this->config->getBusinessGoals();
        if (!empty($businessGoals) && isset($businessGoals['operational_efficiency']) && $businessGoals['operational_efficiency']) {
            $merchantMessage .= ' This supports your goal of improving operational efficiency while enhancing customer experience.';
        }
        
        // Update the recommendation
        $recommendation['original_message'] = $technicalMessage;
        $recommendation['message'] = $merchantMessage;
        
        return $recommendation;
    }
    
    /**
     * Transform fraud alert recommendation
     *
     * @param array $recommendation
     * @return array
     */
    private function transformFraudAlertRecommendation($recommendation)
    {
        // Original technical message
        $technicalMessage = $recommendation['message'] ?? '';
        
        // Extract data
        $orderId = $recommendation['order_id'] ?? '';
        $riskScore = $recommendation['risk_score'] ?? 0;
        $potentialLoss = $recommendation['potential_loss'] ?? 0;
        $riskFactors = $recommendation['risk_factors'] ?? [];
        
        // Create merchant-centric message with revenue protection framing
        $merchantMessage = sprintf(
            'Order #%s has been flagged with a %.0f%% fraud risk, potentially saving you %s in losses.',
            $orderId,
            $riskScore * 100,
            $this->priceHelper->currency($potentialLoss, true, false)
        );
        
        // Add key risk factors in merchant-friendly language
        if (!empty($riskFactors)) {
            $merchantMessage .= ' Key concerns include: ';
            $translatedFactors = [];
            
            foreach ($riskFactors as $factor) {
                switch ($factor) {
                    case 'address_mismatch':
                        $translatedFactors[] = 'shipping and billing addresses don\'t match';
                        break;
                    case 'high_value_first_order':
                        $translatedFactors[] = 'unusually high value for a first-time customer';
                        break;
                    case 'multiple_shipping_changes':
                        $translatedFactors[] = 'multiple shipping address changes';
                        break;
                    case 'high_risk_region':
                        $translatedFactors[] = 'shipping to a region with high fraud rates';
                        break;
                    default:
                        $translatedFactors[] = $factor;
                }
            }
            
            $merchantMessage .= implode(', ', $translatedFactors) . '.';
        }
        
        // Add business goal alignment if configured
        $businessGoals = $this->config->getBusinessGoals();
        if (!empty($businessGoals) && isset($businessGoals['loss_prevention']) && $businessGoals['loss_prevention']) {
            $merchantMessage .= ' This supports your goal of reducing fraud-related losses this quarter.';
        }
        
        // Update the recommendation
        $recommendation['original_message'] = $technicalMessage;
        $recommendation['message'] = $merchantMessage;
        
        return $recommendation;
    }
    
    /**
     * Get current quarter
     *
     * @return int
     */
    private function getCurrentQuarter()
    {
        $month = (int)date('n');
        return ceil($month / 3);
    }
    
    /**
     * Get warehouse name from code
     *
     * @param string $warehouseCode
     * @return string
     */
    private function getWarehouseName($warehouseCode)
    {
        $warehouseNames = [
            'whse_a' => 'East Coast Warehouse',
            'whse_b' => 'West Coast Warehouse',
            'whse_c' => 'Central Warehouse',
            'whse_d' => 'Southern Warehouse',
            'whse_e' => 'Northern Warehouse'
        ];
        
        return $warehouseNames[$warehouseCode] ?? $warehouseCode;
    }
    
    /**
     * Get region for warehouse
     *
     * @param string $warehouseCode
     * @return string
     */
    private function getRegionForWarehouse($warehouseCode)
    {
        $warehouseRegions = [
            'whse_a' => 'Northeast',
            'whse_b' => 'West Coast',
            'whse_c' => 'Midwest',
            'whse_d' => 'Southern',
            'whse_e' => 'Northern'
        ];
        
        return $warehouseRegions[$warehouseCode] ?? 'regional';
    }
    
    /**
     * Calculate time savings for a recommendation
     *
     * @param array $recommendation
     * @return float
     */
    public function calculateTimeSavings($recommendation)
    {
        $type = $recommendation['type'] ?? '';
        
        switch ($type) {
            case 'reorder':
                // Estimate time saved from manual forecasting and reorder calculation
                return 15.0; // 15 minutes
                
            case 'stock_transfer':
                // Estimate time saved from manual inventory analysis and transfer creation
                return 20.0; // 20 minutes
                
            case 'order_routing':
                // Estimate time saved from manual routing decision
                return 5.0; // 5 minutes
                
            case 'fraud_alert':
                // Estimate time saved from manual fraud review
                return 12.0; // 12 minutes
                
            default:
                return 5.0; // Default 5 minutes
        }
    }
} 