<?php
namespace AI\InventoryOptimizer\Api;

interface ForecastRepositoryInterface
{
    /**
     * Save forecast
     *
     * @param \AI\InventoryOptimizer\Model\Forecast $forecast
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($forecast);
    
    /**
     * Get forecast by ID
     *
     * @param int $forecastId
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($forecastId);
    
    /**
     * Get forecast by SKU
     *
     * @param string $sku
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySku($sku);
    
    /**
     * Delete forecast
     *
     * @param \AI\InventoryOptimizer\Model\Forecast $forecast
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete($forecast);
    
    /**
     * Delete forecast by ID
     *
     * @param int $forecastId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($forecastId);
} 