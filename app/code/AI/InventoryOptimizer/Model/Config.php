<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;

class Config
{
    /**
     * Config paths
     */
    const XML_PATH_ENABLED = 'ai_inventory_optimizer/general/enabled';
    const XML_PATH_LOG_LEVEL = 'ai_inventory_optimizer/general/log_level';
    
    const XML_PATH_MAGIC_MOMENTS_ENABLED = 'ai_inventory_optimizer/magic_moments/enabled';
    const XML_PATH_COMPETITOR_STOCKOUT_DETECTION = 'ai_inventory_optimizer/magic_moments/competitor_stockout_detection';
    const XML_PATH_COMPETITOR_API_KEY = 'ai_inventory_optimizer/magic_moments/competitor_api_key';
    const XML_PATH_SOCIAL_TREND_DETECTION = 'ai_inventory_optimizer/magic_moments/social_trend_detection';
    const XML_PATH_SOCIAL_TRENDS_API_KEY = 'ai_inventory_optimizer/magic_moments/social_trends_api_key';
    const XML_PATH_WEATHER_ISSUE_DETECTION = 'ai_inventory_optimizer/magic_moments/weather_issue_detection';
    const XML_PATH_WEATHER_API_KEY = 'ai_inventory_optimizer/magic_moments/weather_api_key';
    
    const XML_PATH_ONBOARDING_ENABLED = 'ai_inventory_optimizer/onboarding/enabled';
    const XML_PATH_INSTANT_VALUE_GENERATION = 'ai_inventory_optimizer/onboarding/instant_value_generation';
    const XML_PATH_SUCCESS_TRACKING = 'ai_inventory_optimizer/onboarding/success_tracking';
    const XML_PATH_PROGRESSIVE_DISCLOSURE = 'ai_inventory_optimizer/onboarding/progressive_disclosure';
    
    const XML_PATH_LANGUAGE_ENABLED = 'ai_inventory_optimizer/language/enabled';
    const XML_PATH_BUSINESS_GOALS = 'ai_inventory_optimizer/language/business_goals';
    const XML_PATH_REVENUE_IMPACT = 'ai_inventory_optimizer/language/revenue_impact';
    const XML_PATH_TIME_SAVINGS = 'ai_inventory_optimizer/language/time_savings';
    const XML_PATH_CUSTOMER_EXPERIENCE = 'ai_inventory_optimizer/language/customer_experience';
    
