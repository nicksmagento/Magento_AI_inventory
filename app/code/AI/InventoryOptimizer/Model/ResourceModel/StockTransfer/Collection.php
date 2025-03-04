<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\StockTransfer;

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
            \AI\InventoryOptimizer\Model\StockTransfer::class,
            \AI\InventoryOptimizer\Model\ResourceModel\StockTransfer::class
        );
    }
} 