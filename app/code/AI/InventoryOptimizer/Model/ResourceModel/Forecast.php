<?php
namespace AI\InventoryOptimizer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Forecast extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ai_inventory_forecast', 'entity_id');
    }
} 