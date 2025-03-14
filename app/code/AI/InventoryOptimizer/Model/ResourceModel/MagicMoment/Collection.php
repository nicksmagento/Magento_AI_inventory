<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\MagicMoment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \AI\InventoryOptimizer\Model\MagicMoment::class,
            \AI\InventoryOptimizer\Model\ResourceModel\MagicMoment::class
        );
    }
} 