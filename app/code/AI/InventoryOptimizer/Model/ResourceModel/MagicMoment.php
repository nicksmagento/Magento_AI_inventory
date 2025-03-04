<?php
namespace AI\InventoryOptimizer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MagicMoment extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ai_inventory_magic_moments', 'entity_id');
    }

    /**
     * Get magic moments with proper parameter binding
     *
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getMagicMomentsByType($type, $limit = 10)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        
        // Use parameter binding to prevent SQL injection
        $select = $connection->select()
            ->from($table)
            ->where('moment_type = ?', $type)
            ->limit((int)$limit); // Cast to int for safety
        
        return $connection->fetchAll($select);
    }
} 