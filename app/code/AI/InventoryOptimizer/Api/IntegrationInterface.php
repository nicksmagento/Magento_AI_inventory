<?php
namespace AI\InventoryOptimizer\Api;

interface IntegrationInterface
{
    /**
     * Get integration code
     *
     * @return string
     */
    public function getCode();
    
    /**
     * Get integration name
     *
     * @return string
     */
    public function getName();
    
    /**
     * Check if integration is enabled
     *
     * @return bool
     */
    public function isEnabled();
    
    /**
     * Initialize the integration
     *
     * @return bool
     */
    public function initialize();
    
    /**
     * Test connection to the external system
     *
     * @return bool
     */
    public function testConnection();
    
    /**
     * Import inventory data from external system
     *
     * @param array $filters
     * @return array
     */
    public function importInventory(array $filters = []);
    
    /**
     * Export inventory data to external system
     *
     * @param array $data
     * @return bool
     */
    public function exportInventory(array $data);
    
    /**
     * Import orders from external system
     *
     * @param array $filters
     * @return array
     */
    public function importOrders(array $filters = []);
    
    /**
     * Export orders to external system
     *
     * @param array $data
     * @return bool
     */
    public function exportOrders(array $data);
    
    /**
     * Get integration status
     *
     * @return array
     */
    public function getStatus();
} 