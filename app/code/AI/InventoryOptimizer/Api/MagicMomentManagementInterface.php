<?php
namespace AI\InventoryOptimizer\Api;

interface MagicMomentManagementInterface
{
    /**
     * Mark a magic moment as read
     *
     * @param int $id
     * @return bool
     */
    public function markAsRead($id);
    
    /**
     * Mark a magic moment as actioned
     *
     * @param int $id
     * @return bool
     */
    public function markAsActioned($id);
    
    /**
     * Get count of unread magic moments
     *
     * @return int
     */
    public function getUnreadCount();
} 