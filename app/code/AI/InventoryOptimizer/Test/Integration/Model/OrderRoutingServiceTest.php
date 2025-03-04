<?php
namespace AI\InventoryOptimizer\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use AI\InventoryOptimizer\Api\OrderRoutingServiceInterface;
use AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface;

class OrderRoutingServiceTest extends TestCase
{
    /**
     * @var OrderRoutingServiceInterface
     */
    private $orderRoutingService;
    
    protected function setUp(): void
    {
        $this->orderRoutingService = Bootstrap::getObjectManager()->get(OrderRoutingServiceInterface::class);
    }
    
    /**
     * @magentoConfigFixture current_store ai_inventory/general/enabled 1
     * @magentoConfigFixture current_store ai_inventory/order_routing/enabled 1
     */
    public function testRouteOrder()
    {
        $orderId = '12345';
        $customerLocation = 'New York, NY';
        $availableWarehouses = ['A', 'B', 'C'];
        $shippingCosts = [
            'A' => ['cost' => 10.50, 'days' => 3],
            'B' => ['cost' => 8.25, 'days' => 2],
            'C' => ['cost' => 12.75, 'days' => 1]
        ];
        
        $result = $this->orderRoutingService->routeOrder(
            $orderId,
            $customerLocation,
            $availableWarehouses,
            $shippingCosts
        );
        
        $this->assertInstanceOf(OrderRoutingResultInterface::class, $result);
        $this->assertEquals($orderId, $result->getOrderId());
        $this->assertNotEmpty($result->getAssignedWarehouse());
        $this->assertNotEmpty($result->getWarehouseName());
        $this->assertNotEmpty($result->getEstimatedDelivery());
    }
} 