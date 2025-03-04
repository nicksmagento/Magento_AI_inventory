<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\ForecastRepositoryInterface;
use AI\InventoryOptimizer\Model\ResourceModel\Forecast as ForecastResource;
use AI\InventoryOptimizer\Model\ForecastFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class ForecastRepository implements ForecastRepositoryInterface
{
    /**
     * @var ForecastResource
     */
    private $forecastResource;
    
    /**
     * @var ForecastFactory
     */
    private $forecastFactory;
    
    /**
     * @param ForecastResource $forecastResource
     * @param ForecastFactory $forecastFactory
     */
    public function __construct(
        ForecastResource $forecastResource,
        ForecastFactory $forecastFactory
    ) {
        $this->forecastResource = $forecastResource;
        $this->forecastFactory = $forecastFactory;
    }
    
    /**
     * Save forecast
     *
     * @param \AI\InventoryOptimizer\Model\Forecast $forecast
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws CouldNotSaveException
     */
    public function save($forecast)
    {
        try {
            $this->forecastResource->save($forecast);
            return $forecast;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save forecast: %1', $e->getMessage()));
        }
    }
    
    /**
     * Get forecast by ID
     *
     * @param int $forecastId
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws NoSuchEntityException
     */
    public function getById($forecastId)
    {
        $forecast = $this->forecastFactory->create();
        $this->forecastResource->load($forecast, $forecastId);
        
        if (!$forecast->getId()) {
            throw new NoSuchEntityException(__('Forecast with ID "%1" does not exist.', $forecastId));
        }
        
        return $forecast;
    }
    
    /**
     * Get forecast by SKU
     *
     * @param string $sku
     * @return \AI\InventoryOptimizer\Model\Forecast
     * @throws NoSuchEntityException
     */
    public function getBySku($sku)
    {
        $forecast = $this->forecastFactory->create();
        $this->forecastResource->load($forecast, $sku, 'sku');
        
        if (!$forecast->getId()) {
            throw new NoSuchEntityException(__('Forecast for SKU "%1" does not exist.', $sku));
        }
        
        return $forecast;
    }
    
    /**
     * Delete forecast
     *
     * @param \AI\InventoryOptimizer\Model\Forecast $forecast
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($forecast)
    {
        try {
            $this->forecastResource->delete($forecast);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete forecast: %1', $e->getMessage()));
        }
    }
    
    /**
     * Delete forecast by ID
     *
     * @param int $forecastId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($forecastId)
    {
        return $this->delete($this->getById($forecastId));
    }
} 