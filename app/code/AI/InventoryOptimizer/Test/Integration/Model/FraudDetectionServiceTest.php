<?php
namespace AI\InventoryOptimizer\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use AI\InventoryOptimizer\Api\FraudDetectionServiceInterface;
use AI\InventoryOptimizer\Api\Data\FraudCheckResultInterface;

class FraudDetectionServiceTest extends TestCase
{
    /**
     * @var FraudDetectionServiceInterface
     */
    private $fraudDetectionService;
    
    protected function setUp(): void
    {
        $this->fraudDetectionService = Bootstrap::getObjectManager()->get(FraudDetectionServiceInterface::class);
    }
    
    /**
     * @magentoConfigFixture current_store ai_inventory/general/enabled 1
     * @magentoConfigFixture current_store ai_inventory/fraud_detection/enabled 1
     */
    public function testCheckOrderForFraud()
    {
        $orderId = '12345';
        $customerIp = '192.168.1.1';
        $orderValue = 299.99;
        
        $result = $this->fraudDetectionService->checkOrderForFraud($orderId, $customerIp, $orderValue);
        
        $this->assertInstanceOf(FraudCheckResultInterface::class, $result);
        $this->assertEquals($orderId, $result->getOrderId());
        $this->assertNotEmpty($result->getFraudRisk());
        $this->assertIsNumeric($result->getRiskScore());
        $this->assertNotEmpty($result->getAction());
    }
} 