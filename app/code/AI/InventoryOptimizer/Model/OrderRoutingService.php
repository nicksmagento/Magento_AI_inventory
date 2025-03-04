<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\OrderRoutingServiceInterface;
use AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterfaceFactory;
use AI\InventoryOptimizer\Api\OrderRoutingRepositoryInterface;
use AI\InventoryOptimizer\Model\OrderRoutingFactory;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderRoutingService implements OrderRoutingServiceInterface
{
    /**
     * @var AiService
     */
    private $aiService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var OrderRoutingResultInterfaceFactory
     */
    private $orderRoutingResultFactory;
    
    /**
     * @var OrderRoutingRepositoryInterface
     */
    private $orderRoutingRepository;
    
    /**
     * @var OrderRoutingFactory
     */
    private $orderRoutingFactory;
    
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param AiService $aiService
     * @param Config $config
     * @param OrderRoutingResultInterfaceFactory $orderRoutingResultFactory
     * @param OrderRoutingRepositoryInterface $orderRoutingRepository
     * @param OrderRoutingFactory $orderRoutingFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiService $aiService,
        Config $config,
        OrderRoutingResultInterfaceFactory $orderRoutingResultFactory,
        OrderRoutingRepositoryInterface $orderRoutingRepository,
        OrderRoutingFactory $orderRoutingFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->aiService = $aiService;
        $this->config = $config;
        $this->orderRoutingResultFactory = $orderRoutingResultFactory;
        $this->orderRoutingRepository = $orderRoutingRepository;
        $this->orderRoutingFactory = $orderRoutingFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }
    
    /**
     * Route an order to the optimal fulfillment center
     *
     * @param string $orderId
     * @param string $customerLocation
     * @param string[] $availableWarehouses
     * @param mixed $shippingCosts
     * @return \AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface
     */
    public function routeOrder($orderId, $customerLocation, array $availableWarehouses, $shippingCosts)
    {
        try {
            if (!$this->config->isOrderRoutingEnabled()) {
                throw new \Exception('AI Order Routing is disabled in configuration');
            }
            
            // Prepare order data for AI service
            $orderData = [
                'order_id' => $orderId,
                'customer_location' => $customerLocation
            ];
            
            // Prepare warehouse data for AI service
            $warehouseData = [];
            foreach ($availableWarehouses as $warehouse) {
                $warehouseData[] = [
                    'code' => $warehouse,
                    'name' => 'Warehouse ' . $warehouse,
                    'shipping_cost' => isset($shippingCosts[$warehouse]['cost']) ? $shippingCosts[$warehouse]['cost'] : 0,
                    'delivery_days' => isset($shippingCosts[$warehouse]['days']) ? $shippingCosts[$warehouse]['days'] : 0
                ];
            }
            
            // Call AI service to determine optimal warehouse
            $routingResult = $this->aiService->calculateOptimalWarehouse($orderData, $warehouseData);
            
            // Save routing result to database
            $orderRouting = $this->orderRoutingFactory->create();
            $orderRouting->setOrderId($orderId);
            $orderRouting->setAssignedWarehouse($routingResult['assigned_warehouse']);
            $orderRouting->setEstimatedDeliveryDays(intval($routingResult['estimated_delivery']));
            $this->orderRoutingRepository->save($orderRouting);
            
            // Create and return routing result object
            $result = $this->orderRoutingResultFactory->create();
            $result->setOrderId($routingResult['order_id']);
            $result->setAssignedWarehouse($routingResult['assigned_warehouse']);
            $result->setWarehouseName($routingResult['warehouse_name']);
            $result->setEstimatedDelivery($routingResult['estimated_delivery']);
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Order Routing Service Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get routing information for an order
     *
     * @param string $orderId
     * @return \AI\InventoryOptimizer\Api\Data\OrderRoutingResultInterface
     */
    public function getOrderRouting($orderId)
    {
        try {
            // Try to get existing routing information
            try {
                $orderRouting = $this->orderRoutingRepository->getByOrderId($orderId);
                
                // Create routing result from existing data
                $result = $this->orderRoutingResultFactory->create();
                $result->setOrderId($orderRouting->getOrderId());
                $result->setAssignedWarehouse($orderRouting->getAssignedWarehouse());
                $result->setWarehouseName('Warehouse ' . $orderRouting->getAssignedWarehouse());
                $result->setEstimatedDelivery($orderRouting->getEstimatedDeliveryDays() . ' days');
                
                return $result;
            } catch (NoSuchEntityException $e) {
                // If no routing exists, generate a new one
                $order = $this->orderRepository->get($orderId);
                $shippingAddress = $order->getShippingAddress();
                
                if (!$shippingAddress) {
                    throw new \Exception('Order does not have a shipping address');
                }
                
                $customerLocation = [
                    'city' => $shippingAddress->getCity(),
                    'region' => $shippingAddress->getRegion(),
                    'country' => $shippingAddress->getCountryId(),
                    'postcode' => $shippingAddress->getPostcode()
                ];
                
                // Get available warehouses (simplified)
                $availableWarehouses = ['A', 'B', 'C'];
                
                // Calculate shipping costs (simplified)
                $shippingCosts = [
                    'A' => ['cost' => 10.50, 'days' => 3],
                    'B' => ['cost' => 8.25, 'days' => 2],
                    'C' => ['cost' => 12.75, 'days' => 1]
                ];
                
                // Route the order
                return $this->routeOrder($orderId, $customerLocation, $availableWarehouses, $shippingCosts);
            }
        } catch (\Exception $e) {
            $this->logger->error('Get Order Routing Error: ' . $e->getMessage());
            throw $e;
        }
    }
} 