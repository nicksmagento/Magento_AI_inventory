<?php
namespace AI\InventoryOptimizer\Model\ResourceModel\AiModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use AI\InventoryOptimizer\Model\AiModel;
use AI\InventoryOptimizer\Model\ResourceModel\AiModel as AiModelResource;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AiModel::class, AiModelResource::class);
    }
} 