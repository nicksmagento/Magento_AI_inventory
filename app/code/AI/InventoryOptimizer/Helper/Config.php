<?php
namespace AI\InventoryOptimizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * Config paths
     */
    const XML_PATH_ENABLED = 'ai_inventory_optimizer/general/enabled';
    const XML_PATH_API_KEY = 'ai_inventory_optimizer/general/api_key';
    
    const XML_PATH_FORECASTING_ENABLED = 'ai_inventory_optimizer/forecasting/enabled';
    const XML_PATH_FORECASTING_MODEL_TYPE = 'ai_inventory_optimizer/forecasting/model_type';
    const XML_PATH_FORECASTING_AUTO_REORDER = 'ai_inventory_optimizer/forecasting/auto_reorder';
    
    const XML_PATH_STOCK_BALANCING_ENABLED = 'ai_inventory_optimizer/stock_balancing/enabled';
    const XML_PATH_STOCK_BALANCING_AUTO_TRANSFER = 'ai_inventory_optimizer/stock_balancing/auto_transfer';
    
    const XML_PATH_ORDER_ROUTING_ENABLED = 'ai_inventory_optimizer/order_routing/enabled';
    const XML_PATH_ORDER_ROUTING_OPTIMIZATION = 'ai_inventory_optimizer/order_routing/optimization_priority';
    
    const XML_PATH_FRAUD_DETECTION_ENABLED = 'ai_inventory_optimizer/fraud_detection/enabled';
    const XML_PATH_FRAUD_DETECTION_THRESHOLD = 'ai_inventory_optimizer/fraud_detection/risk_threshold';
    const XML_PATH_FRAUD_DETECTION_AUTO_HOLD = 'ai_inventory_optimizer/fraud_detection/auto_hold';
    
    const XML_PATH_MULTICHANNEL_ENABLED = 'ai_inventory_optimizer/multichannel/enabled';
    const XML_PATH_MULTICHANNEL_SYNC_FREQUENCY = 'ai_inventory_optimizer/multichannel/sync_frequency';
    
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    
    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }
    
    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get API key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        $encryptedApiKey = $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return $encryptedApiKey ? $this->encryptor->decrypt($encryptedApiKey) : '';
    }
    
    /**
     * Check if forecasting is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isForecastingEnabled($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_FORECASTING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get forecasting model type
     *
     * @param int|null $storeId
     * @return string
     */
    public function getForecastingModelType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FORECASTING_MODEL_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if auto reorder is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAutoReorderEnabled($storeId = null)
    {
        return $this->isForecastingEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_FORECASTING_AUTO_REORDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if stock balancing is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isStockBalancingEnabled($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_STOCK_BALANCING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if auto transfer is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAutoTransferEnabled($storeId = null)
    {
        return $this->isStockBalancingEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_STOCK_BALANCING_AUTO_TRANSFER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if order routing is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isOrderRoutingEnabled($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_ORDER_ROUTING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get optimization priority
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOptimizationPriority($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_ROUTING_OPTIMIZATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if fraud detection is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isFraudDetectionEnabled($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_FRAUD_DETECTION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get fraud risk threshold
     *
     * @param int|null $storeId
     * @return int
     */
    public function getFraudRiskThreshold($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_FRAUD_DETECTION_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if auto hold for fraud orders is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAutoHoldEnabled($storeId = null)
    {
        return $this->isFraudDetectionEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_FRAUD_DETECTION_AUTO_HOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Check if multichannel sync is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isMultichannelSyncEnabled($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_MULTICHANNEL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get multichannel sync frequency in minutes
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMultichannelSyncFrequency($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MULTICHANNEL_SYNC_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
} 