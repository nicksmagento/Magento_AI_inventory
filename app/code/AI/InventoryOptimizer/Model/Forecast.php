<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Framework\Model\AbstractModel;

class Forecast extends AbstractModel
{
    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AI\InventoryOptimizer\Model\ResourceModel\Forecast::class);
    }
} 