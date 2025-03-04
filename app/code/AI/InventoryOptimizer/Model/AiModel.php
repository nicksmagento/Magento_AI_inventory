<?php
namespace AI\InventoryOptimizer\Model;

use Magento\Framework\Model\AbstractModel;
use AI\InventoryOptimizer\Model\ResourceModel\AiModel as AiModelResource;

class AiModel extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AiModelResource::class);
    }
} 