<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Framework\Model\AbstractModel;

class OrderRouting extends AbstractModel
{
    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AI\InventoryOptimizer\Model\ResourceModel\OrderRouting::class);
    }
} 