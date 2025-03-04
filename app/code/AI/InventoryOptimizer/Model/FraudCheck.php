<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Framework\Model\AbstractModel;

class FraudCheck extends AbstractModel
{
    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AI\InventoryOptimizer\Model\ResourceModel\FraudCheck::class);
    }
} 