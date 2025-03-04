<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\Forecast;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \AI\InventoryOptimizer\Model\Forecast::class,
            \AI\InventoryOptimizer\Model\ResourceModel\Forecast::class
        );
    }
} 