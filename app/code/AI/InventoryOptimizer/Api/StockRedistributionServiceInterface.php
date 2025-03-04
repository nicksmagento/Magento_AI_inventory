<?php
namespace AI\InventoryOptimizer\Api;

interface StockRedistributionServiceInterface
{
    /**
     * Calculate optimal stock redistribution between warehouses
     *
     * @param string $sku
     * @param array $warehouseStockData
     * @return \AI\InventoryOptimizer\Api\Data\StockTransferInterface
     */
    public function calculateRedistribution($sku, array $warehouseStockData);
    
    /**
     * Execute stock transfer between warehouses
     *
     * @param \AI\InventoryOptimizer\Api\Data\StockTransferInterface $transfer
     * @return bool
     */
    public function executeTransfer($transfer);
} 