    // Integration constants
    const XML_PATH_INTEGRATIONS_ENABLED = 'ai_inventory/integrations/enabled';
    const XML_PATH_INTEGRATIONS_SYNC_FREQUENCY = 'ai_inventory/integrations/sync_frequency';
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var Json
     */
    private $json;
    
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array
     */
    private $temporaryValues = [];
    
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        EncryptorInterface $encryptor,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }
    
    /**
     * Check if module is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isModuleEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get log level
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return string
     */
    public function getLogLevel($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOG_LEVEL,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if Magic Moments is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isMagicMomentsEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isModuleEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_MAGIC_MOMENTS_ENABLED,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if competitor stockout detection is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isCompetitorStockoutDetectionEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMagicMomentsEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMPETITOR_STOCKOUT_DETECTION,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get competitor API key
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return string
     */
    public function getCompetitorApiKey($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getEncryptedApiKey(self::XML_PATH_COMPETITOR_API_KEY, $scopeType, $scopeCode);
    }
    
    /**
     * Check if social trend detection is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isSocialTrendDetectionEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMagicMomentsEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_SOCIAL_TREND_DETECTION,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get social trends API key
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return string
     */
    public function getSocialTrendsApiKey($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getEncryptedApiKey(self::XML_PATH_SOCIAL_TRENDS_API_KEY, $scopeType, $scopeCode);
    }
    
    /**
     * Check if weather issue detection is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isWeatherIssueDetectionEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMagicMomentsEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_WEATHER_ISSUE_DETECTION,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get weather API key
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return string
     */
    public function getWeatherApiKey($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->getEncryptedApiKey(self::XML_PATH_WEATHER_API_KEY, $scopeType, $scopeCode);
    }
    
    /**
     * Check if onboarding is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isOnboardingEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isModuleEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_ONBOARDING_ENABLED,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if instant value generation is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isInstantValueGenerationEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isOnboardingEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_INSTANT_VALUE_GENERATION,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if success tracking is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isSuccessTrackingEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isOnboardingEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_SUCCESS_TRACKING,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if progressive disclosure is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isProgressiveDisclosureEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isOnboardingEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_PROGRESSIVE_DISCLOSURE,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if merchant-centric language is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isMerchantCentricLanguageEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isModuleEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_LANGUAGE_ENABLED,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get business goals
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return array
     */
    public function getBusinessGoals($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $goals = $this->scopeConfig->getValue(
            self::XML_PATH_BUSINESS_GOALS,
            $scopeType,
            $scopeCode
        );
        
        if (empty($goals)) {
            return [];
        }
        
        if (is_string($goals)) {
            $goals = explode(',', $goals);
        }
        
        $result = [];
        foreach ($goals as $goal) {
            $result[$goal] = true;
        }
        
        return $result;
    }
    
    /**
     * Check if revenue impact is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isRevenueImpactEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMerchantCentricLanguageEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_REVENUE_IMPACT,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if time savings is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isTimeSavingsEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMerchantCentricLanguageEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_TIME_SAVINGS,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if customer experience is enabled
     *
     * @param string $scopeType
     * @param mixed $scopeCode
     * @return bool
     */
    public function isCustomerExperienceEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isMerchantCentricLanguageEnabled($scopeType, $scopeCode) && $this->scopeConfig->isSetFlag(
            self::XML_PATH_CUSTOMER_EXPERIENCE,
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Check if integrations are enabled
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isIntegrationsEnabled($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->isModuleEnabled($scopeType, $scopeCode) && 
               $this->scopeConfig->isSetFlag(self::XML_PATH_INTEGRATIONS_ENABLED, $scopeType, $scopeCode);
    }
    
    /**
     * Check if specific integration is enabled
     *
     * @param string $code
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isIntegrationEnabled($code, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        if (!$this->isIntegrationsEnabled($scopeType, $scopeCode)) {
            return false;
        }
        
        // Determine integration type from code
        $type = $this->getIntegrationType($code);
        
        return $this->scopeConfig->isSetFlag(
            'ai_inventory/integrations/' . $type . '/' . $code . '/enabled',
            $scopeType,
            $scopeCode
        );
    }
    
    /**
     * Get integration API URL
     *
     * @param string $code
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string
     */
    public function getIntegrationApiUrl($code, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $type = $this->getIntegrationType($code);
        $path = 'ai_inventory/integrations/' . $type . '/' . $code . '/api_url';
        
        // Check for temporary value first
        if (isset($this->temporaryValues[$path])) {
            return $this->temporaryValues[$path];
        }
        
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }
    
    /**
     * Get integration credentials
     *
     * @param string $code
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return array
     */
    public function getIntegrationCredentials($code, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $type = $this->getIntegrationType($code);
        $basePath = 'ai_inventory/integrations/' . $type . '/' . $code;
        
        $credentials = [];
        
        // Client ID
        $clientIdPath = $basePath . '/client_id';
        if (isset($this->temporaryValues[$clientIdPath])) {
            $credentials['client_id'] = $this->temporaryValues[$clientIdPath];
        } else {
            $credentials['client_id'] = $this->scopeConfig->getValue($clientIdPath, $scopeType, $scopeCode);
        }
        
        // Client Secret
        $clientSecretPath = $basePath . '/client_secret';
        if (isset($this->temporaryValues[$clientSecretPath])) {
            $credentials['client_secret'] = $this->temporaryValues[$clientSecretPath];
        } else {
            $credentials['client_secret'] = $this->scopeConfig->getValue($clientSecretPath, $scopeType, $scopeCode);
        }
        
        // Add any additional credentials specific to the integration
        switch ($code) {
            case 'amazon':
                $credentials['seller_id'] = $this->scopeConfig->getValue($basePath . '/seller_id', $scopeType, $scopeCode);
                $credentials['marketplace_id'] = $this->scopeConfig->getValue($basePath . '/marketplace_id', $scopeType, $scopeCode);
                break;
                
            case 'shopify':
                $credentials['shop_domain'] = $this->scopeConfig->getValue($basePath . '/shop_domain', $scopeType, $scopeCode);
                $credentials['api_version'] = $this->scopeConfig->getValue($basePath . '/api_version', $scopeType, $scopeCode);
                break;
        }
        
        return $credentials;
    }
    
    /**
     * Get warehouse mapping for integration
     *
     * @param string $code
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return array
     */
    public function getWarehouseMapping($code, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $type = $this->getIntegrationType($code);
        $mappingString = $this->scopeConfig->getValue(
            'ai_inventory/integrations/' . $type . '/' . $code . '/warehouse_mapping',
            $scopeType,
            $scopeCode
        );
        
        if (empty($mappingString)) {
            return [];
        }
        
        $mapping = [];
        $lines = explode("\n", $mappingString);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $mapping[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $mapping;
    }
    
    /**
     * Get cached token for integration
     *
     * @param string $code
     * @return array|null
     */
    public function getCachedToken($code)
    {
        // In a real implementation, this would retrieve from cache or database
        // For this example, we'll just return null to force token refresh
        return null;
    }
    
    /**
     * Set cached token for integration
     *
     * @param string $code
     * @param array $tokenData
     * @return void
     */
    public function setCachedToken($code, array $tokenData)
    {
        // In a real implementation, this would store in cache or database
        // For this example, we'll just log it
        // No implementation needed for this example
    }
    
    /**
     * Set temporary configuration value
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setTemporaryValue($path, $value)
    {
        $this->temporaryValues[$path] = $value;
    }
    
    /**
     * Get integration type from code
     *
     * @param string $code
     * @return string
     */
    private function getIntegrationType($code)
    {
        $typeMap = [
            'sap' => 'erp',
            'netsuite' => 'erp',
            'dynamics' => 'erp',
            'brightpearl' => 'ims',
            'cin7' => 'ims',
            'tradegecko' => 'ims',
            'zoho' => 'ims',
            'fishbowl' => 'ims',
            'katana' => 'ims',
            'orderbot' => 'oms',
            'shipstation' => 'oms',
            'linnworks' => 'oms',
            'orderhive' => 'oms',
            'skubana' => 'oms',
            'manhattan' => 'wms',
            'highjump' => 'wms',
            'logiwa' => 'wms',
            'shiphero' => 'wms',
            'threepl' => 'wms',
            'amazon' => 'marketplace',
            'walmart' => 'marketplace',
            'ebay' => 'marketplace',
            'etsy' => 'marketplace',
            'target' => 'marketplace'
        ];
        
        return $typeMap[$code] ?? 'other';
    }
    
    /**
     * Get API key with proper encryption
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    private function getEncryptedApiKey($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $encryptedKey = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        if (!$encryptedKey) {
            return '';
        }
        
        try {
            return $this->encryptor->decrypt($encryptedKey);
        } catch (\Exception $e) {
            $this->logger->critical('Failed to decrypt API key: ' . $e->getMessage());
            return '';
        }
    }
} 